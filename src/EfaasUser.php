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

    /**
     * Get the full name in Dhivehi
     *
     * @return string
     */
    public function getDhivehiName()
    {
        $name = $this->fname_dhivehi;

        if ($middle_name = $this->mname_dhivehi) {
            $name .= ' '.$middle_name;
        }

        if ($last_name = $this->lname_dhivehi) {
            $name .= ' '.$last_name;
        }

        return $name;
    }
}
