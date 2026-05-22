<?php

use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';
require __DIR__ . '/backend.php';

Route::localized(function () {
    Route::get('/', fn () => redirect()->route('backend_dashboard'));
});
