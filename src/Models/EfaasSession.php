<?php

namespace Javaabu\EfaasSocialite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Javaabu\EfaasSocialite\Contracts\EfaasSessionContract;
use Laravel\Socialite\Facades\Socialite;

class EfaasSession extends Model implements EfaasSessionContract
{
    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('efaas.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('efaas.table_name'));
        }

        parent::__construct($attributes);
    }



    public function logOut(?string $guard = null)
    {
        if (! $guard) {
            $guard = config('efaas.session_guard');
        }

        $user_id = Socialite::driver('efaas')
            ->sessionHandler()
            ->findUserIdByLaravelSessionId($this->laravel_session_id);

        // first destroy the laravel session
        session()->getHandler()->destroy($this->laravel_session_id);

        // cycle the remember token
        if ($user_id) {
            $auth_guard = Auth::guard($guard);
            $provider = $auth_guard->getProvider();

            $user = $provider->retrieveById($user_id);

            if ($user) {
                $user->setRememberToken($token = Str::random(60));
                $provider->updateRememberToken($user, $token);
            }
        }

        // then destroy self
        $this->delete();

        // then destroy self
        $this->delete();
    }
}
