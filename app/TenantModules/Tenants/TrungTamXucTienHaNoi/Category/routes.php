<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Category\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:category,Tenants/TrungTamXucTienHaNoi/Category'])
    ->prefix('backend/category')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('backend_category');
        Route::post('/', [CategoryController::class, 'saveDataIndex'])->name('backend_category_save_data_index');
        Route::get('/create', [CategoryController::class, 'edit'])->name('backend_category_create');
        Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('backend_category_edit');
        Route::post('/save/{category?}', [CategoryController::class, 'save'])->name('backend_category_save');
        Route::get('/delete/{id}', [CategoryController::class, 'delete'])->name('backend_category_delete');
        Route::post('/bulk_delete', [CategoryController::class, 'bulkDelete'])->name('backend_category_bulk_delete');
        Route::post('approve/{category}', [CategoryController::class, 'approve'])->name('backend_category_approve');
        Route::post('/reject/{category}', [CategoryController::class, 'reject'])->name('backend_category_reject');
    });
