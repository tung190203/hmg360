<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Post\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:post,Tenants/TrungTamXucTienHaNoi/Post'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/post')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.post.')
    ->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::post('/', [PostController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('create', [PostController::class, 'edit'])->name('create');
        Route::get('edit/{post}', [PostController::class, 'edit'])->name('edit');
        Route::post('save/{post?}', [PostController::class, 'save'])->name('save');
        Route::get('delete/{id}', [PostController::class, 'delete'])->name('delete');
        Route::post('bulk_delete', [PostController::class, 'bulkDelete'])->name('bulk_delete');
        Route::get('clone/{post}', [PostController::class, 'clone'])->name('clone');
        Route::get('restore/{id}', [PostController::class, 'restore'])->name('restore');
        Route::get('force-delete/{id}', [PostController::class, 'forceDelete'])->name('force_delete');
        Route::get('import', [PostController::class, 'showImportForm'])->name('show_import_form');
        Route::post('import', [PostController::class, 'importFromUrl'])->name('import');
        Route::post('approve/{post}', [PostController::class, 'approve'])->name('approve');
        Route::post('/reject/{post}', [PostController::class, 'reject'])->name('reject');
    });
