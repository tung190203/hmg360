<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản đã bị khóa hoặc không còn hoạt động.',
            ]);
        }

        return $next($request);
    }
}
