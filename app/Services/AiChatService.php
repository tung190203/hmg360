<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiChatService
{
    protected $baseUrl;
    protected $apiKey;
    protected $apiAdminKey;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_chat.api_url');
        $this->apiKey = config('services.ai_chat.api_key');
        $this->apiAdminKey = config('services.ai_chat.api_admin_key');
    }

    protected function client()
    {
        return Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'X-Admin-API-Key' => $this->apiAdminKey,
            'Content-Type' => 'application/json'
        ]);
    }

    protected function multipartClient()
    {
        return Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'X-Admin-API-Key' => $this->apiAdminKey,
        ]);
    }

    public function sendFeedback($data)
    {
        return $this->client()->post($this->baseUrl . '/api/v1/chat/feedback', [
            'session_id' => $data['session_id'],
            'message_id' => $data['message_id'],
            'rating' => $data['rating'],
            'feedback_type' => $data['feedback_type'] ?? 'helpful',
            'comment' => $data['comment'] ?? null
        ]);
    }

    public function chat(array $payload)
    {
        return $this->client()->post($this->baseUrl . '/api/v1/chat', $payload);
    }

    public function extractContent($file, string $summaryMode = 'auto', string $language = 'auto')
    {
        return $this->multipartClient()
            ->attach('file', fopen($file->getRealPath(), 'r'), $file->getClientOriginalName())
            ->post($this->baseUrl . '/api/v1/admin/extract', [
                'summary_mode' => $summaryMode,
                'language' => $language,
            ]);
    }

    public function extractContentFromPath(string $path, ?string $filename = null, string $summaryMode = 'auto', string $language = 'auto')
    {
        return $this->multipartClient()
            ->attach('file', fopen($path, 'r'), $filename ?: basename($path))
            ->post($this->baseUrl . '/api/v1/admin/extract', [
                'summary_mode' => $summaryMode,
                'language' => $language,
            ]);
    }

    public function getExtractConfig()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/extract/config');
    }

    public function createKnowledgeFromPath(string $path, ?string $filename = null, array $data = [])
    {
        return $this->multipartClient()
            ->attach('file', fopen($path, 'r'), $filename ?: basename($path))
            ->post($this->baseUrl . '/api/v1/admin/knowledge', array_filter([
                'title' => $data['title'] ?? null,
                'language' => $data['language'] ?? 'auto',
                'summary_mode' => $data['summary_mode'] ?? 'none',
            ], fn ($value) => $value !== null && $value !== ''));
    }

    public function createKnowledgeFromText(array $data)
    {
        return $this->multipartClient()->asMultipart()->post($this->baseUrl . '/api/v1/admin/knowledge', array_filter([
            'text' => $data['text'] ?? '',
            'title' => $data['title'] ?? null,
            'language' => $data['language'] ?? 'auto',
            'summary_mode' => $data['summary_mode'] ?? 'none',
        ], fn ($value) => $value !== null && $value !== ''));
    }

    public function getKnowledgeConfig()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/knowledge-config');
    }

    public function getKnowledgeJobs(array $params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/knowledge/jobs', $params);
    }

    public function getKnowledgeJob(string $jobId)
    {
        return $this->client()->get($this->baseUrl . "/api/v1/admin/knowledge/jobs/{$jobId}");
    }

    public function getKnowledgeDocs(array $params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/knowledge', $params);
    }

    public function getKnowledgeDoc(string $docId)
    {
        return $this->client()->get($this->baseUrl . "/api/v1/admin/knowledge/{$docId}");
    }

    public function deleteKnowledgeDoc(string $docId)
    {
        return $this->client()->delete($this->baseUrl . "/api/v1/admin/knowledge/{$docId}");
    }

    public function getAdminUsage(array $params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/usage', $params);
    }

    public function getAdminUsageSummary(array $params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/usage/summary', $params);
    }

    public function getAdminSyncHistory(array $params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/sync/history', $params);
    }

    public function getSessionHistory($sessionId)
    {
        return $this->client()->get($this->baseUrl . "/api/v1/session/{$sessionId}");
    }

    public function deleteSession($sessionId)
    {
        return $this->client()->delete($this->baseUrl . "/api/v1/session/{$sessionId}");
    }

    public function getHealthStatus()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/health');
    }

    public function getStatus()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/status');
    }

    public function getMetrics()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/metrics');
    }

    /**
     * Admin Statistics Methods
     */

    public function getAdminOverview($startDate = null, $endDate = null)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/overview', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function getAdminDaily($startDate = null, $endDate = null)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/daily', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function getAdminIntents($startDate = null, $endDate = null)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/intents', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    public function getAdminLatency($dateFrom = null, $dateTo = null)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/latency', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
    }

    public function getAdminFallback($dateFrom = null, $dateTo = null)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/fallback', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ]);
    }

    public function getAdminTopQuestions($dateFrom = null, $dateTo = null, $limit = 10, $language = 'vi')
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/top-questions', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'limit' => $limit,
            'language' => $language
        ]);
    }

    public function getAdminHealth()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/health');
    }

    public function getAdminKnowledge($topDistricts = 10)
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/knowledge', [
            'top_districts' => $topDistricts
        ]);
    }

    public function getAdminFeedbackStats($dateFrom = null, $dateTo = null, $language = 'vi')
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/stats/feedback', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'language' => $language
        ]);
    }

    public function getAdminFeedbackList($params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/feedback', $params);
    }

    public function getAdminSessions($params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/sessions', $params);
    }

    public function getAdminSessionDetail($sessionId)
    {
        return $this->client()->get($this->baseUrl . "/api/v1/admin/sessions/{$sessionId}");
    }

    public function exportAdminSessions($params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/sessions/export', $params);
    }

    public function exportAdminSingleSession($sessionId, $type = 'json')
    {
        return $this->client()->get($this->baseUrl . "/api/v1/admin/sessions/{$sessionId}/export", ['type' => $type]);
    }

    public function getAdminSyncSettings()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/sync/settings');
    }

    public function updateAdminSyncSettings(array $data)
    {
        return $this->client()->put($this->baseUrl . '/api/v1/admin/sync/settings', $data);
    }

    public function triggerAdminSync($mode = 'delta')
    {
        return $this->client()->post($this->baseUrl . '/api/v1/admin/sync/trigger', ['mode' => $mode]);
    }

    public function swapAdminSyncSlots()
    {
        return $this->client()->post($this->baseUrl . '/api/v1/admin/sync/swap');
    }

    public function getAdminPrompts()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/prompts');
    }

    public function updateAdminPrompt($key, $language, $content)
    {
        return $this->client()->put($this->baseUrl . "/api/v1/admin/prompts/{$key}/{$language}", [
            'content' => $content
        ]);
    }

    public function resetAdminPrompt($key, $language)
    {
        return $this->client()->post($this->baseUrl . "/api/v1/admin/prompts/{$key}/{$language}/reset");
    }

    public function getAdminBlacklist()
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/blacklist');
    }

    public function addAdminBlacklistKeyword(array $data)
    {
        return $this->client()->post($this->baseUrl . '/api/v1/admin/blacklist', $data);
    }

    public function updateAdminBlacklistKeyword($keywordId, array $data)
    {
        return $this->client()->put($this->baseUrl . "/api/v1/admin/blacklist/{$keywordId}", $data);
    }

    public function deleteAdminBlacklistKeyword($keywordId)
    {
        return $this->client()->delete($this->baseUrl . "/api/v1/admin/blacklist/{$keywordId}");
    }

    public function updateAdminBlacklistRefusal($group, $language, $content)
    {
        return $this->client()->put($this->baseUrl . "/api/v1/admin/blacklist/refusal/{$group}/{$language}", [
            'content' => $content
        ]);
    }

    public function resetAdminBlacklistRefusal($group, $language)
    {
        return $this->client()->post($this->baseUrl . "/api/v1/admin/blacklist/refusal/{$group}/{$language}/reset");
    }

    public function getAdminBlacklistLog($params = [])
    {
        return $this->client()->get($this->baseUrl . '/api/v1/admin/blacklist/log', $params);
    }
}
