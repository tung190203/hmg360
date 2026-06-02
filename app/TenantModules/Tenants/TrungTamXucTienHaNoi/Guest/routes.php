<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Guest\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:guest,Tenants/TrungTamXucTienHaNoi/Guest'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/guest')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.guest.')
    ->group(function () {
        Route::get('/', [GuestController::class, 'index'])->name('index');
        Route::post('/', [GuestController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('/create', [GuestController::class, 'edit'])->name('create');
        Route::get('/edit/{guest}', [GuestController::class, 'edit'])->name('edit');
        Route::post('/save/{guest?}', [GuestController::class, 'save'])->name('save');
        Route::get('/delete/{id}', [GuestController::class, 'delete'])->name('delete');
        Route::post('/bulk_delete', [GuestController::class, 'bulkDelete'])->name('bulk_delete');
    });
