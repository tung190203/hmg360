<?php

namespace App\Http\Controllers;

use App\Models\AiUsageLog;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AIChatController extends Controller
{
    protected $aiChatService;

    public function __construct(AiChatService $aiChatService)
    {
        $this->aiChatService = $aiChatService;
    }

    public function chat(Request $request)
    {
        $payload = [
            'language' => "auto",
            'message' => $request->message,
            'session_id' => $request->session_id,
        ];
        if ($request->has('model')) {
            $payload['model_name'] = $request->model;
        }

        $response = $this->aiChatService->chat($payload);
        $body = $response->json() ?? [];

        if ($response->successful()) {
            $tokens = (array) Arr::get($body, 'tokens', []);

            AiUsageLog::create([
                'endpoint' => '/chat',
                'user_id' => optional(auth('web')->user())->id ?: optional(auth('guest')->user())->id,
                'model_used' => Arr::get($body, 'model_used') ?: Arr::get($tokens, 'model'),
                'input_tokens' => (int) (Arr::get($tokens, 'input') ?? Arr::get($tokens, 'input_tokens') ?? 0),
                'output_tokens' => (int) (Arr::get($tokens, 'output') ?? Arr::get($tokens, 'output_tokens') ?? 0),
                'cost_usd' => (float) (Arr::get($tokens, 'cost_usd') ?? Arr::get($body, 'cost_usd') ?? 0),
                'payload_json' => $body,
                'called_at' => now(),
            ]);
        }

        return response()->json($body, $response->status());
    }

    public function sessionHistory($sessionId)
    {
        $response = $this->aiChatService->getSessionHistory($sessionId);

        return response()->json($response->json());
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
            'comment' => $request->comment
        ]);

        return response()->json($response->json(), $response->status());
    }

    public function getHealthStatus()
    {
        $response = $this->aiChatService->getHealthStatus();
        return response()->json($response->json());
    }
}
