<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\InvestmentGuide\Http\Controllers\InvestMentGuideController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:investment_guide,Tenants/TrungTamXucTienHaNoi/InvestmentGuide'])
    ->prefix('backend/tenants/trung-tam-xuc-tien-ha-noi/modules/investment-guide')
    ->name('tenant.trung_tam_xuc_tien_ha_noi.investment_guide.')
    ->group(function () {
        Route::get('/', [InvestMentGuideController::class, 'index'])->name('index');
        Route::post('/', [InvestMentGuideController::class, 'saveDataIndex'])->name('save_data_index');
        Route::get('create', [InvestMentGuideController::class, 'edit'])->name('create');
        Route::get('edit/{investment_guide}', [InvestMentGuideController::class, 'edit'])->name('edit');
        Route::post('save/{investment_guide?}', [InvestMentGuideController::class, 'save'])->name('save');
        Route::get('delete/{id}', [InvestMentGuideController::class, 'delete'])->name('delete');
        Route::post('bulk_delete', [InvestMentGuideController::class, 'bulkDelete'])->name('bulk_delete');
        Route::get('clone/{investment_guide}', [InvestMentGuideController::class, 'clone'])->name('clone');
        Route::get('restore/{id}', [InvestMentGuideController::class, 'restore'])->name('restore');
        Route::get('force-delete/{id}', [InvestMentGuideController::class, 'forceDelete'])->name('force_delete');
        Route::get('import', [InvestMentGuideController::class, 'showImportForm'])->name('show_import_form');
        Route::post('import', [InvestMentGuideController::class, 'importFromUrl'])->name('import');
        Route::post('approve/{investment_guide}', [InvestMentGuideController::class, 'approve'])->name('approve');
        Route::post('/reject/{investment_guide}', [InvestMentGuideController::class, 'reject'])->name('reject');
    });
