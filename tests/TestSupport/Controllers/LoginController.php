<?php

namespace Javaabu\EfaasSocialite\Tests\TestSupport\Controllers;

use App\Models\Applicant;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Javaabu\EfaasSocialite\EfaasProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;

class LoginController
{
    /**
     * Redirect the user to the OAuth Provider.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.  Check if the user already exists in our
     * database by looking up their provider_id in the database.
     * If the user exists, log them in. Otherwise, create a new user then log them in. After that
     * redirect them to the authenticated users homepage.
     *
     * @param  Request  $request
     * @param           $provider
     * @return Application|RedirectResponse|Response|Redirector
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        /** @var EfaasProvider $provider */
        $provider = Socialite::driver($provider);
        $provider->setRequest($request);

        /** @var User $oauth_user */
        $oauth_user = $provider->user();


        session()->put('efaas_user', $oauth_user);

        // redirect to home
        return redirect($this->redirectPath());
    }

    protected function redirectPath()
    {
        return '/';
    }
}
