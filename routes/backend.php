<?php

use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\PopupController;
use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\FileManagerController;
use App\Http\Controllers\Backend\ProjectController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\ChatbotAdminController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\GroupController;
use App\Http\Controllers\Backend\GuestController;
use App\Http\Controllers\Backend\InvestMentGuideController;
use App\Http\Controllers\Backend\VrTour\SkinController;
use App\Http\Controllers\Backend\VrTour\HotspotController;
use App\Http\Controllers\Backend\VrTour\ContentController;
use App\Http\Controllers\Backend\ProfileController;
use App\Http\Controllers\Backend\AIChatMonitorController;
use Illuminate\Support\Facades\Route;

Route::localized(function () {
    Route::get('/backend', function () {
        return redirect()->route('backend_dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::prefix('backend')->middleware(['auth', 'can:backend_access'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend_dashboard');
        Route::get('/dashboard/export-logs', [DashboardController::class, 'exportLogs'])->name('backend_dashboard_export_logs');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('backend.profile.update');

        Route::prefix('category')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('backend_category');
            Route::post('/', [CategoryController::class, 'saveDataIndex'])->name('backend_category_save_data_index');
            Route::get('/create', [CategoryController::class, 'edit'])->name('backend_category_create');
            Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('backend_category_edit');
            Route::post('/save/{category?}', [CategoryController::class, 'save'])->name('backend_category_save');
            Route::get('/delete/{id}', [CategoryController::class, 'delete'])->name('backend_category_delete');
            Route::post('/bulk_delete', [CategoryController::class, 'bulkDelete'])->name('backend_category_bulk_delete');
            Route::post('approve/{category}', [CategoryController::class, 'approve'])->name('backend_category_approve');
            Route::post('/reject/{category}', [CategoryController::class, 'reject'])->name('backend_category_reject');
        });

        Route::prefix('menu')->group(function () {
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
        Route::prefix('popup')->group(function () {
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

        Route::prefix('guest')->group(function () {
            Route::get('/', [GuestController::class, 'index'])->name('backend_guest');
            Route::post('/', [GuestController::class, 'saveDataIndex'])->name('backend_guest_save_data_index');
            Route::get('/create', [GuestController::class, 'edit'])->name('backend_guest_create');
            Route::get('/edit/{guest}', [GuestController::class, 'edit'])->name('backend_guest_edit');
            Route::post('/save/{guest?}', [GuestController::class, 'save'])->name('backend_guest_save');
            Route::get('/delete/{id}', [GuestController::class, 'delete'])->name('backend_guest_delete');
            Route::post('/bulk_delete', [GuestController::class, 'bulkDelete'])->name('backend_guest_bulk_delete');
        });

        Route::prefix('post')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('backend_post');
            Route::post('/', [PostController::class, 'saveDataIndex'])->name('backend_post_save_data_index');
            Route::get('create', [PostController::class, 'edit'])->name('backend_post_create');
            Route::get('edit/{post}', [PostController::class, 'edit'])->name('backend_post_edit');
            Route::post('save/{post?}', [PostController::class, 'save'])->name('backend_post_save');
            Route::get('delete/{id}', [PostController::class, 'delete'])->name('backend_post_delete');
            Route::post('bulk_delete', [PostController::class, 'bulkDelete'])->name('backend_post_bulk_delete');
            Route::get('clone/{post}', [PostController::class, 'clone'])->name('backend_post_clone');
            Route::get('restore/{id}', [PostController::class, 'restore'])->name('backend_post_restore');
            Route::get('force-delete/{id}', [PostController::class, 'forceDelete'])->name('backend_post_force_delete');
            Route::get('import', [PostController::class, 'showImportForm'])->name('backend_post_show_import_form');
            Route::post('import', [PostController::class, 'importFromUrl'])->name('backend_post_import');
            Route::post('approve/{post}', [PostController::class, 'approve'])->name('backend_post_approve');
            Route::post('/reject/{post}', [PostController::class, 'reject'])->name('backend_post_reject');
        });

        Route::prefix('investment_guide')->group(function () {
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
            Route::post('/reject/{investment_guide}', [InvestmentGuideController::class, 'reject'])->name('backend_investment_guide_reject');
        });

        Route::get('file-manager', [FileManagerController::class, 'index'])->name('backend_file_manager');

        Route::prefix('setting')->group(function () {
            Route::get('/general', [SettingController::class, 'general'])->name('backend_setting_general');
            Route::get('/author', [SettingController::class, 'author'])->name('backend_setting_author');
            Route::get('/payment', [SettingController::class, 'payment'])->name('backend_setting_payment');
            Route::get('/social', [SettingController::class, 'social'])->name('backend_setting_social');
            Route::get('/seo', [SettingController::class, 'seo'])->name('backend_setting_seo');
            Route::get('/chatbot', [SettingController::class, 'chatbot'])->name('backend_setting_chatbot');
            Route::get('/chatbot/basic', [SettingController::class, 'chatbotBasic'])->name('backend_chatbot_basic');
            Route::get('/chatbot/sync', [SettingController::class, 'chatbotSync'])->name('backend_chatbot_sync');
            Route::get('/chatbot/prompts', [SettingController::class, 'chatbotPrompts'])->name('backend_chatbot_prompts');
            Route::get('/chatbot/blacklist', [SettingController::class, 'chatbotBlacklist'])->name('backend_chatbot_blacklist');
            Route::get('/chatbot/sessions', [SettingController::class, 'chatbotSessions'])->name('backend_chatbot_sessions');
            Route::get('/chatbot/knowledge', [SettingController::class, 'chatbotKnowledge'])->name('backend_chatbot_knowledge');
            Route::get('/chatbot/usage', [SettingController::class, 'chatbotUsage'])->name('backend_chatbot_usage');
            Route::get('/chatbot/webhooks', [AIChatMonitorController::class, 'webhookHistory'])->name('backend_chatbot_webhooks');
            Route::post('/save', [SettingController::class, 'save'])->name('backend_setting_save');
        });

        Route::prefix('chatbot-admin')->group(function () {
            Route::get('/sync/settings', [ChatbotAdminController::class, 'getSyncSettings']);
            Route::post('/sync/settings', [ChatbotAdminController::class, 'updateSyncSettings']);
            Route::post('/sync/trigger', [ChatbotAdminController::class, 'triggerSync']);
            Route::post('/sync/swap', [ChatbotAdminController::class, 'swapSlots']);
            Route::get('/sync/history', [ChatbotAdminController::class, 'getSyncHistory']);
            Route::get('/extract/config', [ChatbotAdminController::class, 'getExtractConfig']);
            Route::post('/extract', [ChatbotAdminController::class, 'extract']);
            Route::get('/knowledge/config', [ChatbotAdminController::class, 'getKnowledgeConfig']);
            Route::post('/knowledge', [ChatbotAdminController::class, 'createKnowledge']);
            Route::get('/knowledge/jobs', [ChatbotAdminController::class, 'getKnowledgeJobs']);
            Route::get('/knowledge/jobs/{jobId}', [ChatbotAdminController::class, 'getKnowledgeJob']);
            Route::get('/knowledge', [ChatbotAdminController::class, 'getKnowledgeDocs']);
            Route::get('/knowledge/{docId}', [ChatbotAdminController::class, 'getKnowledgeDoc']);
            Route::delete('/knowledge/{docId}', [ChatbotAdminController::class, 'deleteKnowledgeDoc']);
            Route::get('/usage', [ChatbotAdminController::class, 'getUsage']);
            Route::get('/usage/summary', [ChatbotAdminController::class, 'getUsageSummary']);

            Route::get('/prompts', [ChatbotAdminController::class, 'getPrompts']);
            Route::put('/prompts/{key}/{language}', [ChatbotAdminController::class, 'updatePrompt']);
            Route::post('/prompts/{key}/{language}/reset', [ChatbotAdminController::class, 'resetPrompt']);

            Route::get('/blacklist', [ChatbotAdminController::class, 'getBlacklist']);
            Route::post('/blacklist', [ChatbotAdminController::class, 'addBlacklistKeyword']);
            Route::put('/blacklist/{keywordId}', [ChatbotAdminController::class, 'updateBlacklistKeyword']);
            Route::delete('/blacklist/{keywordId}', [ChatbotAdminController::class, 'deleteBlacklistKeyword']);
            Route::put('/blacklist/refusal/{group}/{language}', [ChatbotAdminController::class, 'updateBlacklistRefusal']);
            Route::post('/blacklist/refusal/{group}/{language}/reset', [ChatbotAdminController::class, 'resetBlacklistRefusal']);
            Route::get('/blacklist/log', [ChatbotAdminController::class, 'getBlacklistLog']);

            Route::get('/sessions', [ChatbotAdminController::class, 'getSessions']);
            Route::get('/sessions/export', [ChatbotAdminController::class, 'exportSessions']);
            Route::get('/sessions/{sessionId}/export', [ChatbotAdminController::class, 'exportSingleSession']);
            Route::get('/sessions/{sessionId}', [ChatbotAdminController::class, 'getSessionDetail']);

            Route::get('/feedback', [ChatbotAdminController::class, 'getFeedbackList']);
        });

        Route::prefix('user')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('backend_user');
            Route::post('save/{user?}', [UserController::class, 'save'])->name('backend_user_save');
            Route::get('create', [UserController::class, 'edit'])->name('backend_user_create');
            Route::get('edit/{user}', [UserController::class, 'edit'])->name('backend_user_edit');
            Route::get('delete/{user}', [UserController::class, 'delete'])->name('backend_user_delete');
            Route::post('approve/{user}', [UserController::class, 'approve'])->name('backend_user_approve');
            Route::post('/reject/{user}', [UserController::class, 'reject'])->name('backend_user_reject');
        });

        Route::prefix('group')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('backend_group');
            Route::post('save/{group?}', [GroupController::class, 'save'])->name('backend_group_save');
            Route::get('create', [GroupController::class, 'edit'])->name('backend_group_create');
            Route::get('edit/{group}', [GroupController::class, 'edit'])->name('backend_group_edit');
            Route::get('delete/{group}', [GroupController::class, 'delete'])->name('backend_group_delete');
        });

        Route::prefix('project')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('backend_project');
            Route::post('/', [ProjectController::class, 'saveDataIndex'])->name('backend_project_save_data_index');
            Route::get('create', [ProjectController::class, 'edit'])->name('backend_project_create');
            Route::get('edit/{project}', [ProjectController::class, 'edit'])->name('backend_project_edit');
            Route::post('save/{project?}', [ProjectController::class, 'save'])->name('backend_project_save');
            Route::get('delete/{id}', [ProjectController::class, 'delete'])->name('backend_project_delete');
            Route::post('bulk_delete', [ProjectController::class, 'bulkDelete'])->name('backend_project_bulk_delete');
            Route::post('/approve/{project}', [ProjectController::class, 'approve'])->name('backend_project_approve');
            Route::post('/reject/{project}', [ProjectController::class, 'reject'])->name('backend_project_reject');
            Route::get('/export', [ProjectController::class, 'exportCsv'])->name('backend_project_export');
        });

        Route::prefix('vrtour')->group(function () {
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

        Route::prefix('ai-monitor')->group(function () {
            Route::get('/overview', [AIChatMonitorController::class, 'overview'])->name('backend_chatbot_overview');
            Route::get('/status', [AIChatMonitorController::class, 'getApiStatus'])->name('backend_ai_monitor_status');
            Route::get('/advanced-stats', [AIChatMonitorController::class, 'getAdvancedStats'])->name('backend_ai_monitor_advanced_stats');
            Route::get('/extra-stats', [AIChatMonitorController::class, 'getExtraStats'])->name('backend_ai_monitor_extra_stats');
        });
    });
});

Route::middleware(['auth'])->group(function () {
    Route::any('/ckfinder/connector', '\CKSource\CKFinderBridge\Controller\CKFinderController@requestAction')->name('ckfinder_connector');
    Route::any('/ckfinder/browser', '\CKSource\CKFinderBridge\Controller\CKFinderController@browserAction')->name('ckfinder_browser');
});
