<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOrganizer
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isTenantOrganizer(), 403, 'Chỉ organizer của tenant được truy cập khu vực này.');

        return $next($request);
    }
}
