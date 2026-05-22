<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class AiChatService
{
    protected $baseUrl;
    protected $apiKey;
    protected $adminKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_chat.api_url');
        $this->apiKey = config('services.ai_chat.api_key');
        $this->adminKey = config('services.ai_chat.api_admin_key');
    }

    protected function headers(array $extra = [])
    {
        $headers = [
            'X-API-Key' => $this->apiKey,
            'X-Admin-API-Key' => $this->adminKey,
            'Content-Type' => 'application/json',
        ];

        return array_merge($headers, $extra);
    }

    public function getHealthStatus()
    {
        return Http::withHeaders($this->headers())->timeout(5)->get($this->baseUrl . '/api/v1/health');
    }

    public function getStatus()
    {
        return Http::withHeaders($this->headers())->timeout(5)->get($this->baseUrl . '/api/v1/status');
    }

    public function getMetrics()
    {
        return Http::withHeaders($this->headers())->timeout(5)->get($this->baseUrl . '/api/v1/metrics');
    }

    // --- Admin / Sync ---
    public function getAdminSyncSettings()
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sync/settings');
    }

    public function updateAdminSyncSettings(array $payload)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/sync/settings', $payload);
    }

    public function triggerAdminSync(string $mode = 'delta')
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/sync/trigger', ['mode' => $mode]);
    }

    public function swapAdminSyncSlots()
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/sync/swap');
    }

    // --- Extract ---
    public function getExtractConfig()
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/extract/config');
    }

    public function extractContent(UploadedFile $file, string $summaryMode = 'auto', string $language = 'auto')
    {
        $response = Http::withHeaders($this->headers())
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->post($this->baseUrl . '/api/v1/admin/extract', [
                'summary_mode' => $summaryMode,
                'language' => $language,
            ]);

        return $response;
    }

    public function extractContentFromPath(string $path, string $filename, string $summaryMode = 'auto', string $language = 'auto')
    {
        if (!is_file($path)) {
            return Http::response(['error' => 'file_not_found'], 404);
        }

        $response = Http::withHeaders($this->headers())
            ->attach('file', fopen($path, 'r'), $filename)
            ->post($this->baseUrl . '/api/v1/admin/extract', [
                'summary_mode' => $summaryMode,
                'language' => $language,
            ]);

        return $response;
    }

    // --- Knowledge ---
    public function getKnowledgeConfig()
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/knowledge/config');
    }

    public function createKnowledgeFromPath(string $path, string $filename, array $payload = [])
    {
        if (!is_file($path)) {
            return Http::response(['error' => 'file_not_found'], 404);
        }

        $response = Http::withHeaders($this->headers())
            ->attach('file', fopen($path, 'r'), $filename)
            ->post($this->baseUrl . '/api/v1/admin/knowledge', $payload);

        return $response;
    }

    public function createKnowledgeFromText(array $payload)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/knowledge/text', $payload);
    }

    public function getKnowledgeJobs(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/knowledge/jobs', $query);
    }

    public function getKnowledgeJob($jobId)
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/knowledge/jobs/' . $jobId);
    }

    public function getKnowledgeDocs(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/knowledge/docs', $query);
    }

    public function getKnowledgeDoc($docId)
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/knowledge/docs/' . $docId);
    }

    public function deleteKnowledgeDoc($docId)
    {
        return Http::withHeaders($this->headers())->delete($this->baseUrl . '/api/v1/admin/knowledge/docs/' . $docId);
    }

    // --- Usage / Sessions / Prompts / Blacklist / Feedback ---
    public function getAdminUsage(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/usage', $query);
    }

    public function getAdminUsageSummary(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/usage/summary', $query);
    }

    public function getAdminSyncHistory(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sync/history', $query);
    }

    public function getAdminPrompts()
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/prompts');
    }

    public function updateAdminPrompt($key, $language, $content)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/prompts/' . $key . '/' . $language, ['content' => $content]);
    }

    public function resetAdminPrompt($key, $language)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/prompts/' . $key . '/' . $language . '/reset');
    }

    public function getAdminBlacklist()
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/blacklist');
    }

    public function addAdminBlacklistKeyword(array $payload)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/blacklist', $payload);
    }

    public function updateAdminBlacklistKeyword($keywordId, array $payload)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/blacklist/' . $keywordId, $payload);
    }

    public function deleteAdminBlacklistKeyword($keywordId)
    {
        return Http::withHeaders($this->headers())->delete($this->baseUrl . '/api/v1/admin/blacklist/' . $keywordId);
    }

    public function updateAdminBlacklistRefusal($group, $language, $content)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/blacklist/refusal/' . $group . '/' . $language, ['content' => $content]);
    }

    public function resetAdminBlacklistRefusal($group, $language)
    {
        return Http::withHeaders($this->headers())->post($this->baseUrl . '/api/v1/admin/blacklist/refusal/' . $group . '/' . $language . '/reset');
    }

    public function getAdminBlacklistLog(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/blacklist/log', $query);
    }

    public function getAdminSessions(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sessions', $query);
    }

    public function getAdminSessionDetail($sessionId)
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sessions/' . $sessionId);
    }

    public function exportAdminSessions(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sessions/export', $query);
    }

    public function exportAdminSingleSession($sessionId, $type = 'json')
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/sessions/' . $sessionId . '/export', ['type' => $type]);
    }

    public function getAdminFeedbackList(array $query = [])
    {
        return Http::withHeaders($this->headers())->get($this->baseUrl . '/api/v1/admin/feedback', $query);
    }
}
