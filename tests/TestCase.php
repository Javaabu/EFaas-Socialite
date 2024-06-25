<?php

namespace Javaabu\EfaasSocialite\Tests;

use Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    const CLIENT_ID = 'abc44ec3-aa7b-4eab-a50e-4d18f17c3f62';
    const CLIENT_SECRET = '9fz11cd8-7bb8-40fa-b3eb-bc5dc43439c3';
    const REDIRECT_URL = 'http://localhost/oauth/efaas/callback';


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
}
