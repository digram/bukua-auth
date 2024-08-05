<?php

use Illuminate\Support\Facades\Route;

Route::prefix('bukua-auth')->middleware('web')->group(function () {
    // Redirect user to Bukua login page for approval
    Route::post('/authorize', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@authorize',
        'as' => 'bukua-auth.authorize',
    ]);

    // Process after user approval on Bukua
    Route::get('/callback', [
        'uses' => '\BukuaAuth\Controllers\BukuaAuthController@callback',
    ]);
});
