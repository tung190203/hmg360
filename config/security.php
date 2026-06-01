<?php

return [
    'remove_headers' => [
        'X-Powered-By',
    ],

    'headers' => [
        'Strict-Transport-Security' => env(
            'SECURITY_HSTS',
            'max-age=31536000; includeSubDomains; preload'
        ),
        'Content-Security-Policy' => env(
            'SECURITY_CSP',
            "default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://fonts.gstatic.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: blob: https:; font-src 'self' data: https://fonts.gstatic.com; connect-src 'self' https:; frame-src 'self' blob:; media-src 'self' data: blob:"
        ),
        'X-Frame-Options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'Permissions-Policy' => env(
            'SECURITY_PERMISSIONS_POLICY',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        ),
    ],
];
