<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Popup\Http\Controllers\PopupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:popup,Tenants/TrungTamXucTienHaNoi/Popup'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/popup')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.popup.')
    ->group(function () {
        Route::get('/', [PopupController::class, 'index'])->name('index');
        Route::post('/', [PopupController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('/create', [PopupController::class, 'edit'])->name('create');
        Route::get('/edit/{popup}', [PopupController::class, 'edit'])->name('edit');
        Route::post('/save/{popup?}', [PopupController::class, 'save'])->name('save');
        Route::get('/delete/{id}', [PopupController::class, 'delete'])->name('delete');
        Route::post('/bulk_delete', [PopupController::class, 'bulkDelete'])->name('bulk_delete');
        Route::post('approve/{popup}', [PopupController::class, 'approve'])->name('approve');
        Route::post('/reject/{popup}', [PopupController::class, 'reject'])->name('reject');
    });
