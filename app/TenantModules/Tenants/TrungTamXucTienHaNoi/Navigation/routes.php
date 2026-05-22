<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Navigation\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:menu,Tenants/TrungTamXucTienHaNoi/Navigation'])
    ->prefix('backend/menu')
    ->group(function () {
        Route::get('/', [MenuController::class, 'index'])->name('backend_menu');
        Route::post('/', [MenuController::class, 'saveDataIndex'])->name('backend_menu_save_data_index');
        Route::get('/create', [MenuController::class, 'edit'])->name('backend_menu_create');
        Route::get('/edit/{menu}', [MenuController::class, 'edit'])->name('backend_menu_edit');
        Route::post('/save/{menu?}', [MenuController::class, 'save'])->name('backend_menu_save');
        Route::get('/delete/{id}', [MenuController::class, 'delete'])->name('backend_menu_delete');
        Route::post('/bulk_delete', [MenuController::class, 'bulkDelete'])->name('backend_menu_bulk_delete');
        Route::post('approve/{menu}', [MenuController::class, 'approve'])->name('backend_menu_approve');
        Route::post('/reject/{menu}', [MenuController::class, 'reject'])->name('backend_menu_reject');
    });
