<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isPlatformOwner(), 403, 'Chỉ platform owner được truy cập khu vực này.');

        return $next($request);
    }
}
