<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bukua-auth')->middleware('web')->group(function () {
    Route::post('/authorize', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@authorize',
        'as' => 'bukua-auth.authorize',
    ])->middleware('throttle:12,1'); // 12 requests per minute

    Route::get('/callback', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@callback',
    ])->middleware('throttle:20,1');

    Route::get('/autologin', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@authorize',
        'as' => 'bukua-auth.autologin',
    ])->middleware('throttle:12,1');
});
