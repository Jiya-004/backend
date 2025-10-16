<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Ensure 'api/*' is included to cover your routes

    'allowed_methods' => ['*'], // Allow all methods (GET, POST, PUT, DELETE, etc.)

    // --- CRITICAL FIX HERE ---
    'allowed_origins' => ['http://localhost:5173'], // or use ['*'] during development

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers (including Authorization header for tokens)

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // true only if using cookies/sessions

];
