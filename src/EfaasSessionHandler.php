<?php

namespace Javaabu\EfaasSocialite;

use Illuminate\Database\Eloquent\Collection;
use Javaabu\EfaasSocialite\Contracts\EfaasSessionContract;
use Javaabu\EfaasSocialite\Contracts\EfaasSessionHandlerContract;

class EfaasSessionHandler implements EfaasSessionHandlerContract
{
    public static function modelClass(): string
    {
        return config('efaas.session_model');
    }

    public function findByLaravelSessionId(string $laravel_session_id): ?EfaasSessionContract
    {
        $model_class = static::modelClass();

        return $model_class::query()
            ->where('laravel_session_id', $laravel_session_id)
            ->first();
    }

    public function findBySid(string $sid): Collection
    {
        $model_class = static::modelClass();

        return $model_class::query()
            ->where('efaas_sid', $sid)
            ->get();
    }

    public function logoutSessions(string $sid)
    {
        $sessions = $this->findBySid($sid);

        /** @var EfaasSessionContract $session */
        foreach ($sessions as $session) {
            $session->logOut();
        }
    }

    public function saveSid(string $sid, ?string $laravel_session_id = null): EfaasSessionContract
    {
        if (! $laravel_session_id) {
            $laravel_session_id = $this->getCurrentLaravelSessionId();
        }

        $model_class = static::modelClass();

        // find existing session
        $session = $this->findByLaravelSessionId($laravel_session_id);

        if (! $session) {
            $session = new $model_class();
            $session->laravel_session_id = $laravel_session_id;
        }

        $session->efaas_sid = $sid;
        $session->save();

        return $session;
    }

    public function getCurrentLaravelSessionId(): string
    {
        return session()->getId();
    }
}
