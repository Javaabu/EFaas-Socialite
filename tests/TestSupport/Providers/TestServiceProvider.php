<?php

namespace Javaabu\EfaasSocialite\Tests\TestSupport\Providers;

use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom([
            __DIR__ . '/../database',
            __DIR__ . '/../../../database/migrations',
        ]);
    }

    /**
     * Register the application services.
     */
    public function register()
    {

    }
}
