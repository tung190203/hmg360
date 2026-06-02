<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantDatabaseManager;
use App\Http\Controllers\Controller;
use App\Services\AiChatService;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\AiUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIChatController extends Controller
{
    private const TENANT_SLUG = 'trung-tam-xuc-tien-ha-noi';
    private const MODULE_SLUG = 'chatbot_management';
    private const MODULE_PATH = 'Tenants/TrungTamXucTienHaNoi/ChatbotManagement';

    public function __construct(private AiChatService $aiChatService)
    {
    }

    public function chat(Request $request)
    {
        $payload = [
            'language' => 'auto',
            'message' => $request->message,
            'session_id' => $request->session_id,
        ];

        if ($request->has('model')) {
            $payload['model_name'] = $request->model;
        }

        $response = $this->aiChatService->chat($payload);
        $body = $response->json() ?? [];

        if ($response->successful()) {
            try {
                $this->connectTenant();

                $tokens = (array) Arr::get($body, 'tokens', []);

                AiUsageLog::create([
                    'endpoint' => '/chat',
                    'user_id' => optional(auth('web')->user())->id,
                    'model_used' => Arr::get($body, 'model_used') ?: Arr::get($tokens, 'model'),
                    'input_tokens' => (int) (Arr::get($tokens, 'input') ?? Arr::get($tokens, 'input_tokens') ?? 0),
                    'output_tokens' => (int) (Arr::get($tokens, 'output') ?? Arr::get($tokens, 'output_tokens') ?? 0),
                    'cost_usd' => (float) (Arr::get($tokens, 'cost_usd') ?? Arr::get($body, 'cost_usd') ?? 0),
                    'payload_json' => $body,
                    'called_at' => now(),
                ]);
            } catch (\Throwable $exception) {
                Log::warning('AI chat usage log failed: ' . $exception->getMessage());
            }
        }

        return response()->json($body, $response->status());
    }

    public function sessionHistory($sessionId)
    {
        $response = $this->aiChatService->getSessionHistory($sessionId);

        return response()->json($response->json(), $response->status());
    }

    public function deleteSession($sessionId)
    {
        $response = $this->aiChatService->deleteSession($sessionId);

        return response()->json($response->json(), $response->status());
    }

    public function submitFeedback(Request $request)
    {
        $response = $this->aiChatService->sendFeedback([
            'session_id' => $request->session_id,
            'message_id' => $request->message_id,
            'rating' => $request->rating,
            'feedback_type' => $request->feedback_type ?? 'helpful',
            'comment' => $request->comment,
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function getHealthStatus()
    {
        $response = $this->aiChatService->getHealthStatus();

        return response()->json($response->json(), $response->status());
    }

    private function connectTenant(): void
    {
        $tenant = Tenant::query()
            ->where('slug', self::TENANT_SLUG)
            ->where('status', Tenant::STATUS_ACTIVE)
            ->first();

        if (!$tenant) {
            throw new RuntimeException('Tenant is not active.');
        }

        $moduleEnabled = Module::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', self::MODULE_SLUG)
            ->where('path', self::MODULE_PATH)
            ->where('is_enabled', true)
            ->exists();

        if (!$moduleEnabled) {
            throw new RuntimeException('Chatbot management module is not enabled.');
        }

        app(TenantDatabaseManager::class)->connect($tenant);
    }
}
