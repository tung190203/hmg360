<?php

use App\Http\Controllers\Backend\Core\DashboardController;
use App\Http\Controllers\Backend\Core\ModuleController;
use App\Http\Controllers\Backend\Core\TenantController;
use App\Http\Controllers\Backend\FileManagerController;
use App\Http\Controllers\Backend\ProfileController;
use Illuminate\Support\Facades\Route;

Route::localized(function () {
    Route::get('/backend', function () {
        return redirect()->route('backend_dashboard');
    })->middleware(['auth'])->name('dashboard');

    Route::prefix('backend')->middleware(['auth', 'active.user'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend_dashboard');
        Route::get('/dashboard/export-logs', [DashboardController::class, 'exportLogs'])->name('backend_dashboard_export_logs');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('backend.profile.update');
        Route::get('file-manager', [FileManagerController::class, 'index'])->name('backend_file_manager');

        Route::middleware('platform.owner')->group(function () {
            Route::post('/tenant-db/test', [TenantController::class, 'testConnectionInput'])->name('backend_tenant_db_test');

            Route::prefix('tenants')->group(function () {
                Route::get('/', [TenantController::class, 'index'])->name('backend_core_tenants');
                Route::get('/create', [TenantController::class, 'create'])->name('backend_core_tenants_create');
                Route::post('/', [TenantController::class, 'store'])->name('backend_core_tenants_store');
                Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('backend_core_tenants_edit');
                Route::put('/{tenant}', [TenantController::class, 'update'])->name('backend_core_tenants_update');
            });

            Route::prefix('modules')->group(function () {
                Route::get('/', [ModuleController::class, 'index'])->name('backend_core_modules');
                Route::get('/create', [ModuleController::class, 'create'])->name('backend_core_modules_create');
                Route::post('/', [ModuleController::class, 'store'])->name('backend_core_modules_store');
                Route::post('/reorder', [ModuleController::class, 'reorder'])->name('backend_core_modules_reorder');
                Route::get('/{module}/edit', [ModuleController::class, 'edit'])->name('backend_core_modules_edit');
                Route::put('/{module}', [ModuleController::class, 'update'])->name('backend_core_modules_update');
                Route::post('/{module}/toggle', [ModuleController::class, 'toggle'])->name('backend_core_modules_toggle');
            });
        });
    });
});

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::any('/ckfinder/connector', '\CKSource\CKFinderBridge\Controller\CKFinderController@requestAction')->name('ckfinder_connector');
    Route::any('/ckfinder/browser', '\CKSource\CKFinderBridge\Controller\CKFinderController@browserAction')->name('ckfinder_browser');
});

if (is_dir(app_path('TenantModules'))) {
    $tenantModuleRouteFiles = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('TenantModules'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($tenantModuleRouteFiles as $tenantModuleRoutes) {
        if ($tenantModuleRoutes->isFile() && $tenantModuleRoutes->getFilename() === 'routes.php') {
            require $tenantModuleRoutes->getPathname();
        }
    }
}
