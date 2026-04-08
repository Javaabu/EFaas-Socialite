<?php

namespace Javaabu\EfaasSocialite\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Javaabu\EfaasSocialite\EfaasProvider;
use Javaabu\EfaasSocialite\EfaasSessionHandler;
use Javaabu\EfaasSocialite\Tests\TestCase;
use Javaabu\EfaasSocialite\Tests\TestSupport\Models\User;
use Laravel\Socialite\Facades\Socialite;

class EfaasSessionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function getEfaasSessionHandler(): EfaasSessionHandler
    {
        /** @var EfaasSessionHandler $provider */
        return Socialite::driver('efaas')->sessionHandler();
    }

    protected function getUser(?string $name = null, ?string $email = null, ?string $remember_token = null): User
    {
        $user = new User();
        $user->name = $name ?: 'Test User';
        $user->email = $email ?: 'test@example.com';
        $user->password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $user->remember_token = $remember_token ?: Str::random(10);
        $user->save();

        return $user;
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

    public function test_it_can_find_session_user_id_by_laravel_session_id()
    {
        $this->withoutExceptionHandling();

        $user = $this->getUser('Test', 'test@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        $this->app['config']->set('session.driver', 'database');

        $this->actingAs($user);

        $laravel_session_id = session()->getId();
        session()->save();

        $this->assertDatabaseHas('sessions', [
            'id' => $laravel_session_id,
            'user_id' => $user->id,
        ]);

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID, $laravel_session_id);
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => $laravel_session_id,
            'efaas_sid' => self::SID
        ]);

        $user_id = $handler->findUserIdByLaravelSessionId($laravel_session_id);

        $this->assertEquals($user->id, $user_id);
    }

    public function test_it_can_cycle_remember_token_when_efaas_user_is_logged_out()
    {
        $this->withoutExceptionHandling();

        $user = $this->getUser('Test', 'test@example.com', 'testtoken');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Test',
            'email' => 'test@example.com',
            'remember_token' => 'testtoken',
        ]);

        $this->app['config']->set('session.driver', 'database');

        $this->actingAs($user);

        $laravel_session_id = session()->getId();
        session()->save();

        $this->assertDatabaseHas('sessions', [
            'id' => $laravel_session_id,
            'user_id' => $user->id,
        ]);

        $handler = $this->getEfaasSessionHandler();

        $session = $handler->saveSid(self::SID, $laravel_session_id);
        $id = $session->id;

        $this->assertDatabaseHas('efaas_sessions', [
            'id' => $id,
            'laravel_session_id' => $laravel_session_id,
            'efaas_sid' => self::SID
        ]);

        $handler->logoutSessions(self::SID);

        $this->assertDatabaseMissing('sessions', [
            'id' => $laravel_session_id,
        ]);

        $this->assertDatabaseMissing('efaas_sessions', [
            'efaas_sid' => self::SID,
        ]);

        $user = $user->fresh();

        $this->assertNotEquals($user->remember_token, 'testtoken');
    }
}
