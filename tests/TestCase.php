<?php

namespace Javaabu\EfaasSocialite\Tests;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Javaabu\EfaasSocialite\EfaasProvider;
use Javaabu\EfaasSocialite\EfaasUser;
use Javaabu\EfaasSocialite\EfaasUser as User;
use Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider;
use Javaabu\EfaasSocialite\Tests\TestSupport\Providers\TestServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Lcobucci\Clock\FrozenClock;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    const CLIENT_ID = 'abc44ec3-aa7b-4eab-a50e-4d18f17c3f62';
    const CLIENT_SECRET = '9fz11cd8-7bb8-40fa-b3eb-bc5dc43439c3';
    const REDIRECT_URL = 'http://localhost/oauth/efaas/callback';
    const ACCESS_TOKEN = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjVDREE1Q0YzNzgzOTc3MzNERDMzRUZCREE4MkQwRjMxN0RDQzFENTNSUzI1NiIsInR5cCI6ImF0K2p3dCIsIng1dCI6IlhOcGM4M2c1ZHpQZE0tLTlxQzBQTVgzTUhWTSJ9.eyJuYmYiOjE3MTk4NzA4NDQsImV4cCI6MTcxOTg3NDQ0NCwiaXNzIjoiaHR0cHM6Ly9kZXZlbG9wZXIuZ292Lm12L2VmYWFzIiwiY2xpZW50X2lkIjoiNDllM2NlNmEtZmYyMS00OGU5LWE2NmEtMDM3NzRkMmRhNTM4Iiwic3ViIjoiOTY0NmE3ZWQtMWM3Ny00ZTA4LTliM2MtOGFiNWE4YWVkOWQ1IiwiYXV0aF90aW1lIjoxNzE5ODcwNzcwLCJpZHAiOiJsb2NhbCIsImp0aSI6IjY1QzI4NzJFMzRCNDk4NTA0Nzk3MTBBN0RCRDlFMzBDIiwic2lkIjoiNkJBN0MyNDg5NEZEMzg0RUJCOEVGNDM4RDU2NkNBQjMiLCJpYXQiOjE3MTk4NzA4NDQsInNjb3BlIjpbIm9wZW5pZCIsImVmYWFzLnByb2ZpbGUiLCJlZmFhcy5iaXJ0aGRhdGUiLCJlZmFhcy5lbWFpbCIsImVmYWFzLm1vYmlsZSIsImVmYWFzLnBob3RvIiwiZWZhYXMucGVybWFuZW50X2FkZHJlc3MiLCJlZmFhcy5jb3VudHJ5IiwiZWZhYXMucGFzc3BvcnRfbnVtYmVyIiwiZWZhYXMud29ya19wZXJtaXRfc3RhdHVzIl0sImFtciI6WyJwd2QiXX0.CAeNxrrBS4EAZu6Gd7JBNA-O6cDOve1eCVmVCnXx0kDTaZymJ0PMb3doCbDhP7_zMANGkHhOLLfs8UvWKBPahN35e-nUQg5kjcB4_uBEQgtgOJe4cQ6An5ss7pV2isXWvXfzkks5-Fp7xM5Ds89SyWpKglhShDVKxtdbz4Idaxg5vN50LItrwkfsGbR1Ta69pYGFARc9gHVi97gGZQ-APBC9uVNYhiGZWp8jl4xKHG2qd7sS_P-oXEHod_9-WJlhDbQeqwQ78AlhOjBDWJH9zrzk_at6ZVp0tRNhyXdnA2hwE7Ctvhg-GZ6QRkBVuqMLO77tuNLldEpJOTA7CqUCAQ';
    const ID_TOKEN = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjVDREE1Q0YzNzgzOTc3MzNERDMzRUZCREE4MkQwRjMxN0RDQzFENTNSUzI1NiIsInR5cCI6IkpXVCIsIng1dCI6IlhOcGM4M2c1ZHpQZE0tLTlxQzBQTVgzTUhWTSJ9.eyJuYmYiOjE3MTkzMjE1MzEsImV4cCI6MTcxOTMyMTgzMSwiaXNzIjoiaHR0cHM6Ly9kZXZlbG9wZXIuZ292Lm12L2VmYWFzIiwiYXVkIjoiNDllM2NlNmEtZmYyMS00OGU5LWE2NmEtMDM3NzRkMmRhNTM4Iiwibm9uY2UiOiJzZGFzZGFzZGFzZCIsImlhdCI6MTcxOTMyMTUzMSwiYXRfaGFzaCI6IlZoOWZ3cExJRnVqbmU3SFlkcWwwaXciLCJzaWQiOiI5RjY2MkM3RDZBM0ExQTk4MEIyNjFFRjIyNUIzMDBDOCIsInN1YiI6IjNiNDZkYzRiLWY1NjUtNDIwYi1hZjhmLTkzMTJjODZlNDBjYiIsImF1dGhfdGltZSI6MTcxOTMwMTkwNiwiaWRwIjoibG9jYWwiLCJtaWRkbGVfbmFtZSI6IlRlc3QgVXNlciIsImdlbmRlciI6Ik0iLCJpZG51bWJlciI6IkE5MDAzMTgiLCJlbWFpbCI6ImNzYzMxOEBnbWFpbC5jb20iLCJiaXJ0aGRhdGUiOiI2LzMvMTk5MCIsInBhc3Nwb3J0X251bWJlciI6IiIsImlzX3dvcmtwZXJtaXRfYWN0aXZlIjoiRmFsc2UiLCJ1cGRhdGVkX2F0IjoiMS8xLzE5OTUgMTI6MDA6MDAgQU0iLCJjb3VudHJ5X2RpYWxpbmdfY29kZSI6Iis5NjAiLCJjb3VudHJ5X2NvZGUiOiI0NjIiLCJjb3VudHJ5X2NvZGVfYWxwaGEzIjoiTURWIiwidmVyaWZpZWQiOiJGYWxzZSIsInZlcmlmaWNhdGlvbl90eXBlIjoiTkEiLCJmaXJzdF9uYW1lIjoiQ1NDIiwibGFzdF9uYW1lIjoiMTgiLCJmdWxsX25hbWUiOiJDU0MgVGVzdCBVc2VyIDE4IiwiZmlyc3RfbmFtZV9kaGl2ZWhpIjoiIiwibWlkZGxlX25hbWVfZGhpdmVoaSI6IiIsImxhc3RfbmFtZV9kaGl2ZWhpIjoiIiwiZnVsbF9uYW1lX2RoaXZlaGkiOiIiLCJwZXJtYW5lbnRfYWRkcmVzcyI6IntcIkFkZHJlc3NMaW5lMVwiOlwiYXNkXCIsXCJBZGRyZXNzTGluZTJcIjpcIlwiLFwiUm9hZFwiOlwiXCIsXCJBdG9sbEFiYnJldmlhdGlvblwiOlwiS1wiLFwiQXRvbGxBYmJyZXZpYXRpb25EaGl2ZWhpXCI6XCLehlwiLFwiSXNsYW5kTmFtZVwiOlwiTWFsZSdcIixcIklzbGFuZE5hbWVEaGl2ZWhpXCI6XCLeid6n3o3erFwiLFwiSG9tZU5hbWVEaGl2ZWhpXCI6XCJcIixcIldhcmRcIjpcIkRoYWZ0aGFydVwiLFwiV2FyZEFiYnJldmlhdGlvbkVuZ2xpc2hcIjpcIkRoYWZ0aGFydVwiLFwiV2FyZEFiYnJldmlhdGlvbkRoaXZlaGlcIjpcIlwiLFwiQ291bnRyeVwiOlwiTWFsZGl2ZXNcIixcIkNvdW50cnlJU09UaHJlZURpZ2l0Q29kZVwiOlwiNDYyXCIsXCJDb3VudHJ5SVNPVGhyZWVMZXR0ZXJDb2RlXCI6XCJNRFZcIn0iLCJ1c2VyX3R5cGVfZGVzY3JpcHRpb24iOiJNYWxkaXZpYW4iLCJtb2JpbGUiOiI3NzMwMDE4IiwicGhvdG8iOiJodHRwczovL2VmYWFzLWFwaS5kZXZlbG9wZXIuZ292Lm12L3VzZXIvcGhvdG8iLCJjb3VudHJ5X25hbWUiOiJNYWxkaXZlcyIsImxhc3RfdmVyaWZpZWRfZGF0ZSI6IiIsImFtciI6WyJwd2QiXX0.bD4WhgwKYTx1--Z3-OGGGgN-nDto-g2UH_QHmQmxLDtLoWfIlatVb9jU7kiArseUvnzXXZ-mNNC9ACSoWZwb1l3uuKs50DkR6iybGx8NGH9kA_TeM6enirbRXO5s4njCDgCrhNV6c_j8hH_OHef0Tiu43wpWVF79ayxv1SRv54tZtb9NE7tumFTEcI_bwVMDxe499ZCgilNGcaB6xNOJY4_Iw166R8eZ_Q37ccVlQRSPaKtsf-LRdVjDTA8T7D4Gbl2etOpqC0NcN1eF2y5fHWGsggRawVVTR8b3LtAYm4bJxuG9j5Cj-EPDBfBAo0LMWCsqVzC0O2p8tj6C8vuOgg';
    const SID = '9F662C7D6A3A1A980B261EF225B300C8';


    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        // setup efaas config
        $this->app['config']->set('efaas.client.client_id', self::CLIENT_ID);
        $this->app['config']->set('efaas.client.client_secret', self::CLIENT_SECRET);
        $this->app['config']->set('efaas.client.redirect', self::REDIRECT_URL);
        $this->app['config']->set('efaas.client.mode', 'production');

    }

    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            EfaasSocialiteServiceProvider::class,
            TestServiceProvider::class
        ];
    }

    protected function mockStatelessProvider(): MockInterface
    {
        $provider = $this->mockProvider();

        $provider->shouldReceive('usesState')
                 ->andReturnFalse();

        $provider->shouldReceive('isStateless')
                ->andReturnTrue();

        return $provider;
    }

    protected function mockProvider(): MockInterface
    {
        return Mockery::mock(EfaasProvider::class, [
            request(),
            config('efaas.client.client_id'),
            config('efaas.client.client_secret'),
            config('efaas.client.redirect'),
        ])->makePartial()
          ->shouldAllowMockingProtectedMethods();
    }

    protected function mockValidStatelessProvider(): MockInterface
    {
        return $this->mockValidProvider(true);
    }

    protected function mockValidProvider(bool $stateless = false): MockInterface
    {
        $provider = $stateless ? $this->mockStatelessProvider() : $this->mockProvider();

        $this->mockProviderGetAccessToken($provider);
        $this->mockProviderGetJwksResponse($provider);
        $this->mockProviderSystemTime($provider,  '2024-06-25T18:19:00+0500');

        return $provider;
    }

    protected function mockProviderSystemTime(MockInterface $provider, $time): MockInterface
    {
        if (! $time instanceof \DateTimeImmutable) {
            $time = Carbon::parse($time)->toDateTimeImmutable();
        }

        $provider->shouldReceive('getCurrentSystemTime')
            ->andReturn(new FrozenClock($time));

        return $provider;
    }

    protected function mockProviderGetJwksResponse(MockInterface $provider): MockInterface
    {
        $provider->shouldReceive('getJwksResponse')
            ->with(true)
            ->once()
            ->andReturn('{"keys":[{"kty":"RSA","use":"sig","kid":"5CDA5CF378397733DD33EFBDA82D0F317DCC1D53RS256","x5t":"XNpc83g5dzPdM--9qC0PMX3MHVM","e":"AQAB","n":"xNcqX-FTpecM5p00QUgS0zx8eX_TujLs3sX83qujZKabP_JnAG2PVKwgNJyHaqukJcA6FDIlCMTxy0riCLUC5e6qtJA7Gn9hXEOJKjzq-TYvKnVqeamu7JeczW3ZiUXgGoI0p6pafxdkHz4CZuThKEjtpeVRXyqAfuzN7liNdPgjWsj50YQvyFjQlQgiMZIXIGESEu2nYK5VST_T891rPtfYKnRUhOu7HYggeXk5qYsoEVSFpBdZWzpCaprFGUx9fg89xeQpd0jzhpw8-gg3UsnpuuY8-KXUVzGPVLb4lKymQC8o8a1VyzA_GyqHovk7K3zWoyK1WD1aNwFFHLM8DQ","x5c":["MIIC9TCCAd2gAwIBAgIQv04GLDYrSadBQa/cbrpM4DANBgkqhkiG9w0BAQsFADASMRAwDgYDVQQDEwdFZmFhc1RSMB4XDTE4MDgzMDA4MDA1NloXDTM5MTIzMTIzNTk1OVowEjEQMA4GA1UEAxMHRWZhYXNUUjCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAMTXKl/hU6XnDOadNEFIEtM8fHl/07oy7N7F/N6ro2Smmz/yZwBtj1SsIDSch2qrpCXAOhQyJQjE8ctK4gi1AuXuqrSQOxp/YVxDiSo86vk2Lyp1anmpruyXnM1t2YlF4BqCNKeqWn8XZB8\u002BAmbk4ShI7aXlUV8qgH7sze5YjXT4I1rI\u002BdGEL8hY0JUIIjGSFyBhEhLtp2CuVUk/0/Pdaz7X2Cp0VITrux2IIHl5OamLKBFUhaQXWVs6QmqaxRlMfX4PPcXkKXdI84acPPoIN1LJ6brmPPil1Fcxj1S2\u002BJSspkAvKPGtVcswPxsqh6L5Oyt81qMitVg9WjcBRRyzPA0CAwEAAaNHMEUwQwYDVR0BBDwwOoAQz61XDh2d8BVuoI/PEQ4nBKEUMBIxEDAOBgNVBAMTB0VmYWFzVFKCEL9OBiw2K0mnQUGv3G66TOAwDQYJKoZIhvcNAQELBQADggEBACiqmi\u002B6JCcsNkhYEuBAI2c1A33S3pAhmMENf8RtMv2JTm6U4xLZtPEvVG/IyaSEQGcnOJjut8YOFPuXvjiVMqcA1wzjZkeyxpaO/7tGjf3Eiwt35VP9V3tdleBbxHWiZM7mGeith9M8Ol3sscfiR\u002Bib8BHq9BXSo0P16IgASJMMPOljzkqvtbExpVv1/OhjOMWAJwHtjErTIhhido/sJhe8DyenQQjcAlbxftblF4\u002BjvDAEn\u002B6WRsquOy1Q7n6JKlK\u002BF0xH\u002BZNQo9xrDfpIREyb\u002B4p3UuJgCs6zMhQoMkiR4TkdyKSSlrSYtImfJn92VMqkhaWI/ntiHfx\u002Bsnlb41U="],"alg":"RS256"}]}');

        return $provider;
    }

    protected function mockProviderGetAccessToken(MockInterface $provider): MockInterface
    {
        $provider->shouldReceive('getAccessTokenResponse')
            ->andReturn(json_decode(
                '{
                    "id_token": "' . self::ID_TOKEN . '",
                    "access_token": "' . self::ACCESS_TOKEN . '",
                    "expires_in": 3600,
                    "token_type": "Bearer",
                    "scope": "openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status"
                }',
                true
            ));

        return $provider;
    }

    protected function getTestPhotoJson(): array
    {
        return json_decode(file_get_contents(__DIR__ . '/TestSupport/resources/photo.json'), true);
    }

    protected function getTestPhotoDataUrl(): string
    {
        return file_get_contents(__DIR__ . '/TestSupport/resources/photo-data-url.txt');
    }

    protected function mockUserPhoto(MockInterface $provider): MockInterface
    {
        $url = 'https://efaas-api.developer.gov.mv/user/photo';

        $user = $this->partialMock(EfaasUser::class);

        $user->shouldReceive('getPhotoResponse')
            ->andReturn($this->getTestPhotoJson());

        $user->setRaw(['photo' => $url])->map([
            'photo' => $url,
            'avatar' => $url,
        ]);

        $provider->shouldReceive('user')
                ->andReturn($user);

        return $provider;
    }

    protected function setMockProvider($provider)
    {
        Socialite::shouldReceive('driver')
            ->with('efaas')
            ->andReturn($provider);
    }
}
