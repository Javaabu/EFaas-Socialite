<?php

namespace Javaabu\EfaasSocialite\Tests;

use Illuminate\Session\SessionManager;
use Javaabu\EfaasSocialite\EfaasProvider;
use Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Mockery;
use Mockery\MockInterface;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    const CLIENT_ID = 'abc44ec3-aa7b-4eab-a50e-4d18f17c3f62';
    const CLIENT_SECRET = '9fz11cd8-7bb8-40fa-b3eb-bc5dc43439c3';
    const REDIRECT_URL = 'http://localhost/oauth/efaas/callback';
    const ACCESS_TOKEN = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjVDREE1Q0YzNzgzOTc3MzNERDMzRUZCREE4MkQwRjMxN0RDQzFENTNSUzI1NiIsInR5cCI6ImF0K2p3dCIsIng1dCI6IlhOcGM4M2c1ZHpQZE0tLTlxQzBQTVgzTUhWTSJ9.eyJuYmYiOjE3MTkzMjE1MzEsImV4cCI6MTcxOTMyNTEzMSwiaXNzIjoiaHR0cHM6Ly9kZXZlbG9wZXIuZ292Lm12L2VmYWFzIiwiY2xpZW50X2lkIjoiNDllM2NlNmEtZmYyMS00OGU5LWE2NmEtMDM3NzRkMmRhNTM4Iiwic3ViIjoiM2I0NmRjNGItZjU2NS00MjBiLWFmOGYtOTMxMmM4NmU0MGNiIiwiYXV0aF90aW1lIjoxNzE5MzAxOTA2LCJpZHAiOiJsb2NhbCIsImp0aSI6IjlCQzNBOEE1MUJDNENFRTY3NkYxRENFODg0ODhEQjI1Iiwic2lkIjoiOUY2NjJDN0Q2QTNBMUE5ODBCMjYxRUYyMjVCMzAwQzgiLCJpYXQiOjE3MTkzMjE1MzEsInNjb3BlIjpbIm9wZW5pZCIsImVmYWFzLnByb2ZpbGUiLCJlZmFhcy5iaXJ0aGRhdGUiLCJlZmFhcy5lbWFpbCIsImVmYWFzLm1vYmlsZSIsImVmYWFzLnBob3RvIiwiZWZhYXMucGVybWFuZW50X2FkZHJlc3MiLCJlZmFhcy5jb3VudHJ5IiwiZWZhYXMucGFzc3BvcnRfbnVtYmVyIiwiZWZhYXMud29ya19wZXJtaXRfc3RhdHVzIl0sImFtciI6WyJwd2QiXX0.teSbFFwuQMEnbM6GDX72rXMHsONDJ_lttZ4iWx0SoUSnd61v9TmuSTw8jMWl4rzM7WR9I5tP4vFzDWN9-aR9iSzC0Xi_Sy4l5yAX_dVZsnevdFv-eLjZeTgqBDSgdHxsr6wUS1ihstuEZaURLmdq6iqu1pdK68WC0HvQucQca21oUItJkaIvhhfvCd0ebFVi4lcOQzgctmw_Je59w6HphGrc1qe4E09oSA_qonUxsxfzwJX9GSZPZcuno69nbCt0QZYixgxjMkAHKy89h6DFNO8kjXQb0oiO-tGNqRHwOlrfy9boAxQcmPBshTir23EgqCH9Av0NAbhi0qFtHglFEg';


    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');

        $this->app['config']->set('session.serialization', 'php');

        // setup efaas config
        $this->app['config']->set('services.efaas.client_id', self::CLIENT_ID);
        $this->app['config']->set('services.efaas.client_secret', self::CLIENT_SECRET);
        $this->app['config']->set('services.efaas.redirect', self::REDIRECT_URL);
        $this->app['config']->set('services.efaas.mode', 'production');

    }

    protected function getPackageProviders($app)
    {
        return [
            SocialiteServiceProvider::class,
            EfaasSocialiteServiceProvider::class
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
            config('services.efaas.client_id'),
            config('services.efaas.client_secret'),
            config('services.efaas.redirect'),
        ])->makePartial()
          ->shouldAllowMockingProtectedMethods();
    }

    protected function mockProviderGetAccessToken(MockInterface $provider): MockInterface
    {
        $provider->shouldReceive('getAccessTokenResponse')
            ->andReturn(json_decode(
                '{
                    "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6IjVDREE1Q0YzNzgzOTc3MzNERDMzRUZCREE4MkQwRjMxN0RDQzFENTNSUzI1NiIsInR5cCI6IkpXVCIsIng1dCI6IlhOcGM4M2c1ZHpQZE0tLTlxQzBQTVgzTUhWTSJ9.eyJuYmYiOjE3MTkzMjE1MzEsImV4cCI6MTcxOTMyMTgzMSwiaXNzIjoiaHR0cHM6Ly9kZXZlbG9wZXIuZ292Lm12L2VmYWFzIiwiYXVkIjoiNDllM2NlNmEtZmYyMS00OGU5LWE2NmEtMDM3NzRkMmRhNTM4Iiwibm9uY2UiOiJzZGFzZGFzZGFzZCIsImlhdCI6MTcxOTMyMTUzMSwiYXRfaGFzaCI6IlZoOWZ3cExJRnVqbmU3SFlkcWwwaXciLCJzaWQiOiI5RjY2MkM3RDZBM0ExQTk4MEIyNjFFRjIyNUIzMDBDOCIsInN1YiI6IjNiNDZkYzRiLWY1NjUtNDIwYi1hZjhmLTkzMTJjODZlNDBjYiIsImF1dGhfdGltZSI6MTcxOTMwMTkwNiwiaWRwIjoibG9jYWwiLCJtaWRkbGVfbmFtZSI6IlRlc3QgVXNlciIsImdlbmRlciI6Ik0iLCJpZG51bWJlciI6IkE5MDAzMTgiLCJlbWFpbCI6ImNzYzMxOEBnbWFpbC5jb20iLCJiaXJ0aGRhdGUiOiI2LzMvMTk5MCIsInBhc3Nwb3J0X251bWJlciI6IiIsImlzX3dvcmtwZXJtaXRfYWN0aXZlIjoiRmFsc2UiLCJ1cGRhdGVkX2F0IjoiMS8xLzE5OTUgMTI6MDA6MDAgQU0iLCJjb3VudHJ5X2RpYWxpbmdfY29kZSI6Iis5NjAiLCJjb3VudHJ5X2NvZGUiOiI0NjIiLCJjb3VudHJ5X2NvZGVfYWxwaGEzIjoiTURWIiwidmVyaWZpZWQiOiJGYWxzZSIsInZlcmlmaWNhdGlvbl90eXBlIjoiTkEiLCJmaXJzdF9uYW1lIjoiQ1NDIiwibGFzdF9uYW1lIjoiMTgiLCJmdWxsX25hbWUiOiJDU0MgVGVzdCBVc2VyIDE4IiwiZmlyc3RfbmFtZV9kaGl2ZWhpIjoiIiwibWlkZGxlX25hbWVfZGhpdmVoaSI6IiIsImxhc3RfbmFtZV9kaGl2ZWhpIjoiIiwiZnVsbF9uYW1lX2RoaXZlaGkiOiIiLCJwZXJtYW5lbnRfYWRkcmVzcyI6IntcIkFkZHJlc3NMaW5lMVwiOlwiYXNkXCIsXCJBZGRyZXNzTGluZTJcIjpcIlwiLFwiUm9hZFwiOlwiXCIsXCJBdG9sbEFiYnJldmlhdGlvblwiOlwiS1wiLFwiQXRvbGxBYmJyZXZpYXRpb25EaGl2ZWhpXCI6XCLehlwiLFwiSXNsYW5kTmFtZVwiOlwiTWFsZSdcIixcIklzbGFuZE5hbWVEaGl2ZWhpXCI6XCLeid6n3o3erFwiLFwiSG9tZU5hbWVEaGl2ZWhpXCI6XCJcIixcIldhcmRcIjpcIkRoYWZ0aGFydVwiLFwiV2FyZEFiYnJldmlhdGlvbkVuZ2xpc2hcIjpcIkRoYWZ0aGFydVwiLFwiV2FyZEFiYnJldmlhdGlvbkRoaXZlaGlcIjpcIlwiLFwiQ291bnRyeVwiOlwiTWFsZGl2ZXNcIixcIkNvdW50cnlJU09UaHJlZURpZ2l0Q29kZVwiOlwiNDYyXCIsXCJDb3VudHJ5SVNPVGhyZWVMZXR0ZXJDb2RlXCI6XCJNRFZcIn0iLCJ1c2VyX3R5cGVfZGVzY3JpcHRpb24iOiJNYWxkaXZpYW4iLCJtb2JpbGUiOiI3NzMwMDE4IiwicGhvdG8iOiJodHRwczovL2VmYWFzLWFwaS5kZXZlbG9wZXIuZ292Lm12L3VzZXIvcGhvdG8iLCJjb3VudHJ5X25hbWUiOiJNYWxkaXZlcyIsImxhc3RfdmVyaWZpZWRfZGF0ZSI6IiIsImFtciI6WyJwd2QiXX0.bD4WhgwKYTx1--Z3-OGGGgN-nDto-g2UH_QHmQmxLDtLoWfIlatVb9jU7kiArseUvnzXXZ-mNNC9ACSoWZwb1l3uuKs50DkR6iybGx8NGH9kA_TeM6enirbRXO5s4njCDgCrhNV6c_j8hH_OHef0Tiu43wpWVF79ayxv1SRv54tZtb9NE7tumFTEcI_bwVMDxe499ZCgilNGcaB6xNOJY4_Iw166R8eZ_Q37ccVlQRSPaKtsf-LRdVjDTA8T7D4Gbl2etOpqC0NcN1eF2y5fHWGsggRawVVTR8b3LtAYm4bJxuG9j5Cj-EPDBfBAo0LMWCsqVzC0O2p8tj6C8vuOgg",
                    "access_token": "' . self::ACCESS_TOKEN . '",
                    "expires_in": 3600,
                    "token_type": "Bearer",
                    "scope": "openid efaas.profile efaas.birthdate efaas.email efaas.mobile efaas.photo efaas.permanent_address efaas.country efaas.passport_number efaas.work_permit_status"
                }',
                true
            ));

        return $provider;
    }

    protected function setMockProvider($provider)
    {
        Socialite::shouldReceive('driver')
            ->with('efaas')
            ->andReturn($provider);
    }
}
