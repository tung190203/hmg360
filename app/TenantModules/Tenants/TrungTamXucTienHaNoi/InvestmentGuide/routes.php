<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\InvestmentGuide\Http\Controllers\InvestMentGuideController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:investment_guide,Tenants/TrungTamXucTienHaNoi/InvestmentGuide'])
    ->prefix('backend/investment_guide')
    ->group(function () {
        Route::get('/', [InvestMentGuideController::class, 'index'])->name('backend_investment_guide');
        Route::post('/', [InvestMentGuideController::class, 'saveDataIndex'])->name('backend_investment_guide_save_data_index');
        Route::get('create', [InvestMentGuideController::class, 'edit'])->name('backend_investment_guide_create');
        Route::get('edit/{investment_guide}', [InvestMentGuideController::class, 'edit'])->name('backend_investment_guide_edit');
        Route::post('save/{investment_guide?}', [InvestMentGuideController::class, 'save'])->name('backend_investment_guide_save');
        Route::get('delete/{id}', [InvestMentGuideController::class, 'delete'])->name('backend_investment_guide_delete');
        Route::post('bulk_delete', [InvestMentGuideController::class, 'bulkDelete'])->name('backend_investment_guide_bulk_delete');
        Route::get('clone/{investment_guide}', [InvestMentGuideController::class, 'clone'])->name('backend_investment_guide_clone');
        Route::get('restore/{id}', [InvestMentGuideController::class, 'restore'])->name('backend_investment_guide_restore');
        Route::get('force-delete/{id}', [InvestMentGuideController::class, 'forceDelete'])->name('backend_investment_guide_force_delete');
        Route::get('import', [InvestMentGuideController::class, 'showImportForm'])->name('backend_investment_guide_show_import_form');
        Route::post('import', [InvestMentGuideController::class, 'importFromUrl'])->name('backend_investment_guide_import');
        Route::post('approve/{investment_guide}', [InvestMentGuideController::class, 'approve'])->name('backend_investment_guide_approve');
        Route::post('/reject/{investment_guide}', [InvestMentGuideController::class, 'reject'])->name('backend_investment_guide_reject');
    });
