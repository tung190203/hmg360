<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers;

use App\Core\Module\Module;
use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantDatabaseManager;
use App\Http\Controllers\Controller;
use App\Services\AiUsageAlertService;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\AiEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TtxtWebhookController extends Controller
{
    private const TENANT_SLUG = 'trung-tam-xuc-tien-ha-noi';
    private const MODULE_SLUG = 'chatbot_management';
    private const MODULE_PATH = 'Tenants/TrungTamXucTienHaNoi/ChatbotManagement';

    public function __invoke(Request $request): JsonResponse
    {
        $secret = (string) config('services.ai_chat.webhook_secret');
        if ($secret === '') {
            Log::warning('TTXT webhook rejected because TTXT_WEBHOOK_SECRET is not configured.');

            return response()->json(['message' => 'Webhook secret is not configured'], 500);
        }

        $signature = (string) $request->header('X-TTXT-Signature', '');
        if (! $this->validSignature($request->getContent(), $signature, $secret)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $eventId = (string) ($request->header('X-TTXT-Event-Id') ?: Arr::get($payload, 'event_id'));
        if ($eventId === '') {
            return response()->json(['message' => 'Missing event id'], 422);
        }

        try {
            $this->connectTenant();
        } catch (RuntimeException $exception) {
            Log::error('TTXT webhook tenant connection failed: ' . $exception->getMessage());

            return response()->json(['message' => 'Tenant is not available'], 503);
        }

        if (AiEvent::whereKey($eventId)->exists()) {
            return response()->json(['message' => 'Duplicate event ignored'], 200);
        }

        $eventType = (string) ($request->header('X-TTXT-Event') ?: Arr::get($payload, 'event'));

        AiEvent::create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'status' => Arr::get($payload, 'status'),
            'mode' => Arr::get($payload, 'mode'),
            'documents_uploaded' => Arr::get($payload, 'documents_uploaded'),
            'documents_failed' => Arr::get($payload, 'documents_failed'),
            'new_slot' => Arr::get($payload, 'new_slot'),
            'job_id' => Arr::get($payload, 'job_id'),
            'doc_id' => Arr::get($payload, 'doc_id'),
            'source_filename' => Arr::get($payload, 'source_filename'),
            'chunk_count' => Arr::get($payload, 'chunk_count'),
            'duration_s' => Arr::get($payload, 'duration_s'),
            'embedding_tokens' => Arr::get($payload, 'tokens.embedding_input') ?? Arr::get($payload, 'tokens.input'),
            'cost_usd_total' => Arr::get($payload, 'cost_usd_total', 0) ?? 0,
            'payload_json' => $payload,
            'received_at' => now(),
        ]);

        app(AiUsageAlertService::class)->notifyWebhookEvent($payload, $eventType);

        return response()->json(['message' => 'Event accepted']);
    }

    private function connectTenant(): void
    {
        $tenant = Tenant::query()
            ->where('slug', self::TENANT_SLUG)
            ->where('status', Tenant::STATUS_ACTIVE)
            ->first();

        if (! $tenant) {
            throw new RuntimeException('Tenant is not active.');
        }

        $moduleEnabled = Module::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', self::MODULE_SLUG)
            ->where('path', self::MODULE_PATH)
            ->where('is_enabled', true)
            ->exists();

        if (! $moduleEnabled) {
            throw new RuntimeException('Chatbot management module is not enabled.');
        }

        app(TenantDatabaseManager::class)->connect($tenant);
    }

    private function validSignature(string $body, string $signature, string $secret): bool
    {
        if (! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);

        return hash_equals($expected, $signature);
    }
}
