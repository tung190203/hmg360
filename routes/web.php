<?php

use App\Http\Controllers\TtxtWebhookController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
require __DIR__ . '/backend.php';

Route::post('/ttxt/webhook', TtxtWebhookController::class)->name('ttxt_webhook');

Route::localized(function () {
    Route::get('/', fn () => redirect()->route('backend_dashboard'));
});
