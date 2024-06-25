<?php

namespace Javaabu\EfaasSocialite;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Javaabu\EfaasSocialite\Enums\UserTypes;
use Laravel\Socialite\Two\User;

/**
 * @property string $sub Unique user key assigned to the user
 * @property string $first_name First name of the user
 * @property string $middle_name Middle name of the user
 * @property string $last_name Last name of the user
 * @property string $first_name_dhivehi First name of the user in Dhivehi (Maldivians only)
 * @property string $middle_name_dhivehi Middle name of the user in Dhivehi (Maldivians only)
 * @property string $last_name_dhivehi Last name of the user in dhivehi (Maldivians only)
 * @property string $gender Gender of the user
 * @property string $idnumber Identification number of the user. National ID number for Maldivians. Work permit number for work permit holders. Passport number for other foreigners.
 * @property bool $verified Indicates if the user is verified
 * @property string $verification_type Type of verification taken by the user (Biometric / In-Person)
 * @property Carbon $last_verified_date The last date when the user was verified either using biometrics or by visiting an eFaas verification counter
 * @property string $user_type_description Indicates the type of user (Maldivian / Work Permit Holder / Foreigner)
 * @property Carbon $updated_at The last date when the user information was updated
 * @property string $email Email of the user
 * @property string $mobile Mobile number of the user
 * @property string $country_dialing_code Dialing code of the registered number
 * @property string $birthdate Date of birth of the user
 * @property string $photo Photo of the user
 * @property bool $is_workpermit_active Boolean indicating if the work permit is active (only applicable to work permit holders.)
 * @property string $passport_number Passport number of the user
 * @property string $country_name Name of the country of the user
 * @property string $country_code ISO 3-digit code
 * @property string $country_code_alpha3 ISO alpha3 code

 * @property string $full_name Full name of the user
 * @property string $full_name_dhivehi Full name of the user in Dhivehi (only applicable for Maldivians)
 * @property EfaasAddress $permanent_address Permanent address of the user
 */
class EfaasUser extends User
{
    public $id_token;

    public function setIdToken($id_token)
    {
        $this->id_token = $id_token;

        return $this;
    }

    /**
     * Check if is a maldivian
     *
     * @return boolean
     */
    public function isMaldivian()
    {
        return $this->user_type_description == UserTypes::MALDIVIAN;
    }

    /**
     * Get the full name in Dhivehi
     *
     * @return string
     */
    public function getDhivehiName()
    {
        return $this->full_name_dhivehi;
    }
}
