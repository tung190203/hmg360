<?php

namespace App\Http\Middleware;

use App\Core\Module\Module;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $moduleSlug, ?string $modulePath = null): Response
    {
        $tenant = $request->user()?->tenant;

        $module = $tenant ? Module::query()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $moduleSlug)
            ->where('is_enabled', true)
            ->first() : null;

        $enabled = $module && (
            $modulePath === null
            || trim($module->path, '/') === trim($modulePath, '/')
        );

        abort_unless($enabled, 403, 'Module chưa khả dụng');

        $request->attributes->set('tenantModule', $module);

        return $next($request);
    }
}
