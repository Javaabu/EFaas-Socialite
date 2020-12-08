<?php

namespace Javaabu\EfaasSocialite;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Javaabu\EfaasSocialite\EfaasUser as User;

class EfaasProvider extends AbstractProvider implements ProviderInterface
{

    const DEFAULT_EFAAS_URL = 'https://developer.egov.mv/efaas/connect';

    protected $stateless = true;

    /**
     * Get correct endpoint for API
     *
     * @return string
     */
    protected function getEfaasUrl()
    {
        return rtrim(config('services.efaas.api_url') ?: self::DEFAULT_EFAAS_URL, '/');
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
            'prompt' => 'select_account',
            'response_mode' => 'form_post',
            'scope' => 'openid profile',
            'nonce' => $this->getState()
        ];

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

        return (new User)->setRaw($user)->map([
            'user_type' => $user['user_type'] ?? 1,
            'nickname' => Arr::get($user, 'nickname') ?: null,
            'name' => Arr::get($user, 'name'),
            'email' => Arr::get($user, 'email'),
            'avatar' => Arr::get($user, 'picture') ?: null,
            'address' => $address ?: null,
            'phone' => Arr::get($user, 'phone_number') ?: null,
            'dob'   => Arr::get($user, 'birthdate') ?: null,
            'gender'   => Arr::get($user, 'gender') ?: null,
            'id_no' => Arr::get($user, 'idnumber') ?: null,
            'verification_level' => Arr::get($user, 'verification_level') ?: null,
            'address_string' => $address ? $this->parseAddress($address) : null,
        ]);
    }

    /**
     * Parse address to string
     */
    protected function parseAddress($address)
    {
        $address = array_values(Arr::only($address, ['AddressLine1', 'AddressLine2', 'Ward', 'AtollAbbreviation', 'IslandName']));

        return implode("\n", $address);
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
     * @param  string $token
     * @return array
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
}
