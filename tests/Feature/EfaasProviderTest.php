<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Javaabu\EfaasSocialite\Tests\TestSupport\Controllers\LoginController;

class EfaasProviderTest extends TestCase
{
    /** @test */
    public function it_can_redirect_to_efaas_login_page()
    {
        $this->withoutExceptionHandling();

        Route::get('/oauth/{socialite_provider}/callback', [LoginController::class, 'redirectToProvider'])->where('socialite_provider', 'efaas');

        $response = $this->get('/oauth/efaas/callback')
                         ->assertRedirect();

        $redirect_url = $response->headers->get('Location');

        $this->assertStringStartsWith(
                'https://efaas.gov.mv/connect/authorize?'.
                'client_id='.self::CLIENT_ID.
                '&redirect_uri='. urlencode(self::REDIRECT_URL) .
                '&response_type=' . urlencode('code id_token') .
                '&response_mode=form_post'.
                '&scope=' . urlencode('openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status') .
                '&nonce=',
                $redirect_url
        );
    }

    /** @test */
    public function it_can_perform_efaas_one_tap_login_redirect()
    {
        $this->withoutExceptionHandling();

        $login_code = 'a5d9a8ac-d583-41a7-8844-545dd608fad7';

        $response = $this->get('/efaas-one-tap-login?efaas_login_code=' . $login_code)
            ->assertRedirect();

        $redirect_url = $response->headers->get('Location');

        $this->assertStringStartsWith(
            'https://efaas.gov.mv/connect/authorize?'.
            'client_id='.self::CLIENT_ID.
            '&redirect_uri='. urlencode(self::REDIRECT_URL) .
            '&response_type=' . urlencode('code id_token') .
            '&response_mode=form_post'.
            '&scope=' . urlencode('openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status') .
            '&acr_values=' . urlencode('efaas_login_code:' . $login_code) .
            '&nonce=',
            $redirect_url
        );
    }
}
