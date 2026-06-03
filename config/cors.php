<?php

return [

    // Endpoint yang dilindungi CORS: API v1 + endpoint CSRF cookie Sanctum.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Origin frontend yang diizinkan (Vite). Wajib eksplisit (bukan '*') karena
    // request memakai credentials. Dev port lain di-cover oleh pattern di bawah.
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [
        '#^http://(localhost|127\.0\.0\.1)(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // WAJIB true untuk auth cookie-based Sanctum SPA.
    'supports_credentials' => true,
];
