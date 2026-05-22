<?php

use Illuminate\Support\Facades\Route;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\ContentController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\HotspotController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\VrTour\Http\Controllers\VrTour\SkinController;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:vr-tour,Tenants/TrungTamXucTienHaNoi/VrTour'])
    ->group(function () {
        Route::get('backend/modules/vr-tour', fn () => redirect()->route('backend_vrtour_skin_index'))->name('legacy.vr-tour.index');
        Route::prefix('backend/vrtour')->group(function () {
            Route::prefix('skin')->group(function () {
                Route::get('index', [SkinController::class, 'index'])->name('backend_vrtour_skin_index');
                Route::get('get-data/{vrtour_id}/{type}', [SkinController::class, 'getDataAll'])->name('backend_vrtour_skin_getdata');
                Route::post('update-data/{vrtour_id}', [SkinController::class, 'updateDataAll'])->name('backend_vrtour_skin_updatedata');
            });
            Route::prefix('hotspot')->group(function () {
                Route::get('index', [HotspotController::class, 'index'])->name('backend_vrtour_hotspot_index');
                Route::get('get-hotspot/{id}', [HotspotController::class, 'getHotspot'])->name('backend_vrtour_get_hotspot_index');
                Route::get('edit/{id}', [HotspotController::class, 'edit'])->name('backend_vrtour_hotspot_edit');
                Route::post('save/{id}', [HotspotController::class, 'store'])->name('backend_vrtour_hotspot_store');
            });
            Route::prefix('content')->group(function () {
                Route::get('index', [ContentController::class, 'index'])->name('backend_vrtour_content_index');
                Route::get('get-data/{vrtour_id}', [ContentController::class, 'getDataAll'])->name('backend_vrtour_content_getdata');
                Route::get('edit/{id}', [ContentController::class, 'edit'])->name('backend_vrtour_content_edit');
                Route::post('save/{id}', [ContentController::class, 'store'])->name('backend_vrtour_content_store');
            });
        });
    });
