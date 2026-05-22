<?php

use Illuminate\Support\Facades\Route;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Http\Controllers\ProjectController;

Route::get('backend/modules/projects', [ProjectController::class, 'index'])
    ->middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:projects,Tenants/TrungTamXucTienHaNoi/Projects']);

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:projects,Tenants/TrungTamXucTienHaNoi/Projects'])
    ->prefix('backend/project')
    ->group(function () {
        Route::get('/', [ProjectController::class, 'index'])->name('backend_project');
        Route::post('/', [ProjectController::class, 'saveDataIndex'])->name('backend_project_save_data_index');
        Route::get('create', [ProjectController::class, 'create'])->name('backend_project_create');
        Route::get('edit/{project}', [ProjectController::class, 'edit'])->name('backend_project_edit');
        Route::post('save/{project?}', [ProjectController::class, 'save'])->name('backend_project_save');
        Route::get('delete/{project}', [ProjectController::class, 'delete'])->name('backend_project_delete');
        Route::post('bulk_delete', [ProjectController::class, 'bulkDelete'])->name('backend_project_bulk_delete');
        Route::get('export', [ProjectController::class, 'exportCsv'])->name('backend_project_export');
    });
