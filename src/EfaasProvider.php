<?php

namespace Javaabu\EfaasSocialite;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Javaabu\EfaasSocialite\Exceptions\JwtTokenInvalidException;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Javaabu\EfaasSocialite\EfaasUser as User;
use Lcobucci\Clock\Clock;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Strobotti\JWK\KeySetFactory;

class EfaasProvider extends AbstractProvider implements ProviderInterface
{

    const DEVELOPMENT_EFAAS_URL = 'https://developer.gov.mv/efaas';
    const PRODUCTION_EFAAS_URL = 'https://efaas.gov.mv';
    const ONE_TAP_LOGIN_KEY = 'efaas_login_code';

    protected $enc_type = PHP_QUERY_RFC1738;

    /**
     * Public keys cache
     */
    protected $public_keys = [];

    /**
     * Indicates if Efaas routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'openid',
        'efaas.profile',
        'efaas.birthdate',
        'efaas.email',
        'efaas.mobile',
        'efaas.photo',
        'efaas.permanent_address',
        'efaas.country',
        'efaas.passport_number',
        'efaas.work_permit_status'
    ];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

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
        $endpoint = ltrim($endpoint, '/');

        return $this->getBaseApiUrl('connect/' . $endpoint);
    }

    /**
     * Get correct endpoint for API
     *
     * @param $endpoint
     * @return string
     */
    protected function getBaseApiUrl($endpoint = '')
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
            'response_type' => 'code' . (! $this->usesPKCE() ? ' id_token' : ''),
            'response_mode' => 'form_post',
            'scope' => $this->formatScopes($this->getScopes(), $this->scopeSeparator),
        ];

        // add the efaas login code if provided
        if ($login_code = $this->getLoginCode()) {
            $fields['acr_values'] = self::ONE_TAP_LOGIN_KEY.':'.$login_code;
        }

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        if ($this->usesPKCE()) {
            $fields['code_challenge'] = $this->getCodeChallenge();
            $fields['code_challenge_method'] = $this->getCodeChallengeMethod();
        } else {
            $fields['nonce'] = $this->getState();
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
     * Create a user instance from the given data.
     *
     * @param  array  $response
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function userInstance(array $response, array $user)
    {
        /** @var EfaasUser $user */
        $user = parent::userInstance($response, $user);

        $id_token = Arr::get($response, 'id_token');

        $user->setIdToken($id_token);

        if ($id_token) {
            $sid = $this->getSidFromToken($id_token);
            $user->setSid($sid);
        }

        return $user;
    }

    /**
     * Parse the JWT
     *
     * @return Token
     */
    public function parseJWT($token)
    {
        // parse the token
        $jwt_parser = new Parser(new JoseEncoder());
        return $jwt_parser->parse($token);
    }

    /**
     * Get the current system time as lobucci/clock clock
     *
     * @return Clock
     */
    public function getCurrentSystemTime(): Clock
    {
        return SystemClock::fromSystemTimezone();
    }

    /**
     * Validates and extracts id from an id token or logout token
     *
     * @param $token
     * @return void
     */
    public function getSidFromToken($token)
    {
        // parse the token
        $jwt = $this->parseJWT($token);

        // find the key used to sign the JWT
        $kid = $jwt->headers()->get('kid');

        // get the public key
        $public_key = $this->getPublicKey($kid);

        // create the JWT configuration for verification
        $config = Configuration::forAsymmetricSigner(
            new \Lcobucci\JWT\Signer\Rsa\Sha256(),
            InMemory::plainText('empty_private_key'), // private key is not needed for validation
            InMemory::plainText($public_key)
        );

        try {
            // validate the token
            $config->validator()->assert($jwt, new SignedWith($config->signer(), $config->verificationKey()));
            $config->validator()->assert($jwt, new StrictValidAt($this->getCurrentSystemTime()));
        } catch (RequiredConstraintsViolated $e) {
            throw new JwtTokenInvalidException($e->getMessage());
        }

        $sid = $jwt->claims()->get('sid');

        return $sid;
    }


    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     * @return User
     */
    protected function mapUserToObject(array $user)
    {
        $permanent_address = EfaasAddress::make(Arr::get($user, 'permanent_address'));
        $dob = Arr::get($user, 'birthdate');
        $updated_at = Arr::get($user, 'updated_at');
        $last_verified_date = Arr::get($user, 'last_verified_date');
        $datetime_format = 'd/m/Y h:i:s A';

        return (new User)->setRaw($user)->map([
            'gender' => Arr::get($user, 'gender'),
            'idnumber' => Arr::get($user, 'idnumber'),
            'email' => Arr::get($user, 'email'),
            'birthdate' => $dob ? Carbon::createFromFormat('d/m/Y', $dob) : null,
            'passport_number' => Arr::get($user, 'passport_number'),
            'is_workpermit_active' => Arr::get($user, 'is_workpermit_active') == 'True',
            'updated_at' =>  $updated_at ? Carbon::createFromFormat($datetime_format, $updated_at) : null,
            'country_dialing_code' => Arr::get($user, 'country_dialing_code'),
            'country_code' => (int) Arr::get($user, 'country_code'),
            'country_code_alpha3' => Arr::get($user, 'country_code_alpha3'),
            'verified' => Arr::get($user, 'verified') == 'True',
            'verification_type' => Arr::get($user, 'verification_type'),
            'first_name' => Arr::get($user, 'first_name'),
            'middle_name' => Arr::get($user, 'middle_name'),
            'last_name' => Arr::get($user, 'last_name'),
            'full_name' => Arr::get($user, 'full_name'),
            'first_name_dhivehi' => Arr::get($user, 'first_name_dhivehi'),
            'middle_name_dhivehi' => Arr::get($user, 'middle_name_dhivehi'),
            'last_name_dhivehi' => Arr::get($user, 'last_name_dhivehi'),
            'full_name_dhivehi' => Arr::get($user, 'full_name_dhivehi'),
            'permanent_address' => $permanent_address ?: null,
            'user_type_description' => Arr::get($user, 'user_type_description'),
            'mobile' => Arr::get($user, 'mobile'),
            'photo' => Arr::get($user, 'photo'),
            'country_name' => Arr::get($user, 'country_name'),
            'last_verified_date' => $last_verified_date ? Carbon::createFromFormat($datetime_format, $last_verified_date) : null,
            'sub' => Arr::get($user, 'sub'),

            // Socialite Specific
            'name' => Arr::get($user, 'full_name'),
            'avatar' => Arr::get($user, 'photo') ?: null,
            'nickname' => Arr::get($user, 'first_name'),
        ]);
    }

    /**
     * Get the jwks url to the signing keys
     *
     * @return string
     */
    protected function getJwksUrl()
    {
        return $this->getBaseApiUrl('.well-known/openid-configuration/jwks');
    }

    /**
     * Get the jwks response from the jwks url
     *
     * @return array|string
     */
    public function getJwksResponse(bool $as_json = false)
    {
        $response = $this->getHttpClient()->get($this->getJwksUrl());
        $body = $response->getBody();

        return $as_json ? $body : json_decode($body, true);
    }

    /**
     * Get the public key for the given key id
     * @return ?string $public_key PEM format public key or null if key not found
     */
    public function getPublicKey(string $kid)
    {
        // check if public key is already there
        if (isset($this->public_keys[$kid])) {
            return $this->public_keys[$kid];
        }

        // get the jwks response
        $jwks = $this->getJwksResponse(true);
        $key_set = (new KeySetFactory())->createFromJSON($jwks);

        $key = $key_set->getKeyById($kid);

        if (! $key) {
            // abort
            return null;
        }

        $pem_key = (new \Strobotti\JWK\KeyConverter())->keyToPem($key);

        $this->public_keys[$kid] = $pem_key;

        return $pem_key;
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
     * @param string       $id_token  ID token (obtained at login)
     * @param string|null  $redirect  URL to which the RP is requesting that the End-User's User Agent
     * be redirected after a logout has been performed. The value MUST have been previously
     * registered with the OP. Value can be null.
     * https://github.com/jumbojett/OpenID-Connect-PHP/blob/master/src/OpenIDConnectClient.php
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logOut($id_token, $redirect)
    {
        $signout_endpoint = $this->getApiUrl('endsession');

        $signout_params = [
            'id_token_hint' => $id_token,
            'state' => $this->getState(),
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
