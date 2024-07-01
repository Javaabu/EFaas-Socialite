<?php

namespace Javaabu\EfaasSocialite\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\EfaasSocialite\Contracts\EfaasSessionHandlerContract;
use Javaabu\EfaasSocialite\EfaasProvider;

class EfaasSocialiteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // declare publishes
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'efaas-migrations');

            $this->publishes([
                __DIR__ . '/../../config/efaas.php' => config_path('efaas.php'),
            ], 'efaas-config');
        }

        $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);

        $socialite->extend(
            'efaas',
            function ($app) use ($socialite) {
                $config = $app['config']['efaas.client'];
                return $socialite->buildProvider(EfaasProvider::class, $config);
            }
        );

        $this->registerRoutes();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // merge package config with user defined config
        $this->mergeConfigFrom(__DIR__ . '/../../config/efaas.php', 'efaas');

        $this->app->singleton(EfaasSessionHandlerContract::class, config('efaas.session_handler'));
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
