<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/efaas-one-tap-login', [
        'uses' => 'EfaasOneTapLoginController@efaasOneTapLogin',
        'as' => 'one-tap-login',
    ]);
});
