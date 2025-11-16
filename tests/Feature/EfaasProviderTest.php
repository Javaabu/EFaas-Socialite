<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\EfaasSocialite\EfaasAddress;
use Javaabu\EfaasSocialite\EfaasProvider;
use Javaabu\EfaasSocialite\EfaasUser;
use Javaabu\EfaasSocialite\Exceptions\JwtTokenInvalidException;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Javaabu\EfaasSocialite\Tests\TestSupport\Controllers\LoginController;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class EfaasProviderTest extends TestCase
{
    /** @test */
    public function it_can_redirect_to_efaas_login_page()
    {
        $this->withoutExceptionHandling();

        Route::get('/oauth/{socialite_provider}', [LoginController::class, 'redirectToProvider'])
            ->middleware('web')
            ->where('socialite_provider', 'efaas');

        $response = $this->get('/oauth/efaas')
                         ->assertRedirect()
                         ->assertSessionHas('state');

        $redirect_url = $response->headers->get('Location');

        $state = session('state');

        $this->assertStringStartsWith(
                'https://efaas.gov.mv/connect/authorize?'.
                'client_id='.self::CLIENT_ID.
                '&redirect_uri='. urlencode(self::REDIRECT_URL) .
                '&response_type=' . urlencode('code id_token') .
                '&response_mode=form_post'.
                '&scope=' . urlencode('openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status') .
                '&state=' . $state .
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
            ->assertRedirect()
            ->assertSessionHas('state');

        $redirect_url = $response->headers->get('Location');

        $state = session('state');

        $this->assertStringStartsWith(
            'https://efaas.gov.mv/connect/authorize?'.
            'client_id='.self::CLIENT_ID.
            '&redirect_uri='. urlencode(self::REDIRECT_URL) .
            '&response_type=' . urlencode('code id_token') .
            '&response_mode=form_post'.
            '&scope=' . urlencode('openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status') .
            '&acr_values=' . urlencode('efaas_login_code:' . $login_code) .
            '&state=' . $state .
            '&nonce=',
            $redirect_url
        );
    }

    /** @test */
    public function it_can_map_efaas_user()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockValidStatelessProvider();

        $provider->shouldReceive('getUserByToken')
            ->with(self::ACCESS_TOKEN)
            ->andReturn(json_decode('{
                            "middle_name": "Test User",
                            "gender": "M",
                            "idnumber": "A900318",
                            "email": "csc318@gmail.com",
                            "birthdate": "10/22/1993",
                            "passport_number": "LA19E7432",
                            "is_workpermit_active": "False",
                            "updated_at": "1/2/1995 12:00:00 AM",
                            "country_dialing_code": "+960",
                            "country_code": "462",
                            "country_code_alpha3": "MDV",
                            "verified": "False",
                            "verification_type": "NA",
                            "first_name": "CSC",
                            "last_name": "18",
                            "full_name": "CSC Test User 18",
                            "first_name_dhivehi": "ސީއެސްސީ",
                            "middle_name_dhivehi": "ޓެސްޓް ޔޫސަރ",
                            "last_name_dhivehi": "18",
                            "full_name_dhivehi": "ސީއެސްސީ ޓެސްޓް ޔޫސަރ 18",
                            "permanent_address": "{\"AddressLine1\":\"asd\",\"AddressLine2\":\"\",\"Road\":\"\",\"AtollAbbreviation\":\"K\",\"AtollAbbreviationDhivehi\":\"ކ\",\"IslandName\":\"Male\'\",\"IslandNameDhivehi\":\"މާލެ\",\"HomeNameDhivehi\":\"\",\"Ward\":\"Dhaftharu\",\"WardAbbreviationEnglish\":\"Dhaftharu\",\"WardAbbreviationDhivehi\":\"\",\"Country\":\"Maldives\",\"CountryISOThreeDigitCode\":\"462\",\"CountryISOThreeLetterCode\":\"MDV\"}",
                            "user_type_description": "Maldivian",
                            "mobile": "7730018",
                            "photo": "https://efaas-api.developer.gov.mv/user/photo",
                            "country_name": "Maldives",
                            "last_verified_date": "",
                            "sub": "3b46dc4b-f565-420b-af8f-9312c86e40cb"
                  }', true));

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])->where('socialite_provider', 'efaas');

        $this->post('/oauth/efaas/callback', [
            'code' => '21FD3643DC0ECB8684E8266702B033E027BCFC9DD03908F4BB6FC9C751DE81DE',
            'id_token' => self::ID_TOKEN,
            'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
            'session_state' => 'JAfehG74z0MLdngrDpHLpUDA1FJhEKOvDwU0yp_Np7w.4C84F61DD069280A518176B67796F4FA'
        ])
            ->assertRedirect()
            ->assertSessionHas('efaas_user');

        /** @var EfaasUser|\PHPUnit\Framework\mixed $efaas_user */
        $efaas_user = session('efaas_user');

        $this->assertInstanceOf(EfaasUser::class, $efaas_user);

        // Socialite Methods
        $this->assertEquals($efaas_user->name, 'CSC Test User 18');
        $this->assertEquals($efaas_user->getName(), 'CSC Test User 18');
        $this->assertEquals($efaas_user->getEmail(), 'csc318@gmail.com');

        $this->assertEquals($efaas_user->middle_name, 'Test User');
        $this->assertEquals($efaas_user->gender, 'M');
        $this->assertEquals($efaas_user->idnumber, 'A900318');
        $this->assertEquals($efaas_user->email, 'csc318@gmail.com');
        $this->assertEquals($efaas_user->birthdate->toDateString(), '1993-10-22');
        $this->assertEquals($efaas_user->passport_number, 'LA19E7432');
        $this->assertFalse($efaas_user->is_workpermit_active);
        $this->assertEquals($efaas_user->updated_at->toDateTimeString(), '1995-01-02 00:00:00');
        $this->assertEquals($efaas_user->country_dialing_code, '+960');
        $this->assertEquals($efaas_user->country_code, 462);
        $this->assertEquals($efaas_user->country_code_alpha3, 'MDV');
        $this->assertFalse($efaas_user->verified);
        $this->assertEquals($efaas_user->verification_type, 'NA');
        $this->assertEquals($efaas_user->first_name, 'CSC');
        $this->assertEquals($efaas_user->last_name, '18');
        $this->assertEquals($efaas_user->full_name, 'CSC Test User 18');
        $this->assertEquals($efaas_user->first_name_dhivehi, 'ސީއެސްސީ');
        $this->assertEquals($efaas_user->middle_name_dhivehi, 'ޓެސްޓް ޔޫސަރ');
        $this->assertEquals($efaas_user->last_name_dhivehi, '18');
        $this->assertEquals($efaas_user->full_name_dhivehi, 'ސީއެސްސީ ޓެސްޓް ޔޫސަރ 18');
        $this->assertEquals($efaas_user->user_type_description, 'Maldivian');
        $this->assertEquals($efaas_user->mobile, '7730018');
        $this->assertEquals($efaas_user->photo, 'https://efaas-api.developer.gov.mv/user/photo');
        $this->assertEquals($efaas_user->country_name, 'Maldives');
        $this->assertNull($efaas_user->last_verified_date);
        $this->assertEquals($efaas_user->sub, '3b46dc4b-f565-420b-af8f-9312c86e40cb');

        // Address
        $this->assertInstanceOf(EfaasAddress::class, $efaas_user->permanent_address);
    }

    /** @test */
    public function it_can_map_efaas_address()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockValidStatelessProvider();

        $provider->shouldReceive('getUserByToken')
            ->andReturn(json_decode('{
                            "permanent_address": "{\"AddressLine1\":\"asd\",\"AddressLine2\":\"Something\",\"Road\":\"Magu\",\"AtollAbbreviation\":\"K\",\"AtollAbbreviationDhivehi\":\"ކ\",\"IslandName\":\"Male\'\",\"IslandNameDhivehi\":\"މާލެ\",\"HomeNameDhivehi\":\"އޭއެސްޑީ\",\"Ward\":\"Dhaftharu\",\"WardAbbreviationEnglish\":\"Dhaftharu\",\"WardAbbreviationDhivehi\":\"ދަފްތަރު\",\"Country\":\"Maldives\",\"CountryISOThreeDigitCode\":\"462\",\"CountryISOThreeLetterCode\":\"MDV\"}"
                  }', true));

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])->where('socialite_provider', 'efaas');

        $this->post('/oauth/efaas/callback', [
            'code' => 'xxxxxx',
            'id_token' => 'xxxxxx',
            'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
            'session_state' => 'xxxxxx'
        ])
            ->assertRedirect()
            ->assertSessionHas('efaas_user');

        /** @var EfaasUser $efaas_user */
        $efaas_user = session('efaas_user');
        $permanent_address = $efaas_user->permanent_address;

        // Address
        $this->assertInstanceOf(EfaasAddress::class, $permanent_address);
        $this->assertEquals($permanent_address->AddressLine1, 'asd');
        $this->assertEquals($permanent_address->AddressLine2, 'Something');
        $this->assertEquals($permanent_address->Road, 'Magu');
        $this->assertEquals($permanent_address->AtollAbbreviation, 'K');
        $this->assertEquals($permanent_address->AtollAbbreviationDhivehi, 'ކ');
        $this->assertEquals($permanent_address->IslandName, 'Male\'');
        $this->assertEquals($permanent_address->IslandNameDhivehi, 'މާލެ');
        $this->assertEquals($permanent_address->HomeNameDhivehi, 'އޭއެސްޑީ');
        $this->assertEquals($permanent_address->Ward, 'Dhaftharu');
        $this->assertEquals($permanent_address->WardAbbreviationEnglish, 'Dhaftharu');
        $this->assertEquals($permanent_address->WardAbbreviationDhivehi, 'ދަފްތަރު');
        $this->assertEquals($permanent_address->Country, 'Maldives');
        $this->assertEquals($permanent_address->CountryISOThreeDigitCode, 462);
        $this->assertEquals($permanent_address->CountryISOThreeLetterCode, 'MDV');

        $this->assertEquals($permanent_address->getFormattedAddress(), 'Dhaftharu. asd, Something');
        $this->assertEquals($permanent_address->getDhivehiFormattedAddress(), 'ދަފްތަރު. އޭއެސްޑީ');
    }

    /** @test */
    public function it_fails_if_state_is_not_provided()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);

        $provider->shouldReceive('getUserByToken')
                ->andReturn([]);

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])
            ->middleware('web')
            ->where('socialite_provider', 'efaas');

        session()->put('state', 'test_state');

        try {
            $this->post('/oauth/efaas/callback', [
                'code' => 'xxxxxx',
                'id_token' => 'xxxxxx',
                'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
                'session_state' => 'xxxxxx'
            ]);
        } catch (InvalidStateException $e) {
            $this->assertInstanceOf(InvalidStateException::class, $e);
            return;
        }

        $this->fail(sprintf('The expected "%s" exception was not thrown.', InvalidStateException::class));
    }

    /** @test */
    public function it_fails_if_state_does_not_match()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);

        $provider->shouldReceive('getUserByToken')
            ->andReturn([]);

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])
            ->middleware('web')
            ->where('socialite_provider', 'efaas');

        session()->put('state', 'test_state');

        try {
            $this->post('/oauth/efaas/callback', [
                'code' => 'xxxxxx',
                'id_token' => 'xxxxxx',
                'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
                'session_state' => 'xxxxxx',
                'state' => 'something_else'
            ]);
        } catch (InvalidStateException $e) {
            $this->assertInstanceOf(InvalidStateException::class, $e);
            return;
        }

        $this->fail(sprintf('The expected "%s" exception was not thrown.', InvalidStateException::class));
    }

    /** @test */
    public function it_can_validate_the_state()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockValidProvider();

        $provider->shouldReceive('getUserByToken')
            ->andReturn([]);

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])
            ->middleware('web')
            ->where('socialite_provider', 'efaas');

        session()->put('state', 'test_state');

        $this->post('/oauth/efaas/callback', [
            'code' => 'xxxxxx',
            'id_token' => 'xxxxxx',
            'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
            'session_state' => 'xxxxxx',
            'state' => 'test_state'
        ])
            ->assertRedirect()
            ->assertSessionHas('efaas_user');
    }

    /** @test */
    public function it_can_get_the_public_key_for_the_given_kid()
    {
        $this->withoutExceptionHandling();

        $kid = '5CDA5CF378397733DD33EFBDA82D0F317DCC1D53RS256';

        /** @var EfaasProvider $provider */
        $provider = $this->mockStatelessProvider();
        $this->mockProviderGetJwksResponse($provider);

        $expected_public_key =
            "-----BEGIN PUBLIC KEY-----\n".
            "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxNcqX+FTpecM5p00QUgS\n".
            "0zx8eX/TujLs3sX83qujZKabP/JnAG2PVKwgNJyHaqukJcA6FDIlCMTxy0riCLUC\n".
            "5e6qtJA7Gn9hXEOJKjzq+TYvKnVqeamu7JeczW3ZiUXgGoI0p6pafxdkHz4CZuTh\n".
            "KEjtpeVRXyqAfuzN7liNdPgjWsj50YQvyFjQlQgiMZIXIGESEu2nYK5VST/T891r\n".
            "PtfYKnRUhOu7HYggeXk5qYsoEVSFpBdZWzpCaprFGUx9fg89xeQpd0jzhpw8+gg3\n".
            "UsnpuuY8+KXUVzGPVLb4lKymQC8o8a1VyzA/GyqHovk7K3zWoyK1WD1aNwFFHLM8\n".
            "DQIDAQAB\n".
            "-----END PUBLIC KEY-----";

        $normalized_key =  preg_replace('/\R+/', '', $expected_public_key);

        $this->assertEquals($normalized_key, preg_replace('/\R+/', '', $provider->getPublicKey($kid)));

        // call twice to make sure key is cached
        $this->assertEquals($normalized_key, preg_replace('/\R+/', '', $provider->getPublicKey($kid)));
    }

    /** @test */
    public function it_can_get_the_sid_from_the_id_token()
    {
        $this->withoutExceptionHandling();

        $sid = self::SID;

        $provider = $this->mockStatelessProvider();

        $this->mockProviderGetAccessToken($provider);
        $this->mockProviderGetJwksResponse($provider);
        $this->mockProviderSystemTime($provider,  '2024-06-25T18:19:00+0500');

        $provider->shouldReceive('getUserByToken')
            ->with(self::ACCESS_TOKEN)
            ->andReturn([]);

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])->where('socialite_provider', 'efaas');

        $this->post('/oauth/efaas/callback', [
            'code' => '21FD3643DC0ECB8684E8266702B033E027BCFC9DD03908F4BB6FC9C751DE81DE',
            'id_token' => self::ID_TOKEN,
            'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
            'session_state' => 'JAfehG74z0MLdngrDpHLpUDA1FJhEKOvDwU0yp_Np7w.4C84F61DD069280A518176B67796F4FA'
        ])
            ->assertRedirect()
            ->assertSessionHas('efaas_user')
            ->assertSessionHas('efaas_sid', $sid);

        /** @var EfaasUser|\PHPUnit\Framework\mixed $efaas_user */
        $efaas_user = session('efaas_user');

        $this->assertEquals($efaas_user->sid, $sid);
    }

    /** @test */
    public function it_fails_on_an_expired_id_token()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockStatelessProvider();

        $this->mockProviderGetAccessToken($provider);
        $this->mockProviderGetJwksResponse($provider);
        $this->mockProviderSystemTime($provider,  '2024-06-28T18:19:00+0500');

        $provider->shouldReceive('getUserByToken')
            ->with(self::ACCESS_TOKEN)
            ->andReturn([]);

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/callback', [LoginController::class, 'handleProviderCallback'])->where('socialite_provider', 'efaas');

        try {
            $this->post('/oauth/efaas/callback', [
                'code' => '21FD3643DC0ECB8684E8266702B033E027BCFC9DD03908F4BB6FC9C751DE81DE',
                'id_token' => self::ID_TOKEN,
                'scope' => 'openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status',
                'session_state' => 'JAfehG74z0MLdngrDpHLpUDA1FJhEKOvDwU0yp_Np7w.4C84F61DD069280A518176B67796F4FA'
            ]);
        } catch (JwtTokenInvalidException $e) {
            $this->assertInstanceOf(JwtTokenInvalidException::class, $e);
            return;
        }

        $this->fail(sprintf('The expected "%s" exception was not thrown.', JwtTokenInvalidException::class));
    }

    /** @test */
    public function it_can_retrieve_the_logout_token_for_back_channel_logout()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockValidProvider();

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/logout', [LoginController::class, 'handleSingleLogout'])->where('socialite_provider', 'efaas');

        $this->post('/oauth/efaas/logout', [
            'logout_token' => self::ID_TOKEN,
        ])
            ->assertRedirect()
            ->assertSessionHas('logout_token', self::ID_TOKEN)
            ->assertSessionHas('logout_sid', self::SID);
    }

    /** @test */
    public function it_can_retrieve_the_logout_token_for_front_channel_logout()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockValidProvider();

        $this->setMockProvider($provider);

        Route::get('/oauth/{socialite_provider}/logout', [LoginController::class, 'handleSingleLogout'])->where('socialite_provider', 'efaas');

        $this->get('/oauth/efaas/logout?logout_token=' . urlencode(self::ID_TOKEN))
            ->assertRedirect()
            ->assertSessionHas('logout_token', self::ID_TOKEN)
            ->assertSessionHas('logout_sid', self::SID);
    }

    /** @test */
    public function it_gives_null_sid_on_expired_logout_token()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);
        $this->mockProviderGetJwksResponse($provider);
        $this->mockProviderSystemTime($provider,  '2024-06-25T18:19:00+0500');

        $this->setMockProvider($provider);

        Route::post('/oauth/{socialite_provider}/logout', [LoginController::class, 'handleSingleLogout'])->where('socialite_provider', 'efaas');

        $this->post('/oauth/efaas/logout', [
            'logout_token' => self::ID_TOKEN,
        ])
            ->assertRedirect()
            ->assertSessionHas('logout_token', self::ID_TOKEN)
            ->assertSessionHas('logout_sid', null);
    }

    /** @test */
    public function it_can_retreive_photo_as_a_base64_encoded_string()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();

        $this->assertEquals($this->getTestPhotoJson()['data']['photo'], $efaas_user->getPhotoBase64());
    }

    /** @test */
    public function it_can_get_the_photo_mime_type()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();

        $this->assertEquals('image/png', $efaas_user->getPhotoMimetype());
    }

    /** @test */
    public function it_can_get_the_photo_extension()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();

        $this->assertEquals('png', $efaas_user->getPhotoExtension());
    }

    /** @test */
    public function it_can_get_the_photo_as_a_data_url()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();

        $this->assertStringStartsWith('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAVYAAAGGCAIAAACv8z77AAABBGlDQ1BJQ0MgUHJvZmlsZQAAKM9jYGCSYAACFgMGhty8kqIgdyeFiMgoBQYkkJhcXMCAGzAyMHy7BiIZGC7rMpAOOFNSi5OB9AcgLikCWg40MgXIFkmHsCtA7', $efaas_user->getPhotoDataUrl());
        $this->assertStringStartsWith('data:image/png;base64,iVBORw0KGgoAAAANS', $efaas_user->getAvatar());
        //$this->assertEquals($this->getTestPhotoDataUrl(), $efaas_user->getPhotoDataUrl());
    }
}
