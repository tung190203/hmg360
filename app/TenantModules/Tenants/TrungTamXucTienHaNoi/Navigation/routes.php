<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Navigation\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:menu,Tenants/TrungTamXucTienHaNoi/Navigation'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/menu')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.menu.')
    ->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('index');
        Route::post('/', [MenuController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('/create', [MenuController::class, 'edit'])->name('create');
        Route::get('/edit/{menu}', [MenuController::class, 'edit'])->name('edit');
        Route::post('/save/{menu?}', [MenuController::class, 'save'])->name('save');
        Route::get('/delete/{id}', [MenuController::class, 'delete'])->name('delete');
        Route::post('/bulk_delete', [MenuController::class, 'bulkDelete'])->name('bulk_delete');
        Route::post('approve/{menu}', [MenuController::class, 'approve'])->name('approve');
        Route::post('/reject/{menu}', [MenuController::class, 'reject'])->name('reject');
    });
