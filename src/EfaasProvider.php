<?php

namespace Javaabu\EfaasSocialite;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Javaabu\EfaasSocialite\Enums\UserStates;
use Javaabu\EfaasSocialite\Enums\UserTypes;
use Javaabu\EfaasSocialite\Enums\VerificationLevels;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Javaabu\EfaasSocialite\EfaasUser as User;

class EfaasProvider extends AbstractProvider implements ProviderInterface
{

    const DEVELOPMENT_EFAAS_URL = 'https://developer.gov.mv/efaas/connect';
    const PRODUCTION_EFAAS_URL = 'https://efaas.gov.mv/connect';
    const ONE_TAP_LOGIN_KEY = 'efaas_login_code';

    protected $stateless = true;

    protected $enc_type = PHP_QUERY_RFC1738;

    /**
     * Indicates if Efaas routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * Get correct endpoint for API
     *
     * @param $key
     * @param null $default
     * @return string
     */
    protected function config($key, $default = null)
    {
        return config("services.efaas.$key", $default);
    }

    /**
     * Check if is in production
     *
     * @return boolean
     */
    protected function isProduction()
    {
        return $this->config('mode') == 'production';
    }

    /**
     * Get correct endpoint for API
     *
     * @return string
     */
    protected function getEfaasUrl()
    {
        $url = $this->config('api_url');

        if (! $url) {
            $url = $this->isProduction() ? self::PRODUCTION_EFAAS_URL : self::DEVELOPMENT_EFAAS_URL;
        }

        return rtrim($url, '/');
    }

    /**
     * Get correct endpoint for API
     *
     * @param $endpoint
     * @return string
     */
    protected function getApiUrl($endpoint = '')
    {
        $api_url = $this->getEfaasUrl();
        $endpoint = ltrim($endpoint, '/');

        if ($endpoint) {
            $api_url .= "/$endpoint";
        }

        return $api_url;
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getApiUrl('authorize'), $state);
    }

    /**
     * Get the login code from the request.
     *
     * @return string
     */
    protected function getLoginCode()
    {
        return $this->request->input(self::ONE_TAP_LOGIN_KEY);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUrl,
            'response_type' => 'code id_token',
            'response_mode' => 'form_post',
            'scope' => $this->config('scopes'),
            'nonce' => $this->getState()
        ];

        // add the efaas login code if provided
        if ($login_code = $this->getLoginCode()) {
            $fields['acr_values'] = self::ONE_TAP_LOGIN_KEY.':'.$login_code;
        }


        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }


    /**
    * Get the code from the request.
    *
    * @return string
    */
    protected function getCode()
    {
        return $this->request->input('code');
    }


    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     * @return User
     */
    protected function mapUserToObject(array $user)
    {
        $address = json_decode(Arr::get($user, 'address'), true);
        $user_type = Arr::get($user, 'user_type');
        $user_state = Arr::get($user, 'user_state');
        $verification_level = Arr::get($user, 'verification_level');
        $dob = Arr::get($user, 'birthdate');
        $updated_at = Arr::get($user, 'updated_at');
        $given_name = Arr::get($user, 'given_name');

        return (new User)->setRaw($user)->map([
            'name' => Arr::get($user, 'name'),
            'given_name' => $given_name,
            'middle_name' => Arr::get($user, 'middle_name'),
            'family_name' => Arr::get($user, 'family_name'),
            'idnumber' => Arr::get($user, 'idnumber'),
            'gender' => Arr::get($user, 'gender'),
            'address' => $address ?: null,
            'phone_number' => Arr::get($user, 'phone_number') ?: null,
            'email' => Arr::get($user, 'email'),
            'fname_dhivehi' => Arr::get($user, 'fname_dhivehi'),
            'mname_dhivehi' => Arr::get($user, 'mname_dhivehi'),
            'lname_dhivehi' => Arr::get($user, 'lname_dhivehi'),
            'user_type' => Arr::get($user, 'user_type'),
            'user_type_desc' => UserTypes::getDescription($user_type),
            'verification_level' => $verification_level,
            'verification_level_desc' => VerificationLevels::getDescription($verification_level),
            'user_state' => $user_state,
            'user_state_desc' => UserStates::getDescription($user_state),
            'birthdate' => $dob ? Carbon::parse($dob) : null,
            'is_workpermit_active' => Arr::get($user, 'is_workpermit_active') == 'True',
            'updated_at' =>  $updated_at ? Carbon::parse($updated_at) : null,
            'avatar' => Arr::get($user, 'picture') ?: null,
            'nickname' => $given_name ?: null,
        ]);
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->getApiUrl('token');
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     * @return array
     */
    protected function getTokenFields($code)
    {
        return Arr::add(
            parent::getTokenFields($code), 'grant_type', 'authorization_code'
        );
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post($this->getApiUrl('userinfo'), [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return  json_decode($response->getBody()->getContents(), true);
    }

    /**
     * It calls the end-session endpoint of the OpenID Connect provider to notify the OpenID
     * Connect provider that the end-user has logged out of the relying party site
     * (the client application).
     *
     * @param string $access_token ID token (obtained at login)
     * @param string|null $redirect URL to which the RP is requesting that the End-User's User Agent
     * be redirected after a logout has been performed. The value MUST have been previously
     * registered with the OP. Value can be null.
     * https://github.com/jumbojett/OpenID-Connect-PHP/blob/master/src/OpenIDConnectClient.php
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logOut($access_token, $redirect)
    {
        $signout_endpoint = $this->getApiUrl('endsession');

        $signout_params = [
            'id_token_hint' => $access_token
        ];

        if ($redirect) {
            $signout_params['post_logout_redirect_uri'] = $redirect;
        }

        $signout_endpoint  .= (strpos($signout_endpoint, '?') === false ? '?' : '&') . http_build_query( $signout_params, null, '&', $this->enc_type);

        return redirect()->to($signout_endpoint);
    }

    /**
     * Get a Social User instance from a known auth code.
     *
     * @param  string  $code
     * @return \Laravel\Socialite\Two\User
     */
    public function userFromCode($code)
    {
        $response = $this->getAccessTokenResponse($code);

        $token = Arr::get($response, 'access_token');

        return $this->userFromToken($token);
    }

    /**
     * Configure Efaas to not register its routes.
     *
     * @return void
     */
    public static function ignoreRoutes()
    {
        static::$registersRoutes = false;
    }
}
