<?php

namespace Javaabu\EfaasSocialite\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\EfaasSocialite\EfaasProvider;

class EfaasSocialiteServiceProvider extends ServiceProvider
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

        $this->registerRoutes();
    }

    /**
     * Register the eFaas routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (EfaasProvider::$registersRoutes) {
            Route::group([
                'as' => 'efaas.',
                'namespace' => '\Javaabu\EfaasSocialite\Http\Controllers',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
            });
        }
    }
}
