<?php

return [
    'bukua_auth' => [
        'client_id' => env('BUKUA_AUTH_CLIENT_ID'),
        'client_secret' => env('BUKUA_AUTH_CLIENT_SECRET'),
        'callback_url' => env('BUKUA_AUTH_CALLBACK_URL'),
        'base_url' => env('BUKUA_AUTH_BASE_URL', 'https://bukua-core.apptempest.com/'),
        'user_model' => env('BUKUA_AUTH_USER_MODEL', 'App\\Models\\User::class'),
    ],
];
