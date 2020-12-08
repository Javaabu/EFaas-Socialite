<?php

namespace Javaabu\EfaasSocialite;

use Javaabu\EfaasSocialite\Enums\UserTypes;
use Laravel\Socialite\Two\User;

class EfaasUser extends User
{
    /**
     * Check if is a maldivian
     *
     * @return boolean
     */
    public function isMaldivian()
    {
        return $this->offsetGet('user_type') == UserTypes::MALDIVIAN;
    }
}
