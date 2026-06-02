<?php

use Illuminate\Support\Facades\Route;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Http\Controllers\ProjectController;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:projects,Tenants/TrungTamXucTienHaNoi/Projects'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/projects')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.projects.')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::post('/', [ProjectController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('create', [ProjectController::class, 'create'])->name('create');
        Route::get('edit/{project}', [ProjectController::class, 'edit'])->name('edit');
        Route::post('save/{project?}', [ProjectController::class, 'save'])->name('save');
        Route::get('delete/{project}', [ProjectController::class, 'delete'])->name('delete');
        Route::post('bulk_delete', [ProjectController::class, 'bulkDelete'])->name('bulk_delete');
        Route::post('approve/{project}', [ProjectController::class, 'approve'])->name('approve');
        Route::post('reject/{project}', [ProjectController::class, 'reject'])->name('reject');
        Route::get('export', [ProjectController::class, 'exportCsv'])->name('export');
    });
