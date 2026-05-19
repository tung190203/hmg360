<?php

namespace App\Http\Controllers;

use App\Models\AiEvent;
use App\Services\AiUsageAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class TtxtWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = (string) config('services.ai_chat.webhook_secret');
        if ($secret === '') {
            Log::warning('TTXT webhook rejected because TTXT_WEBHOOK_SECRET is not configured.');
            return response()->json(['message' => 'Webhook secret is not configured'], 500);
        }

        $signature = (string) $request->header('X-TTXT-Signature', '');
        if (!$this->validSignature($request->getContent(), $signature, $secret)) {
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = $request->json()->all();
        $eventId = (string) ($request->header('X-TTXT-Event-Id') ?: Arr::get($payload, 'event_id'));
        if ($eventId === '') {
            return response()->json(['message' => 'Missing event id'], 422);
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

    private function validSignature(string $body, string $signature, string $secret): bool
    {
        if (!str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $secret);

        return hash_equals($expected, $signature);
    }
}
