<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AiEvent;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIChatMonitorController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
        parent::__construct();
    }

    public function overview()
    {
        $this->data['selectedMainMenu'] = 'chatbot_management';
        $this->data['selectedSubMenu'] = 'overview';
        return view('backend.ai-monitor.overview', $this->data);
    }

    public function webhookHistory(Request $request)
    {
        $this->data['selectedMainMenu'] = 'chatbot_management';
        $this->data['selectedSubMenu'] = 'webhooks';

        $filters = [
            'event_type' => (string) $request->get('event_type', ''),
            'status' => (string) $request->get('status', ''),
            'keyword' => trim((string) $request->get('keyword', '')),
            'date_from' => (string) $request->get('date_from', ''),
            'date_to' => (string) $request->get('date_to', ''),
        ];

        $query = AiEvent::query()->latest('received_at');

        if ($filters['event_type'] !== '') {
            $query->where('event_type', $filters['event_type']);
        }

        if ($filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if ($filters['keyword'] !== '') {
            $keyword = '%' . $filters['keyword'] . '%';
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('event_id', 'like', $keyword)
                    ->orWhere('job_id', 'like', $keyword)
                    ->orWhere('doc_id', 'like', $keyword)
                    ->orWhere('source_filename', 'like', $keyword);
            });
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('received_at', '>=', $filters['date_from']);
        }

        if ($filters['date_to'] !== '') {
            $query->whereDate('received_at', '<=', $filters['date_to']);
        }

        $events = $query->paginate(20)->withQueryString();
        $eventTypes = AiEvent::query()
            ->whereNotNull('event_type')
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');
        $statuses = AiEvent::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status');

        return view('backend.ai-monitor.webhook-history', array_merge($this->data, compact(
            'events',
            'eventTypes',
            'statuses',
            'filters'
        )));
    }

    /**
     * Get combined status and metrics for the AI Bot.
     *
     * @return JsonResponse
     */
    public function getApiStatus(): JsonResponse
    {
        try {
            $health = $this->aiChatService->getHealthStatus();
            $status = $this->aiChatService->getStatus();
            $metrics = $this->aiChatService->getMetrics();

            return response()->json([
                'success' => true,
                'data' => [
                    'health' => $health->json() ?? ['status' => 'offline'],
                    'status' => $status->json() ?? [],
                    'metrics' => $metrics->json() ?? [],
                    'connected' => $health->successful()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('AI Monitor Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not connect to AI Service',
                'data' => [
                    'connected' => false,
                    'health' => ['status' => 'offline']
                ]
            ], 500);
        }
    }

    /**
     * Get advanced admin stats for the dashboard.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAdvancedStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(6)->format('Y-m-d')); // Reduced to 7 days for better performance on development
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            $baseUrl = config('services.ai_chat.api_url');
            $apiKey = config('services.ai_chat.api_key');
            $adminKey = config('services.ai_chat.api_admin_key');

            $headers = [
                'X-API-Key' => $apiKey,
                'X-Admin-API-Key' => $adminKey,
                'Content-Type' => 'application/json'
            ];

            // Using Http Pool to fetch data in parallel to avoid blocking the single-threaded server.
            $responses = Http::pool(fn ($pool) => [
                $pool->as('overview')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/overview', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]),
                $pool->as('daily')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/daily', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]),
                $pool->as('intents')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/intents', [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => $responses['overview']?->json(),
                    'daily' => $responses['daily']?->json(),
                    'intents' => $this->formatIntents($responses['intents']?->json()),
                    'range' => [
                        'start' => $startDate,
                        'end' => $endDate
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('AI Advanced Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching AI statistics'
            ], 500);
        }
    }

    /**
     * Format and translate intents for display.
     */
    private function formatIntents($data)
    {
        if (!$data || !isset($data['intents'])) {
            return $data;
        }

        foreach ($data['intents'] as &$item) {
            // Formatting: ASK_PROJECT -> Ask Project
            $formatted = str_replace(['_', '-'], ' ', $item['intent']);
            $item['intent'] = ucwords(strtolower($formatted));
        }

        return $data;
    }

    /**
     * Get extra admin stats for the dashboard (latency, fallback, feedback, knowledge, etc).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getExtraStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->subDays(6)->format('Y-m-d'));
            $endDate = $request->get('end_date', now()->format('Y-m-d'));

            $baseUrl = config('services.ai_chat.api_url');
            $apiKey = config('services.ai_chat.api_key');
            $adminKey = config('services.ai_chat.api_admin_key');

            $headers = [
                'X-API-Key' => $apiKey,
                'X-Admin-API-Key' => $adminKey,
                'Content-Type' => 'application/json'
            ];

            $responses = Http::pool(fn ($pool) => [
                $pool->as('latency')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/latency', [
                    'date_from' => $startDate,
                    'date_to' => $endDate
                ]),
                $pool->as('fallback')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/fallback', [
                    'date_from' => $startDate,
                    'date_to' => $endDate
                ]),
                $pool->as('top_questions')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/top-questions', [
                    'date_from' => $startDate,
                    'date_to' => $endDate,
                    'limit' => 10
                ]),
                $pool->as('feedback')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/feedback', [
                    'date_from' => $startDate,
                    'date_to' => $endDate
                ]),
                $pool->as('health')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/health'),
                $pool->as('knowledge')->withHeaders($headers)->timeout(10)->get($baseUrl . '/api/v1/admin/stats/knowledge')
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'latency' => $responses['latency']?->json(),
                    'fallback' => $responses['fallback']?->json(),
                    'top_questions' => $responses['top_questions']?->json(),
                    'feedback' => $responses['feedback']?->json(),
                    'health' => $responses['health']?->json(),
                    'knowledge' => $responses['knowledge']?->json()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('AI Extra Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching AI extra statistics'
            ], 500);
        }
    }
}
