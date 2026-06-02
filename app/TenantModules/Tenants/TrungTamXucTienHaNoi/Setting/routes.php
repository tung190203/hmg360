<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Setting\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:setting,Tenants/TrungTamXucTienHaNoi/Setting'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/setting')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.setting.')
    ->group(function () {
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::get('/author', [SettingController::class, 'author'])->name('author');
        Route::get('/payment', [SettingController::class, 'payment'])->name('payment');
        Route::get('/social', [SettingController::class, 'social'])->name('social');
        Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
        Route::post('/save', [SettingController::class, 'save'])->name('save');
    });
