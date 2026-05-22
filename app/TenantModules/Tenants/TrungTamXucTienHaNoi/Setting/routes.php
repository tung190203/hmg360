<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Setting\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:setting,Tenants/TrungTamXucTienHaNoi/Setting'])
    ->prefix('backend/setting')
    ->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('backend_setting_general');
        Route::get('/author', [SettingController::class, 'author'])->name('backend_setting_author');
        Route::get('/payment', [SettingController::class, 'payment'])->name('backend_setting_payment');
        Route::get('/social', [SettingController::class, 'social'])->name('backend_setting_social');
        Route::get('/seo', [SettingController::class, 'seo'])->name('backend_setting_seo');
        Route::post('/save', [SettingController::class, 'save'])->name('backend_setting_save');
    });
