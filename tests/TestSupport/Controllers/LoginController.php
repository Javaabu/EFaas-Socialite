<?php

namespace Javaabu\EfaasSocialite\Tests\TestSupport\Controllers;

use Laravel\Socialite\Facades\Socialite;

class LoginController
{
    /**
     * Redirect the user to the OAuth Provider.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
}
