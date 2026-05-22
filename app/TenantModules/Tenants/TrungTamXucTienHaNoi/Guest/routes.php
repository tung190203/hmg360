<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Guest\Http\Controllers\GuestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:guest,Tenants/TrungTamXucTienHaNoi/Guest'])
    ->prefix('backend/guest')
    ->group(function () {
        Route::get('/', [GuestController::class, 'index'])->name('backend_guest');
        Route::post('/', [GuestController::class, 'saveDataIndex'])->name('backend_guest_save_data_index');
        Route::get('/create', [GuestController::class, 'edit'])->name('backend_guest_create');
        Route::get('/edit/{guest}', [GuestController::class, 'edit'])->name('backend_guest_edit');
        Route::post('/save/{guest?}', [GuestController::class, 'save'])->name('backend_guest_save');
        Route::get('/delete/{id}', [GuestController::class, 'delete'])->name('backend_guest_delete');
        Route::post('/bulk_delete', [GuestController::class, 'bulkDelete'])->name('backend_guest_bulk_delete');
    });
