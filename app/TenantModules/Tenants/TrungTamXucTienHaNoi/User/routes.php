<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\User\Http\Controllers\GroupController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:user,Tenants/TrungTamXucTienHaNoi/User'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/user')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.user.')
    ->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::post('save/{id?}', [UserController::class, 'save'])->name('save');
            Route::get('create', [UserController::class, 'edit'])->name('create');
            Route::get('edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::get('delete/{id}', [UserController::class, 'delete'])->name('delete');
            Route::post('approve/{id}', [UserController::class, 'approve'])->name('approve');
            Route::post('reject/{id}', [UserController::class, 'reject'])->name('reject');
        });

        Route::prefix('groups')->name('groups.')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('index');
            Route::post('save/{group?}', [GroupController::class, 'save'])->name('save');
            Route::get('create', [GroupController::class, 'edit'])->name('create');
            Route::get('edit/{group}', [GroupController::class, 'edit'])->name('edit');
            Route::get('delete/{group}', [GroupController::class, 'delete'])->name('delete');
        });
    });
