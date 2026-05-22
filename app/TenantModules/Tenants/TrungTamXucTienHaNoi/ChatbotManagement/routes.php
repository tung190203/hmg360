<?php

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers\AIChatMonitorController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers\ChatbotSettingController;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\ChatbotManagement\Http\Controllers\ChatbotAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active.user', 'tenant.db', 'module.enabled:chatbot_management,Tenants/TrungTamXucTienHaNoi/ChatbotManagement'])
    ->group(function () {
        Route::prefix('backend/ai-monitor')->group(function () {
            Route::get('/overview', [AIChatMonitorController::class, 'overview'])->name('backend_chatbot_overview');
            Route::get('/webhooks', [AIChatMonitorController::class, 'webhookHistory'])->name('backend_chatbot_webhooks');
            Route::get('/status', [AIChatMonitorController::class, 'getApiStatus'])->name('backend_ai_monitor_status');
            Route::get('/advanced-stats', [AIChatMonitorController::class, 'getAdvancedStats'])->name('backend_ai_monitor_advanced_stats');
            Route::get('/extra-stats', [AIChatMonitorController::class, 'getExtraStats'])->name('backend_ai_monitor_extra_stats');
        });

        Route::prefix('backend/setting/chatbot')->group(function () {
            Route::get('/', [ChatbotSettingController::class, 'index'])->name('backend_setting_chatbot');
            Route::get('/basic', [ChatbotSettingController::class, 'basic'])->name('backend_chatbot_basic');
            Route::get('/sync', [ChatbotSettingController::class, 'sync'])->name('backend_chatbot_sync');
            Route::get('/prompts', [ChatbotSettingController::class, 'prompts'])->name('backend_chatbot_prompts');
            Route::get('/blacklist', [ChatbotSettingController::class, 'blacklist'])->name('backend_chatbot_blacklist');
            Route::get('/sessions', [ChatbotSettingController::class, 'sessions'])->name('backend_chatbot_sessions');
            Route::get('/knowledge', [ChatbotSettingController::class, 'knowledge'])->name('backend_chatbot_knowledge');
            Route::get('/usage', [ChatbotSettingController::class, 'usage'])->name('backend_chatbot_usage');
        });

        Route::prefix('backend/chatbot-admin')->group(function () {
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
    });
