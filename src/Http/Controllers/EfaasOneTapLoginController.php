<?php

namespace Javaabu\EfaasSocialite\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class EfaasOneTapLoginController
{
    /**
     * Redirect the efaas one tap login requests to eFaas
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function efaasOneTapLogin(Request $request)
    {
        return Socialite::driver('efaas')->redirect();
    }
}
