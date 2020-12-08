<?php

namespace Javaabu\EfaasSocialite\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Javaabu\EfaasSocialite\EfaasProvider;

class EfaasSocialiteServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);

        $socialite->extend(
            'efaas',
            function ($app) use ($socialite) {
                $config = $app['config']['services.efaas'];
                return $socialite->buildProvider(EfaasProvider::class, $config);
            }
        );
    }
}
