<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Allow CORS on API routes
    'allowed_methods' => ['*'],  // Allow all HTTP methods (GET, POST, etc.)
    'allowed_origins' => ['*'],  // Allow requests from any origin (change if needed)
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],  // Allow all headers
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,  // Set to true if using cookies or auth
];
