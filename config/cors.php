<?php

return [

    /*
     * Waar de browser vanaf een andere origin bij mag. Naast api/* staan hier
     * de Fortify-routes: het inloggen zelf gebeurt niet onder /api.
     */
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'login',
        'logout',
        'register',
        'two-factor-challenge',
        'forgot-password',
        'reset-password',
    ],

    'allowed_methods' => ['*'],

    /*
     * Geen '*' meer: met cookies erbij eist de browser een exacte origin. Het
     * React-dashboard draait standaard op poort 5173.
     */
    'allowed_origins' => array_filter(explode(',', (string) env('FRONTEND_URL', 'http://localhost:5173,http://127.0.0.1:5173'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     * De kern van de SPA-opzet: hiermee stuurt de browser het sessiecookie mee.
     * Zonder dit blijft elke aanroep oningelogd, hoe goed je ook inlogt.
     */
    'supports_credentials' => true,

];
