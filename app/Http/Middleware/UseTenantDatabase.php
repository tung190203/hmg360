<?php

namespace App\Http\Middleware;

use App\Core\Tenant\TenantDatabaseManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseTenantDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->user()?->tenant;

        if (! $tenant) {
            abort(403, 'User chưa được gắn tenant.');
        }

        app(TenantDatabaseManager::class)->connect($tenant);

        return $next($request);
    }
}
