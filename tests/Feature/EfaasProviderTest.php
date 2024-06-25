<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Javaabu\EfaasSocialite\Tests\TestSupport\Controllers\LoginController;

class EfaasProviderTest extends TestCase
{
    /** @test */
    public function it_can_redirect_to_efaas_login_page()
    {
        $this->withoutExceptionHandling();

        Route::get('/oauth/{socialite_provider}/callback', [LoginController::class, 'redirectToProvider'])->where('socialite_provider', 'efaas');

        $nonce = 'R3ztkHyHgafKh1k445wPKzzTqXBxI3bTXZSKv1HX';

        Str::createRandomStringsUsing(function () use ($nonce) {
            return $nonce;
        });

        $this->get('/oauth/efaas/callback')
            ->assertRedirect(
                'https://efaas.gov.mv/connect/authorize?'.
                'client_id='.self::CLIENT_ID.
                '&redirect_uri='. urlencode(self::REDIRECT_URL) .
                '&response_type=' . urlencode('code id_token') .
                '&response_mode=form_post'.
                '&scope=' . urlencode('openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status') .
                '&nonce=' . $nonce);
    }
}
