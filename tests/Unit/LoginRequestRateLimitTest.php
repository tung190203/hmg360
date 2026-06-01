<?php

namespace Tests\Unit;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRequestRateLimitTest extends TestCase
{
    public function test_login_request_locks_after_configured_failed_attempts(): void
    {
        config([
            'auth.login_throttle.max_attempts' => 3,
            'auth.login_throttle.decay_seconds' => 120,
        ]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ], server: [
            'REMOTE_ADDR' => '127.0.0.10',
        ]);

        RateLimiter::clear($request->throttleKey());

        for ($attempt = 0; $attempt < 3; $attempt++) {
            RateLimiter::hit($request->throttleKey(), $request->loginDecaySeconds());
        }

        $this->expectException(ValidationException::class);

        $request->ensureIsNotRateLimited();
    }
}
