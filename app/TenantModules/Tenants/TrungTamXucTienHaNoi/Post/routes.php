<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Post\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:post,Tenants/TrungTamXucTienHaNoi/Post'])
    ->prefix('backend/post')
    ->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('backend_post');
        Route::post('/', [PostController::class, 'saveDataIndex'])->name('backend_post_save_data_index');
        Route::get('create', [PostController::class, 'edit'])->name('backend_post_create');
        Route::get('edit/{post}', [PostController::class, 'edit'])->name('backend_post_edit');
        Route::post('save/{post?}', [PostController::class, 'save'])->name('backend_post_save');
        Route::get('delete/{id}', [PostController::class, 'delete'])->name('backend_post_delete');
        Route::post('bulk_delete', [PostController::class, 'bulkDelete'])->name('backend_post_bulk_delete');
        Route::get('clone/{post}', [PostController::class, 'clone'])->name('backend_post_clone');
        Route::get('restore/{id}', [PostController::class, 'restore'])->name('backend_post_restore');
        Route::get('force-delete/{id}', [PostController::class, 'forceDelete'])->name('backend_post_force_delete');
        Route::get('import', [PostController::class, 'showImportForm'])->name('backend_post_show_import_form');
        Route::post('import', [PostController::class, 'importFromUrl'])->name('backend_post_import');
        Route::post('approve/{post}', [PostController::class, 'approve'])->name('backend_post_approve');
        Route::post('/reject/{post}', [PostController::class, 'reject'])->name('backend_post_reject');
    });
