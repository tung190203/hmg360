<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Dashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:dashboard,Tenants/TrungTamXucTienHaNoi/Dashboard'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/dashboard')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.dashboard.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('export-logs', [DashboardController::class, 'exportLogs'])->name('export_logs');
    });
