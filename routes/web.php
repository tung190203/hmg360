<?php

use App\Http\Controllers\AIChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvestMentGuideController;
use App\Http\Controllers\TtxtWebhookController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
require __DIR__ . '/backend.php';

Route::post('/ttxt/webhook', TtxtWebhookController::class)->name('ttxt_webhook');

Route::localized(function () {
    Route::group(['prefix' => 'guest'], function () {
        Route::post('/register', [AuthController::class, 'register'])->name('guest_register');
        Route::post('/login', [AuthController::class, 'login'])->name('guest_login');
        Route::get('/logout', [AuthController::class, 'logout'])->name('guest_logout');
        Route::post('/update-info', [AuthController::class, 'updateAccount'])->name('guest_update_account');
        Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('google_login');
        Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
    });

    Route::get('/', [HomeController::class, 'index'])->name('home_page');
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/map/filter', [MapController::class, 'filter']);
    Route::get('/map/bounds', [MapController::class, 'getProjectsInBounds']);
    Route::get('/api/districts', [MapController::class, 'getDistricts'])->name('api_districts');
    Route::get('/projects', [HomeController::class, 'projects'])->name('projects');
    Route::get('/industrial-projects', [HomeController::class, 'industrialProjects'])->name('industrial_projects');
    Route::get('/project-detail/{slug}', [HomeController::class, 'projectDetail'])->name('project_detail');
    Route::get('/vrtour/{slug}', [HomeController::class, 'showVrtour'])->name('show_Vrtour');
    Route::get('/account', [HomeController::class, 'account'])->name('account');
    Route::get('/sitemap.xml', [HomeController::class, 'siteMap'])->name('site_map');
    Route::match(['get', 'post'], '/lien-he', [HomeController::class, 'contact'])->name('contact');
    Route::match(['get', 'post'], '/contact', [HomeController::class, 'contact'])->name('contact');
    Route::post('/interest', [InterestController::class, 'toggleInterest'])->name('interest');
    Route::get('/search', [HomeController::class, 'search'])->name('search');
    Route::get('/ajax-project-suggestions', [HomeController::class, 'ajaxSuggestions'])->name('ajax_project_suggestions');
    Route::group(['prefix' => 'chat'], function () {
        Route::post('/', [AIChatController::class, 'chat']);
        Route::get('/session/{sessionId}', [AIChatController::class, 'sessionHistory']);
        Route::delete('/session/{sessionId}', [AIChatController::class, 'deleteSession']);
        Route::post('/feedback', [AIChatController::class, 'submitFeedback']);
        Route::get('/health', [AIChatController::class, 'getHealthStatus']);
    });

    Route::get('{slug}-n{id}.html', [PostController::class, 'detail'])->where(['slug' => '[a-z0-9\-]+', 'id' => '[0-9]+'])->name('post_detail');
    Route::get('{slug}-p{id}.html', [InvestMentGuideController::class, 'detail'])->where(['slug' => '[a-z0-9\-]+', 'id' => '[0-9]+'])->name('investment_guide_detail');

    Route::get('{slug}', [CategoryController::class, 'index'])->where(['slug' => '[a-zA-Z0-9\-]+'])->name('category');
});
