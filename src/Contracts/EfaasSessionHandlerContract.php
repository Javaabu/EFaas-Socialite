<?php

namespace Javaabu\EfaasSocialite\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface EfaasSessionHandlerContract
{
    /**
     * Find the efaas session by laravel session id
     */
    public function findByLaravelSessionId(string $laravel_session_id): ?EfaasSessionContract;

    /**
     * Find the efaas sessions by sid
     */
    public function findBySid(string $sid): Collection;

    /**
     * Logout all sessions of the sid
     */
    public function logoutSessions(string $sid, ?string $guard = null);

    /**
     * Save the sid
     */
    public function saveSid(string $sid, ?string $laravel_session_id = null): EfaasSessionContract;

    /**
     * Get the current laravel session id
     */
    public function getCurrentLaravelSessionId(): string;

    /**
     * Get the saved user id from the laravel sessions table
     */
    public function findUserIdByLaravelSessionId(string $laravel_session_id): ?string;
}
