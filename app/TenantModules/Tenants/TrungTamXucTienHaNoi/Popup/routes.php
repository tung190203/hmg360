<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Popup\Http\Controllers\PopupController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:popup,Tenants/TrungTamXucTienHaNoi/Popup'])
    ->prefix('backend/popup')
    ->group(function () {
        Route::get('/', [PopupController::class, 'index'])->name('backend_popup');
        Route::post('/', [PopupController::class, 'saveDataIndex'])->name('backend_popup_save_data_index');
        Route::get('/create', [PopupController::class, 'edit'])->name('backend_popup_create');
        Route::get('/edit/{popup}', [PopupController::class, 'edit'])->name('backend_popup_edit');
        Route::post('/save/{popup?}', [PopupController::class, 'save'])->name('backend_popup_save');
        Route::get('/delete/{id}', [PopupController::class, 'delete'])->name('backend_popup_delete');
        Route::post('/bulk_delete', [PopupController::class, 'bulkDelete'])->name('backend_popup_bulk_delete');
        Route::post('approve/{popup}', [PopupController::class, 'approve'])->name('backend_popup_approve');
        Route::post('/reject/{popup}', [PopupController::class, 'reject'])->name('backend_popup_reject');
    });
