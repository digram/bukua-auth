<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bukua-auth')->middleware('web')->group(function () {
    Route::post('/authorize', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@authorize',
        'as' => 'bukua-auth.authorize',
    ]);

    Route::get('/callback', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@callback',
    ]);
});
