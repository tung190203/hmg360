<?php

use Illuminate\Support\Facades\Route;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\ContentController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\HotspotController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\SkinController;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:vr-tour,Tenants/TrungTamXucTienHaNoi/VrTour'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/vr-tour')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.vr_tour.')
    ->group(function () {
        Route::get('/', fn () => redirect()->route('tenant.trung_tam_xuc_tien_ha_noi.vr_tour.skin.index'))->name('index');

        Route::prefix('skin')->name('skin.')->group(function () {
            Route::get('index', [SkinController::class, 'index'])->name('index');
            Route::get('get-data/{vrtour_id}/{type}', [SkinController::class, 'getDataAll'])->name('getdata');
            Route::post('update-data/{vrtour_id}', [SkinController::class, 'updateDataAll'])->name('updatedata');
        });

        Route::prefix('hotspot')->name('hotspot.')->group(function () {
            Route::get('index', [HotspotController::class, 'index'])->name('index');
            Route::get('get-hotspot/{id}', [HotspotController::class, 'getHotspot'])->name('get_hotspot');
            Route::get('edit/{id}', [HotspotController::class, 'edit'])->name('edit');
            Route::post('save/{id}', [HotspotController::class, 'store'])->name('store');
        });

        Route::prefix('content')->name('content.')->group(function () {
            Route::get('index', [ContentController::class, 'index'])->name('index');
            Route::get('get-data/{vrtour_id}', [ContentController::class, 'getDataAll'])->name('getdata');
            Route::get('edit/{id}', [ContentController::class, 'edit'])->name('edit');
            Route::post('save/{id}', [ContentController::class, 'store'])->name('store');
        });
    });
