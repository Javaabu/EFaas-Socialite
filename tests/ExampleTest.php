<?php

namespace Javaabu\EfaasSocialite\Tests;

use Orchestra\Testbench\TestCase;
use Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [EfaasSocialiteServiceProvider::class];
    }

    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
