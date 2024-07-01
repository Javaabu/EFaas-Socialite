<?php

namespace Javaabu\EfaasSocialite;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
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
 * @property Carbon $birthdate Date of birth of the user
 * @property string $photo Photo of the user
 * @property bool $is_workpermit_active Boolean indicating if the work permit is active (only applicable to work permit holders.)
 * @property string $passport_number Passport number of the user
 * @property string $country_name Name of the country of the user
 * @property int $country_code ISO 3-digit code
 * @property string $country_code_alpha3 ISO alpha3 code

 * @property string $full_name Full name of the user
 * @property string $full_name_dhivehi Full name of the user in Dhivehi (only applicable for Maldivians)
 * @property EfaasAddress $permanent_address Permanent address of the user
 */
class EfaasUser extends User
{
    /**
     * The HTTP Client instance.
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public $id_token;
    public $sid;

    /**
     * Mime type of the photo
     * @var string
     */
    protected $photo_mimetype;

    /**
     * File extension of the photo
     * @var string
     */
    protected $photo_extension;

    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

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

    /**
     * Get the user photo response
     *
     * @return array
     */
    public function getPhotoResponse(string $url)
    {
        $response = $this->getHttpClient()->get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get the photo base 64
     * @return string
     */
    protected function loadPhotoData()
    {
        $photo_response = $this->getPhotoResponse($this->photo);

        $base64 = $photo_response['data']['photo'] ?? null;

        if (! $base64) {
            return null;
        }

        // https://stackoverflow.com/questions/45157429/get-image-information-from-base64-encoded-string
        $imgdata = base64_decode($base64);
        $image_info = getimagesizefromstring($imgdata);

        $this->photo_mimetype = $image_info['mime'];
        $this->photo_extension = ltrim(image_type_to_extension($image_info[2]), '.');

        return $base64;
    }

    /**
     * Get the mime type of the photo
     * @return string
     */
    public function getPhotoMimetype()
    {
        if (! $this->photo_mimetype) {
            $this->loadPhotoData();
        }

        return $this->photo_mimetype;
    }

    /**
     * Get the file extension of the photo
     * @return string
     */
    public function getPhotoExtension()
    {
        if (! $this->photo_extension) {
            $this->loadPhotoData();
        }

        return $this->photo_extension;
    }

    /**
     * Get the photo base 64
     * @return string
     */
    public function getPhotoBase64()
    {
        return $this->loadPhotoData();
    }

    /**
     * Get the photo as a data url
     */
    public function getPhotoDataUrl()
    {
        $base64 = $this->getPhotoBase64();

        return $base64 ? 'data:' . $this->getPhotoMimetype() . ';base64,' . $base64 : null;
    }

    /**
     * Get the avatar / image URL for the user as a data url
     *
     * @return string|null
     */
    public function getAvatar()
    {
        return $this->getPhotoDataUrl();
    }

    /**
     * Save the image to the given directory
     *
     * @return string
     */
    public function savePhoto(string $filename, string $directory = '')
    {
        // get base64
        $base64 = $this->getPhotoBase64();
        $extension = $this->getPhotoExtension();

        $directory = rtrim($directory, '/');

        // normalize the file name
        if (! Str::endsWith($filename, '.' . $extension)) {
            $filename .= '.' . $extension;
        }

        $full_path = ($directory ? $directory . '/' : '') . $filename;

        file_put_contents($full_path, base64_decode($base64));

        return $full_path;
    }

    /**
     * Get a instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }
}
