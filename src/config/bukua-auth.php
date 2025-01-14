<?php

return [
    'bukua_auth' => [
        'user_access_client_id' => env('BUKUA_USER_ACCESS_CLIENT_ID'),
        'user_access_client_secret' => env('BUKUA_USER_ACCESS_CLIENT_SECRET'),
        'user_access_callback_url' => env('BUKUA_USER_ACCESS_CALLBACK_URL'),
        'base_url' => env('BUKUA_BASE_URL', 'https://bukua-core.apptempest.com/'),
        'user_model' => env('BUKUA_USER_MODEL', 'App\\Models\\User::class'),
    ],
];
