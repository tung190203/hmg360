<?php

namespace App\Http\Controllers\Backend\Core;

use App\Core\Module\Module;
use App\Core\Permission\Permission;
use App\Core\Permission\Role;
use App\Core\Tenant\Tenant;
use App\Http\Controllers\Controller;
use App\Core\Tenant\TenantDatabaseManager;
use App\Models\User;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Dashboard\Http\Controllers\DashboardController as TenantDashboardController;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected string $selectedMainMenu = 'dashboard';

    public function index(Request $request)
    {
        if (! $request->user()->isPlatformOwner()) {
            $tenant = $request->user()->tenant;

            abort_unless($tenant, 403, 'User chưa được gắn tenant.');

            app(TenantDatabaseManager::class)->connect($tenant);

            abort_unless(
                Module::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('slug', 'dashboard')
                    ->where('is_enabled', true)
                    ->exists(),
                403,
                'Module chưa khả dụng'
            );

            return app(TenantDashboardController::class)->index($request);
        }

        return view('backend.core.dashboard', [
            'tenantCount' => Tenant::count(),
            'activeTenantCount' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
            'moduleCount' => Module::count(),
            'enabledModuleCount' => Module::where('is_enabled', true)->count(),
            'userCount' => User::count(),
            'roleCount' => Role::count(),
            'permissionCount' => Permission::count(),
            'recentTenants' => Tenant::withCount(['modules', 'users'])->latest()->limit(5)->get(),
        ]);
    }

    public function exportLogs(Request $request)
    {
        if (! $request->user()->isPlatformOwner()) {
            $tenant = $request->user()->tenant;

            abort_unless($tenant, 403, 'User chưa được gắn tenant.');

            app(TenantDatabaseManager::class)->connect($tenant);

            abort_unless(
                Module::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('slug', 'dashboard')
                    ->where('is_enabled', true)
                    ->exists(),
                403,
                'Module chưa khả dụng'
            );

            abort_unless(
                $request->user()->hasPermission('dashboard') || $request->user()->hasPermission('dashboard/view'),
                403,
                'Quyền hạn không đủ.'
            );

            return app(TenantDashboardController::class)->exportLogs($request);
        }

        abort(404);
    }
}
