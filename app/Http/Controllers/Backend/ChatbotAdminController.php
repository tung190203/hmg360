<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AiUsageLog;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ChatbotAdminController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
        parent::__construct();
    }

    private function proxyResponse($response)
    {
        return response($response->body(), $response->status())->header('Content-Type', 'application/json');
    }

    private function logAiUsage(string $endpoint, array $payload): void
    {
        $tokens = (array) Arr::get($payload, 'tokens', []);
        $models = Arr::get($payload, 'models_used', []);
        $firstModel = is_array($models) ? Arr::first($models) : null;

        AiUsageLog::create([
            'endpoint' => $endpoint,
            'user_id' => optional(auth('web')->user())->id,
            'model_used' => Arr::get($firstModel, 'model') ?: Arr::get($payload, 'model_used'),
            'input_tokens' => (int) (Arr::get($tokens, 'input') ?? Arr::get($tokens, 'input_tokens') ?? Arr::get($tokens, 'llm_input') ?? Arr::get($tokens, 'embedding_input') ?? 0),
            'output_tokens' => (int) (Arr::get($tokens, 'output') ?? Arr::get($tokens, 'output_tokens') ?? Arr::get($tokens, 'llm_output') ?? 0),
            'cost_usd' => (float) (Arr::get($payload, 'cost_usd_total') ?? Arr::get($tokens, 'cost_usd') ?? Arr::get($payload, 'cost_usd') ?? 0),
            'payload_json' => $payload,
            'called_at' => now(),
        ]);
    }

    // --- Sync ---
    public function getSyncSettings()
    {
        return $this->proxyResponse($this->aiChatService->getAdminSyncSettings());
    }

    public function updateSyncSettings(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->updateAdminSyncSettings($request->all()));
    }

    public function triggerSync(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->triggerAdminSync($request->get('mode', 'delta')));
    }

    public function swapSlots()
    {
        return $this->proxyResponse($this->aiChatService->swapAdminSyncSlots());
    }

    public function getExtractConfig()
    {
        return $this->proxyResponse($this->aiChatService->getExtractConfig());
    }

    public function extract(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required_without:file_url|file|max:20480',
            'file_url' => 'required_without:file|nullable|string|max:2048',
            'summary_mode' => 'nullable|in:auto,short,normal,detailed,none',
            'language' => 'nullable|in:vi,en,auto',
        ]);

        if ($request->hasFile('file')) {
            $response = $this->aiChatService->extractContent(
                $request->file('file'),
                $validated['summary_mode'] ?? 'auto',
                $validated['language'] ?? 'auto'
            );
        } else {
            $path = $this->resolveCkfinderFilePath($validated['file_url']);
            $response = $this->aiChatService->extractContentFromPath(
                $path,
                basename(parse_url($validated['file_url'], PHP_URL_PATH) ?: $path),
                $validated['summary_mode'] ?? 'auto',
                $validated['language'] ?? 'auto'
            );
        }

        if ($response->successful()) {
            $this->logAiUsage('/admin/extract', $response->json() ?? []);
        }

        return $this->proxyResponse($response);
    }

    public function getKnowledgeConfig()
    {
        return $this->proxyResponse($this->aiChatService->getKnowledgeConfig());
    }

    public function createKnowledge(Request $request)
    {
        $validated = $request->validate([
            'file_url' => 'required_without:text|nullable|string|max:2048',
            'text' => 'required_without:file_url|nullable|string',
            'title' => 'nullable|string|max:500',
            'language' => 'nullable|in:auto,vi,en',
            'summary_mode' => 'nullable|in:none,auto,short,normal,detailed',
        ]);

        if (!empty($validated['file_url'])) {
            $path = $this->resolveCkfinderFilePath($validated['file_url']);
            $response = $this->aiChatService->createKnowledgeFromPath(
                $path,
                basename(parse_url($validated['file_url'], PHP_URL_PATH) ?: $path),
                $validated
            );
        } else {
            $response = $this->aiChatService->createKnowledgeFromText($validated);
        }

        return $this->proxyResponse($response);
    }

    public function getKnowledgeJobs(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getKnowledgeJobs($request->query()));
    }

    public function getKnowledgeJob($jobId)
    {
        return $this->proxyResponse($this->aiChatService->getKnowledgeJob($jobId));
    }

    public function getKnowledgeDocs(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getKnowledgeDocs($request->query()));
    }

    public function getKnowledgeDoc($docId)
    {
        return $this->proxyResponse($this->aiChatService->getKnowledgeDoc($docId));
    }

    public function deleteKnowledgeDoc($docId)
    {
        return $this->proxyResponse($this->aiChatService->deleteKnowledgeDoc($docId));
    }

    public function getUsage(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminUsage($request->query()));
    }

    public function getUsageSummary(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminUsageSummary($request->query()));
    }

    public function getSyncHistory(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminSyncHistory($request->query()));
    }

    private function resolveCkfinderFilePath(string $fileUrl): string
    {
        $urlPath = parse_url($fileUrl, PHP_URL_PATH) ?: '';
        $decodedPath = urldecode($urlPath);

        if (str_starts_with($decodedPath, 'uploads/') || str_starts_with($decodedPath, 'storage/')) {
            $decodedPath = '/' . $decodedPath;
        }

        $uploadsPosition = strpos($decodedPath, '/uploads/');
        if ($uploadsPosition !== false) {
            $decodedPath = substr($decodedPath, $uploadsPosition);
        }

        $storagePosition = strpos($decodedPath, '/storage/');
        if ($storagePosition !== false) {
            $decodedPath = substr($decodedPath, $storagePosition);
        }

        if (!str_starts_with($decodedPath, '/uploads/') && !str_starts_with($decodedPath, '/storage/')) {
            throw ValidationException::withMessages([
                'file_url' => 'Không nhận diện được đường dẫn file CKFinder.',
            ]);
        }

        $extension = strtolower(pathinfo($decodedPath, PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png', 'webp', 'txt', 'md'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file_url' => 'Định dạng file không được hỗ trợ.',
            ]);
        }

        $candidatePath = str_starts_with($decodedPath, '/storage/')
            ? storage_path('app/public/' . ltrim(substr($decodedPath, strlen('/storage/')), '/'))
            : public_path(ltrim($decodedPath, '/'));

        $allowedRoots = array_filter([
            realpath(public_path('uploads')),
            realpath(storage_path('app/public')),
        ]);

        $realPath = realpath($candidatePath);
        $isAllowedPath = false;
        foreach ($allowedRoots as $root) {
            if ($realPath && ($realPath === $root || str_starts_with($realPath, $root . DIRECTORY_SEPARATOR))) {
                $isAllowedPath = true;
                break;
            }
        }

        if (!$realPath || !$isAllowedPath || !is_file($realPath)) {
            throw ValidationException::withMessages([
                'file_url' => 'Không tìm thấy file đã chọn.',
            ]);
        }

        if (filesize($realPath) > 20 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'file_url' => 'File vượt quá giới hạn 20MB.',
            ]);
        }

        return $realPath;
    }

    // --- Prompts ---
    public function getPrompts()
    {
        return $this->proxyResponse($this->aiChatService->getAdminPrompts());
    }

    public function updatePrompt(Request $request, $key, $language)
    {
        return $this->proxyResponse($this->aiChatService->updateAdminPrompt($key, $language, $request->get('content')));
    }

    public function resetPrompt($key, $language)
    {
        return $this->proxyResponse($this->aiChatService->resetAdminPrompt($key, $language));
    }

    // --- Blacklist ---
    public function getBlacklist()
    {
        return $this->proxyResponse($this->aiChatService->getAdminBlacklist());
    }

    public function addBlacklistKeyword(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->addAdminBlacklistKeyword($request->all()));
    }

    public function updateBlacklistKeyword(Request $request, $keywordId)
    {
        return $this->proxyResponse($this->aiChatService->updateAdminBlacklistKeyword($keywordId, $request->all()));
    }

    public function deleteBlacklistKeyword($keywordId)
    {
        return $this->proxyResponse($this->aiChatService->deleteAdminBlacklistKeyword($keywordId));
    }

    public function updateBlacklistRefusal(Request $request, $group, $language)
    {
        return $this->proxyResponse($this->aiChatService->updateAdminBlacklistRefusal($group, $language, $request->get('content')));
    }

    public function resetBlacklistRefusal($group, $language)
    {
        return $this->proxyResponse($this->aiChatService->resetAdminBlacklistRefusal($group, $language));
    }

    public function getBlacklistLog(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminBlacklistLog($request->all()));
    }

    // --- Sessions & Feedback ---
    public function getSessions(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminSessions($request->all()));
    }

    public function getSessionDetail($sessionId)
    {
        return $this->proxyResponse($this->aiChatService->getAdminSessionDetail($sessionId));
    }

    public function exportSessions(Request $request)
    {
        // Because export can be a file download (CSV/TXT/JSON), we need to return the actual stream/content and correct content-type
        $response = $this->aiChatService->exportAdminSessions($request->all());
        $contentType = $response->header('Content-Type') ?? 'application/json';
        $contentDisposition = $response->header('Content-Disposition') ?? 'attachment; filename="export.json"';
        return response($response->body(), $response->status())
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', $contentDisposition);
    }

    public function exportSingleSession(Request $request, $sessionId)
    {
        $type = $request->get('type', 'json');
        $response = $this->aiChatService->exportAdminSingleSession($sessionId, $type);
        $contentType = $response->header('Content-Type') ?? 'application/json';
        $contentDisposition = $response->header('Content-Disposition') ?? "attachment; filename=\"session_{$sessionId}.{$type}\"";
        return response($response->body(), $response->status())
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', $contentDisposition);
    }

    public function getFeedbackList(Request $request)
    {
        return $this->proxyResponse($this->aiChatService->getAdminFeedbackList($request->all()));
    }
}
