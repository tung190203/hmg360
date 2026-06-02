<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Category\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:category,Tenants/TrungTamXucTienHaNoi/Category'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/category')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.category.')
    ->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::post('/', [CategoryController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('/create', [CategoryController::class, 'edit'])->name('create');
        Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('edit');
        Route::post('/save/{category?}', [CategoryController::class, 'save'])->name('save');
        Route::get('/delete/{id}', [CategoryController::class, 'delete'])->name('delete');
        Route::post('/bulk_delete', [CategoryController::class, 'bulkDelete'])->name('bulk_delete');
        Route::post('approve/{category}', [CategoryController::class, 'approve'])->name('approve');
        Route::post('/reject/{category}', [CategoryController::class, 'reject'])->name('reject');
    });
