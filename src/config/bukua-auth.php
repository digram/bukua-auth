<?php

return [
    'bukua_auth' => [
        'client_id'                 => env('BUKUA_USER_ACCESS_CLIENT_ID'),
        'client_secret'             => env('BUKUA_USER_ACCESS_CLIENT_SECRET'),
        'app_url'                   => env('BUKUA_USER_ACCESS_APP_URL'),
        'base_url'                  => env('BUKUA_BASE_URL', 'https://bukua-core.apptempest.com'),
        'user_model'                => env('BUKUA_USER_MODEL', 'App\\Models\\User::class'),
        'redirect_after_login'      => env('BUKUA_REDIRECT_AFTER_LOGIN', null),
        'secret'                    => env('BUKUA_USER_ACCESS_SECRET', "Some_Random_40_Character_String@#!$%^&*()_+"),
    ],
];
