<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Javaabu\EfaasSocialite\EfaasUser;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;

class EfaasUserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $test_image = __DIR__ . '/../TestSupport/resources/test.png';

        if (file_exists($test_image)) {
            unlink($test_image);
        }
    }

    protected function tearDown(): void
    {
        $test_image = __DIR__ . '/../TestSupport/resources/test.png';

        if (file_exists($test_image)) {
            unlink($test_image);
        }

        parent::tearDown();
    }

    public function test_it_can_write_the_photo_to_a_file()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();


        $test_image_path = __DIR__ . '/../TestSupport/resources/';
        $test_image = $test_image_path . 'test.png';

        $efaas_user->savePhoto('test.png', $test_image_path);

        $this->assertFileExists($test_image);
        $this->assertFileEquals(__DIR__ . '/../TestSupport/resources/image.png', $test_image);
    }

    public function test_it_adds_the_photo_file_extension_automatically()
    {
        $this->withoutExceptionHandling();

        $provider = $this->mockProvider();

        $this->mockUserPhoto($provider);

        $this->setMockProvider($provider);

        /** @var EfaasUser $efaas_user */
        $efaas_user = Socialite::driver('efaas')->user();


        $test_image_path = __DIR__ . '/../TestSupport/resources/';
        $test_image = $test_image_path . 'test.png';

        $efaas_user->savePhoto('test', $test_image_path);

        $this->assertFileExists($test_image);
        $this->assertFileEquals(__DIR__ . '/../TestSupport/resources/image.png', $test_image);
    }
}
