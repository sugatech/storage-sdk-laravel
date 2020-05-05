<?php

return [
    'api_url' => env('STORAGE_API_URL'),
    'oauth' => [
        'url' => env('STORAGE_OAUTH_URL', env('STORAGE_API_URL').'/oauth/token'),
        'client_id' => env('STORAGE_OAUTH_CLIENT_ID'),
        'client_secret' => env('STORAGE_OAUTH_CLIENT_SECRET'),
    ],
];
