<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\User\Http\Controllers\GroupController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\User\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:user,Tenants/TrungTamXucTienHaNoi/User'])
    ->group(function () {
        Route::prefix('backend/user')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('backend_user');
            Route::post('save/{id?}', [UserController::class, 'save'])->name('backend_user_save');
            Route::get('create', [UserController::class, 'edit'])->name('backend_user_create');
            Route::get('edit/{id}', [UserController::class, 'edit'])->name('backend_user_edit');
            Route::get('delete/{id}', [UserController::class, 'delete'])->name('backend_user_delete');
            Route::post('approve/{id}', [UserController::class, 'approve'])->name('backend_user_approve');
            Route::post('reject/{id}', [UserController::class, 'reject'])->name('backend_user_reject');
        });

        Route::prefix('backend/group')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('backend_group');
            Route::post('save/{group?}', [GroupController::class, 'save'])->name('backend_group_save');
            Route::get('create', [GroupController::class, 'edit'])->name('backend_group_create');
            Route::get('edit/{group}', [GroupController::class, 'edit'])->name('backend_group_edit');
            Route::get('delete/{group}', [GroupController::class, 'delete'])->name('backend_group_delete');
        });
    });
