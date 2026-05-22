<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\RolesPermissions\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'active.user',
    'tenant.db',
    'tenant.organizer',
    'module.enabled:roles_permissions,Tenants/TrungTamXucTienHaNoi/RolesPermissions',
])
    ->prefix('backend/roles')
    ->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('backend_core_roles');
        Route::get('/create', [RoleController::class, 'create'])->name('backend_core_roles_create');
        Route::post('/', [RoleController::class, 'store'])->name('backend_core_roles_store');
        Route::get('/{role}/edit', [RoleController::class, 'edit'])->name('backend_core_roles_edit');
        Route::put('/{role}', [RoleController::class, 'update'])->name('backend_core_roles_update');
    });
