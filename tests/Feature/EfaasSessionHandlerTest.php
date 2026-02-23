<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\EfaasSocialite\EfaasProvider;
use Javaabu\EfaasSocialite\EfaasSessionHandler;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Laravel\Socialite\Facades\Socialite;

class EfaasSessionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function getEfaasSessionHandler(): EfaasSessionHandler
    {
        /** @var EfaasSessionHandler $provider */
        return Socialite::driver('efaas')->sessionHandler();
    }

    public function test_it_can_get_a_session_handler_instance()
    {
        $this->withoutExceptionHandling();

        /** @var EfaasProvider $provider */
        $provider = Socialite::driver('efaas');

        $handler = $provider->sessionHandler();

        $this->assertInstanceOf(EfaasSessionHandler::class, $handler);
    }

    public function test_it_can_save_an_sid()
    {
        $this->withoutExceptionHandling();

        $handler = $this->getEfaasSessionHandler();

        $handler->saveSid(self::SID, '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');

        $this->assertDatabaseHas('efaas_sessions', [
            'laravel_session_id' => '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB',
            'efaas_sid' => self::SID
        ]);
    }

    public function test_it_uses_the_current_laravel_session_id_by_default_when_saving_sid()
    {
        $this->withoutExceptionHandling();

        $this->app['config']->set('session.driver', 'database');

        $session_id_1 = session()->getId();
        session()->save();

        $this->assertDatabaseHas('sessions', [
            'id' => $session_id_1
        ]);

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID);
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => $session_id_1,
            'efaas_sid' => self::SID
        ]);
    }

    public function test_it_overwrites_the_sid_when_saving_a_new_sid_to_the_save_laravel_session_id()
    {
        $this->withoutExceptionHandling();

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID, '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB',
            'efaas_sid' => self::SID
        ]);

        $handler->saveSid('123', '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB',
            'efaas_sid' => '123'
        ]);
    }

    public function test_it_can_find_an_efaas_session_by_laravel_session_id()
    {
        $this->withoutExceptionHandling();

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID, '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB',
            'efaas_sid' => self::SID
        ]);

        $new_session = $handler->findByLaravelSessionId('3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');

        $this->assertEquals($id, $new_session->id);
        $this->assertEquals(self::SID, $new_session->efaas_sid);
    }

    public function test_it_can_find_multiple_efaas_sessions_by_sid()
    {
        $this->withoutExceptionHandling();

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID, '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB');
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => '3gDIVZ5lMHmzYg9X1YM53YYhwI21EOaznL2UWorB',
            'efaas_sid' => self::SID
        ]);

        $new_sessions = $handler->findBySid(self::SID);

        $new_session = $new_sessions->first();

        $this->assertEquals($id, $new_session->id);
        $this->assertEquals(self::SID, $new_session->efaas_sid);
    }

    public function test_it_can_destroy_multiple_efaas_sessions_by_sid()
    {
        $this->withoutExceptionHandling();

        $this->app['config']->set('session.driver', 'database');

        $session_id_1 = session()->getId();
        session()->save();

        $this->assertDatabaseHas('sessions', [
            'id' => $session_id_1
        ]);

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID);
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => $session_id_1,
            'efaas_sid' => self::SID
        ]);

        // logout
        $handler->logoutSessions(self::SID);

        $this->assertDatabaseMissing('sessions', [
            'id' => $session_id_1,
        ]);

        $this->assertDatabaseMissing('efaas_sessions', [
            'id' => $id,
        ]);
    }
}
