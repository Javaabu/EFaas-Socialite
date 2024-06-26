<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Javaabu\EfaasSocialite\EfaasAddress;
use Javaabu\EfaasSocialite\EfaasUser;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Javaabu\EfaasSocialite\Tests\TestSupport\Controllers\LoginController;

class EfaasProviderTest extends TestCase
{
    /** @test */
    public function it_can_redirect_to_efaas_login_page()
    {
        $this->withoutExceptionHandling();

        Route::get('/oauth/{socialite_provider}', [LoginController::class, 'redirectToProvider'])->where('socialite_provider', 'efaas');

        $response = $this->get('/oauth/efaas')
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

    /** @test */
    public function it_can_map_efaas_user()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);

        $provider->shouldReceive('getUserByToken')
            ->with(self::ACCESS_TOKEN)
            ->andReturn(json_decode('{
                            "middle_name": "Test User",
                            "gender": "M",
                            "idnumber": "A900318",
                            "email": "csc318@gmail.com",
                            "birthdate": "6/3/1990",
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
            'id_token' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjVDREE1Q0YzNzgzOTc3MzNERDMzRUZCREE4MkQwRjMxN0RDQzFENTNSUzI1NiIsInR5cCI6IkpXVCIsIng1dCI6IlhOcGM4M2c1ZHpQZE0tLTlxQzBQTVgzTUhWTSJ9.eyJuYmYiOjE3MTkzNzg5NDYsImV4cCI6MTcxOTM3OTI0NiwiaXNzIjoiaHR0cHM6Ly9kZXZlbG9wZXIuZ292Lm12L2VmYWFzIiwiYXVkIjoiNDllM2NlNmEtZmYyMS00OGU5LWE2NmEtMDM3NzRkMmRhNTM4Iiwibm9uY2UiOiJSM3p0a0h5SGdhZktoMWs0NDV3UEt6elRxWEJ4STNiVFhaU0t2MUhYIiwiaWF0IjoxNzE5Mzc4OTQ2LCJjX2hhc2giOiJucnRSa2tyaGJKRVl0Mm9kN3pSa1pBIiwic2lkIjoiOUY2NjJDN0Q2QTNBMUE5ODBCMjYxRUYyMjVCMzAwQzgiLCJzdWIiOiIzYjQ2ZGM0Yi1mNTY1LTQyMGItYWY4Zi05MzEyYzg2ZTQwY2IiLCJhdXRoX3RpbWUiOjE3MTkzMDE5MDYsImlkcCI6ImxvY2FsIiwibWlkZGxlX25hbWUiOiJUZXN0IFVzZXIiLCJnZW5kZXIiOiJNIiwiaWRudW1iZXIiOiJBOTAwMzE4IiwiZW1haWwiOiJjc2MzMThAZ21haWwuY29tIiwiYmlydGhkYXRlIjoiNi8zLzE5OTAiLCJwYXNzcG9ydF9udW1iZXIiOiIiLCJpc193b3JrcGVybWl0X2FjdGl2ZSI6IkZhbHNlIiwidXBkYXRlZF9hdCI6IjEvMS8xOTk1IDEyOjAwOjAwIEFNIiwiY291bnRyeV9kaWFsaW5nX2NvZGUiOiIrOTYwIiwiY291bnRyeV9jb2RlIjoiNDYyIiwiY291bnRyeV9jb2RlX2FscGhhMyI6Ik1EViIsInZlcmlmaWVkIjoiRmFsc2UiLCJ2ZXJpZmljYXRpb25fdHlwZSI6Ik5BIiwiZmlyc3RfbmFtZSI6IkNTQyIsImxhc3RfbmFtZSI6IjE4IiwiZnVsbF9uYW1lIjoiQ1NDIFRlc3QgVXNlciAxOCIsImZpcnN0X25hbWVfZGhpdmVoaSI6IiIsIm1pZGRsZV9uYW1lX2RoaXZlaGkiOiIiLCJsYXN0X25hbWVfZGhpdmVoaSI6IiIsImZ1bGxfbmFtZV9kaGl2ZWhpIjoiIiwicGVybWFuZW50X2FkZHJlc3MiOiJ7XCJBZGRyZXNzTGluZTFcIjpcImFzZFwiLFwiQWRkcmVzc0xpbmUyXCI6XCJcIixcIlJvYWRcIjpcIlwiLFwiQXRvbGxBYmJyZXZpYXRpb25cIjpcIktcIixcIkF0b2xsQWJicmV2aWF0aW9uRGhpdmVoaVwiOlwi3oZcIixcIklzbGFuZE5hbWVcIjpcIk1hbGUnXCIsXCJJc2xhbmROYW1lRGhpdmVoaVwiOlwi3onep96N3qxcIixcIkhvbWVOYW1lRGhpdmVoaVwiOlwiXCIsXCJXYXJkXCI6XCJEaGFmdGhhcnVcIixcIldhcmRBYmJyZXZpYXRpb25FbmdsaXNoXCI6XCJEaGFmdGhhcnVcIixcIldhcmRBYmJyZXZpYXRpb25EaGl2ZWhpXCI6XCJcIixcIkNvdW50cnlcIjpcIk1hbGRpdmVzXCIsXCJDb3VudHJ5SVNPVGhyZWVEaWdpdENvZGVcIjpcIjQ2MlwiLFwiQ291bnRyeUlTT1RocmVlTGV0dGVyQ29kZVwiOlwiTURWXCJ9IiwidXNlcl90eXBlX2Rlc2NyaXB0aW9uIjoiTWFsZGl2aWFuIiwibW9iaWxlIjoiNzczMDAxOCIsInBob3RvIjoiaHR0cHM6Ly9lZmFhcy1hcGkuZGV2ZWxvcGVyLmdvdi5tdi91c2VyL3Bob3RvIiwiY291bnRyeV9uYW1lIjoiTWFsZGl2ZXMiLCJsYXN0X3ZlcmlmaWVkX2RhdGUiOiIiLCJhbXIiOlsicHdkIl19.jYKQc7H6K52IRPm2csGSfINCfgJoz47PBZ1fg9EkHYBgvzenjcL9X7AVHfpbYFpQH3SDM268QYdkq7gWqM_RX8pbZPB3yGmiAzPYL3d4YMgyw2p1UMKbE_F96uDKJBwA5IHdA0WF1jzOptWplgOmGKIlPMizvkH7zIgr6th8WgonqZK23JY2Xh5rzoNHWabne-zDEiQ5qjQn_W5WMkpY7iv7lKpl1-YRXOFMWQLPoGeucEtpWz52ufwwwzTZ7NFPm9hmGxizo1F9LMwFfQ84ghrIGarBKus3qxYa8fM-HrGIyPBl3h1U3HQX31UYy2pit9huPWp_e8MiMVf1imo3RA',
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
        $this->assertEquals($efaas_user->birthdate->toDateString(), '1990-03-06');
        $this->assertEquals($efaas_user->passport_number, 'LA19E7432');
        $this->assertFalse($efaas_user->is_workpermit_active);
        $this->assertEquals($efaas_user->updated_at->toDateTimeString(), '1995-02-01 00:00:00');
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

        $provider = $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);

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
}
