# eFaas Laravel Socialite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/efaas-socialite.svg?style=flat-square)](https://packagist.org/packages/javaabu/efaas-socialite)
[![Test Status](../../actions/workflows/run-tests.yml/badge.svg)](../../actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/efaas-socialite.svg?style=flat-square)](https://packagist.org/packages/javaabu/efaas-socialite)

[Laravel Socialite](https://github.com/laravel/socialite) Provider for [eFaas](https://efaas.gov.mv/).

**Note:** Current version of this package is based on eFaas Documentation version 2.2

<!-- TOC -->
* [eFaas Laravel Socialite](#efaas-laravel-socialite)
  * [Installation](#installation)
    * [Add configuration to your `.env` file](#add-configuration-to-your-env-file)
    * [Publishing the config file](#publishing-the-config-file)
    * [Publishing migrations](#publishing-migrations)
  * [Usage](#usage)
      * [Enabling PKCE](#enabling-pkce)
      * [Logging out the eFaas User](#logging-out-the-efaas-user)
      * [Using eFaas One-tap Login](#using-efaas-one-tap-login)
      * [Implementing Front Channel Single Sign Out](#implementing-front-channel-single-sign-out)
      * [Implementing Back Channel Single Sign Out](#implementing-back-channel-single-sign-out)
      * [Authenticating from mobile apps](#authenticating-from-mobile-apps)
      * [Changing the eFaas login prompt behaviour](#changing-the-efaas-login-prompt-behaviour)
      * [Available Methods for eFaas Provider](#available-methods-for-efaas-provider)
      * [Available Methods and Public Properties for eFaas User](#available-methods-and-public-properties-for-efaas-user)
      * [Changing the eFaas request scopes](#changing-the-efaas-request-scopes)
      * [Getting eFaas data from eFaas User object](#getting-efaas-data-from-efaas-user-object)
      * [Available eFaas data fields](#available-efaas-data-fields)
        * [Scope: efaas.openid](#scope-efaasopenid)
        * [Scope: efaas.profile](#scope-efaasprofile)
        * [Scope: efaas.email](#scope-efaasemail)
        * [Scope: efaas.mobile](#scope-efaasmobile)
        * [Scope: efaas.birthdate](#scope-efaasbirthdate)
        * [Scope: efaas.photo](#scope-efaasphoto)
        * [Scope: efaas.work_permit_status](#scope-efaasworkpermitstatus)
        * [Scope: efaas.passport_number](#scope-efaaspassportnumber)
        * [Scope: efaas.country](#scope-efaascountry)
        * [Scope: efaas.permanent_address](#scope-efaaspermanentaddress)
  * [Testing](#testing)
  * [Changelog](#changelog)
  * [Contributing](#contributing)
  * [Security](#security)
  * [Credits](#credits)
  * [License](#license)
<!-- TOC -->

## Installation

For Laravel 6.0+, you can install the package via `composer`:

``` bash
composer require javaabu/efaas-socialite
```

For Laravel 5.6, use version 1.x

``` bash
composer require javaabu/efaas-socialite:^1.0
```

**Laravel 5.5** and above uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

After updating composer, add the ServiceProvider to the providers array in config/app.php

``` bash
Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider::class,
```

### Add configuration to your `.env` file

Add the following config to your `.env` file

```dotenv
EFAAS_CLIENT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
EFAAS_CLIENT_SECRET=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
EFAAS_REDIRECT_URI=https://your-app.com/path/to/efaas/callback
EFAAS_MODE=development

# for production use
#EFAAS_MODE=production
```

### Publishing the config file

Optionally you can also publish the config file to `config/efaas.php`:

```bash
php artisan vendor:publish --provider="Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider" --tag="efaas-config"
```

This is the default content of the config file:

```php
<?php

return [

    /**
     * eFaas client config
     */
    'client' => [
        /**
         * eFaas Client ID
         */
        'client_id' => env('EFAAS_CLIENT_ID'),

        /**
         * eFaas Client Secret
         */
        'client_secret' => env('EFAAS_CLIENT_SECRET'),

        /**
         * eFaas Redirect url
         */
        'redirect' => env('EFAAS_REDIRECT_URI'),

        /**
         * Development mode
         * supports "production" and "development"
         */
        'mode' => env('EFAAS_MODE', 'development'),
    ],

    /*
     * This model will be used to store efaas session sids
     * The class must implement \Javaabu\EfaasSocialite\Contracts\EfaasSessionContract
     */
    'session_model' => \Javaabu\EfaasSocialite\Models\EfaasSession::class,

    /*
     * This handler will be used to manage saving and destroying efaas session records
     * The class must implement \Javaabu\EfaasSocialite\Contracts\EfaasSessionHandlerContract
     */
    'session_handler' => \Javaabu\EfaasSocialite\EfaasSessionHandler::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the EfaasSession model shipped with this package.
     */
    'table_name' => 'efaas_sessions',

    /*
     * This is the database connection that will be used by the migration and
     * the EfaasSession model shipped with this package. In case it's not set
     * Laravel's database.default will be used instead.
     */
    'database_connection' => env('EFAAS_SESSIONS_DB_CONNECTION'),
];


```

### Publishing migrations

This package ships with the migrations for an `efaas_sessions` table which can be used to implement back channel logout. You can publish these migrations using the following Artisan command:

```bash
php artisan vendor:publish --provider="Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider" --tag="efaas-migrations"
```

After publishing the migrations, you can run them:
```bash
php artisan migrate
```

## Usage

**Note:** A demo implementation of this package is
available [here](https://github.com/ncit-devs/Efaas-Implementation-Javaabu).

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade
installed):
Refer to the [Official Social Docs](https://laravel.com/docs/8.x/socialite#routing) for more info.

**Warning:** If you get `403 Forbidden` error when your Laravel app makes requests to the eFaas authorization endpoints,
request NCIT to whitelist your server IP.

```php
return Socialite::driver('efaas')->redirect();
```

and in your callback handler, you can access the user data like so. Remember to save the user's `id_token` and `sid` (session id).

```php
$efaas_user = Socialite::driver('efaas')->user();
$id_token = $efaas_user->id_token;
$sid = $efaas_user->sid;

session()->put('efaas_id_token', $id_token);
session()->put('efaas_sid', $sid);
```

#### Enabling PKCE

By default, this package has PKCE disabled. To enable PKCE, use the `enablePKCE()` method in both your redirect call and
the callback handler.

```php
return Socialite::driver('efaas')->enablePKCE()->redirect();
```

```php
// inside callback handler
$efaas_user = Socialite::driver('efaas')->enablePKCE()->user();
```

#### Logging out the eFaas User

In your Laravel logout redirect, redirect with the provider `logOut()` method using the id token saved during login

```php
$id_token = session('id_token');
return Socialite::driver('efaas')->logOut($id_token, $post_logout_redirect_url);
```

**Note:** Since the `id_token` can be very long, you might run into nginx errors when redirecting. To fix this you can
add the following to your nginx config. More
info [here](https://laracasts.com/discuss/channels/servers/502-bad-gateway-on-socialite).

```
fastcgi_buffers 16 16k;
fastcgi_buffer_size 32k;
```

#### Using eFaas One-tap Login

This package will automatically add an /efaas-one-tap-login endpoint to your web routes which will redirect to eFaas
with the eFaas login code.

Sometimes you may wish to customize the routes defined by the Efaas Provider. To achieve this, you first need to ignore
the routes registered by Efaas Provider by adding `EfaasProvider::ignoreRoutes` to the register method of your
application's `AppServiceProvider`:

```php
use Javaabu\EfaasSocialite\EfaasProvider;

/**
 * Register any application services.
 */
public function register(): void
{
    EfaasProvider::ignoreRoutes();
}

```

Then, you may copy the routes defined by Efaas Provider in [its routes file](/routes/web.php) to your application's
routes/web.php file and modify them to your liking:

```php
Route::group([
    'as' => 'efaas.',
    'namespace' => '\Javaabu\EfaasSocialite\Http\Controllers',
], function () {
    // Efaas routes...
});
```

#### Implementing Front Channel Single Sign Out

First, during login, in your efaas callback handler method, save the users `sid` (session ID) to your session.

```php
$efaas_user = Socialite::driver('efaas')->user();
$sid = $efaas_user->sid;

session()->put('efaas_sid', $sid);
```

Then, in your single sign out controller handler method, first retrieve the logout token's `sid` using the eFaas provider's `getLogoutSid()` method. The method will return `null` if the provided logout token is invalid. You can then compare the saved `sid` in your current session with the retrieved `sid` and logout the user if they match.

```php
...
public function handleFrontChannelSingleSignOut(Request $request)
{
    $saved_sid = session('efaas_sid');
    $request_sid = Socialite::driver('efaas')->getLogoutSid();
    
    if ($request_sid && $saved_sid == $request_sid) {
        // the logout session matches your saved sid
        // logout your user here
        auth()->guard('web')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }     
      
    return redirect()->to('/your-redirect-url')        
}
...
```

#### Implementing Back Channel Single Sign Out

For Back Channel Logout, you will need to use Laravel's `database` session driver and the provided `efaas_sessions` migration. 

During login, save the user's `sid` (session ID) using the eFaas provider's `sessionHandler()`:

```php
$efaas_user = Socialite::driver('efaas')->user();
$sid = $efaas_user->sid;

Socialite::driver('efaas')
    ->sessionHandler()
    ->saveSid($sid);
```

Then, in your single sign out controller handler method, first retrieve the logout token's `sid` using the eFaas provider's `getLogoutSid()` method. The method will return `null` if the provided logout token is invalid. You can then use the eFaas provider's `sessionHandler()` to logout all laravel sessions that match the `sid`.

```php
...
public function handleBackChannelSingleSignOut(Request $request)
{    
    $sid = Socialite::driver('efaas')->getLogoutSid();
    
    if ($sid) {
        Socialite::driver('efaas')
            ->sessionHandler()
            ->logoutSessions($sid);
    }
    
    // for back channel logout you must return 200 OK response
    return response()->json([
        'success' => ! empty($request_sid)  
    ]);    
}
...
```

#### Authenticating from mobile apps

To authenticate users from mobile apps, redirect to the eFaas login screen through a Web View on the mobile app.
Then intercept the `code` (authorization code) from eFaas after they redirect you back to your website after logging in
to eFaas.

Once your mobile app receives the auth code, send the code to your API endpoint.
You can then get the eFaas user details from your server side using the auth code as follows. Remember to use the `stateless()` option as the redirect had originated outside of your server:

```php
$efaas_user = Socialite::driver('efaas')->stateless()->userFromCode($code);
```

After you receive the eFaas user, you can then issue your own access token or API key according to whatever
authentication scheme you use for your API.

#### Changing the eFaas login prompt behaviour

The eFaas login prompt behaviour can be customized by modifying the prompt option on your redirect request

```php
return Socialite::driver('efaas')->with(['prompt' => 'select_account'])->redirect();
```

The available prompt options are:

 Option               | Description                                                                                                                                                                                                                                   
----------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- 
 **`login`**          | Forces the user to enter their credentials on that request, regardless of whether the user is already logged into eFaas.                                                                                                                      
 **`none`**           | Opposite of the `login` option. Ensures that the user isn't presented with any interactive prompt. If the request can't be completed silently by using single-sign on, the Microsoft identity platform returns an interaction_required error. 
 **`consent`**        | Triggers the OAuth consent dialog after the user signs in, asking the user to grant permissions to the app.                                                                                                                                   
 **`select_account`** | Interrupts the single sign-on, providing account selection experience listing all the accounts either in session or any remembered account or an option to choose to use a different account altogether                                       

#### Available Methods for eFaas Provider

```php
$provider = Socialite::driver('efaas');

$provider->parseJWT($token); // Parses a JWT token string into a Lcobucci\JWT\Token
$provider->getSidFromToken($token); // Validates a given JWT token and returns the sid from the token
$provider->getJwksResponse(false); // Returns the JWKs (JSON Web Keys) response as an array from the eFaas API. Optionally return the response as a json string using the optional boolean argument
$provider->getPublicKey('5CDA5CF378397733DD33EFBDA82D0F317DCC1D53RS256'); // Returns the public key from JWKs for the given key id as a PEM key string  
```

#### Available Methods and Public Properties for eFaas User

```php
$efaas_user->isMaldivian(); // Check if is a Maldivian
$efaas_user->getDhivehiName(); // Full name in Dhivehi
$efaas_user->sid; // Session id of the user
$efaas_user->id_token; // ID Token of the user
$efaas_user->token; // Access token of the user
```

#### Changing the eFaas request scopes

By default, this package adds all available scopes to the eFaas redirect. To customize the scopes you need, you can
override the scopes during the redirect.

```php
return Socialite::driver('efaas')->setScopes(['efaas.openid', 'efaas.profile'])->redirect();
```

#### Getting eFaas data from eFaas User object

``` php
$id_number = $oauth_user->idnumber;
```

#### Available eFaas data fields

Different data is associated with different scopes. By default, all scopes are included, so you should be able to get
all the data fields.

##### Scope: efaas.openid

 Field     | Type     | Description                          | Example                                
-----------|----------|--------------------------------------|----------------------------------------
 **`sub`** | `string` | Unique user key assigned to the user | `178dedf2-581b-4b48-9d73-770f302751dc` 

##### Scope: efaas.profile

 Field                       | Type     | Description                                                                                                                                                        | Example                                      
-----------------------------|----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------|----------------------------------------------
 **`first_name`**            | `string` | First name of the user                                                                                                                                             | `Mariyam`                                    
 **`middle_name`**           | `string` | Middle name of the user                                                                                                                                            | `Ahmed`                                      
 **`last_name`**             | `string` | Last name of the user                                                                                                                                              | `Rasheed`                                    
 **`first_name_dhivehi`**    | `string` | First name of the user in Dhivehi (Maldivians only)                                                                                                                | `މަރިޔަމް`                                   
 **`middle_name_dhivehi`**   | `string` | Middle name of the user in Dhivehi (Maldivians only)                                                                                                               | `އަހުމަދު`                                   
 **`last_name_dhivehi`**     | `string` | Last name of the user in dhivehi (Maldivians only)                                                                                                                 | `ރަޝީދު`                                     
 **`gender`**                | `string` | Gender of the user                                                                                                                                                 | `M / F`                                      
 **`idnumber`**              | `string` | Identification number of the user<br>- National ID number for Maldivians<br>- Work permit number for work permit holders<br>- Passport number for other foreigners | `A000111 / WP941123 / LA110011`              
 **`verified`**              | `bool`   | Indicates if the user is verified                                                                                                                                  | `True / False`                               
 **`verification_type`**     | `string` | Type of verification taken by the user                                                                                                                             | `biometric / in-person / NA`                 
 **`last_verified_date`**    | `Carbon` | The last date when the user was verified either using biometrics or by visiting an eFaas verification counter.                                                     | `6/26/2019 9:18:11 AM`                       
 **`user_type_description`** | `string` | Indicates the type of user                                                                                                                                         | `Maldivian / Work Permit Holder / Foreigner` 
 **`updated_at`**            | `Carbon` | The last date when the user information was updated                                                                                                                | `6/15/2023 2:12:38 PM`                       

##### Scope: efaas.email

 Field       | Type     | Description       | Example               
-------------|----------|-------------------|-----------------------
 **`email`** | `string` | Email of the user | `ahmed_ali@gmail.com` 

##### Scope: efaas.mobile

 Field                      | Type     | Description                           | Example   
----------------------------|----------|---------------------------------------|-----------
 **`mobile`**               | `string` | Mobile number of the user             | `9074512` 
 **`country_dialing_code`** | `string` | Dialing code of the registered number | `+960`    

##### Scope: efaas.birthdate

 Field           | Type     | Description               | Example      
-----------------|----------|---------------------------|--------------
 **`birthdate`** | `string` | Date of birth of the user | `12/20/1990` 

##### Scope: efaas.photo

 Field       | Type     | Description       | Example                                
-------------|----------|-------------------|----------------------------------------
 **`photo`** | `string` | Photo of the user | `https://efaas-api egov.mv/user/photo` 

##### Scope: efaas.work_permit_status

 Field                      | Type   | Description                                                                              | Example        
----------------------------|--------|------------------------------------------------------------------------------------------|----------------
 **`is_workpermit_active`** | `bool` | Boolean indicating if the work permit is active (only applicable to work permit holders) | `true / false` 

##### Scope: efaas.passport_number

 Field                 | Type     | Description                 | Example    
-----------------------|----------|-----------------------------|------------
 **`passport_number`** | `string` | Passport number of the user | `LA110011` 

##### Scope: efaas.country

 Field                      | Type     | Description                     | Example    
----------------------------|----------|---------------------------------|------------
 **`country_name`**         | `string` | Name of the country of the user | `Maldives` 
 **`country_code`**         | `int`    | ISO 3-digit code                | `462`      
 **`country_code_alpha3`**  | `string` | ISO alpha3 code                 | `MDV`      
 **`country_dialing_code`** | `string` | Dialing code of the country     | `+960`     

##### Scope: efaas.permanent_address

 Field                   | Type           | Description                   | Example       
-------------------------|----------------|-------------------------------|---------------
 **`permanent_address`** | `EfaasAddress` | Permanent address of the user | `Given below` 

Here are the fields of the `EfaasAddress` object:

 Field                           | Type     | Example       
---------------------------------|----------|---------------
 **`AddressLine1`**              | `string` | `Blue Light`  
 **`AddressLine2`**              | `string` | ``            
 **`Road`**                      | `string` | `Road Name`   
 **`AtollAbbreviation`**         | `string` | `K`           
 **`AtollAbbreviationDhivehi`**  | `string` | `ކ`           
 **`IslandName`**                | `string` | `Male`        
 **`IslandNameDhivehi`**         | `string` | `މާލެ`        
 **`HomeNameDhivehi`**           | `string` | `ބުލޭ ލައިޓް` 
 **`Ward`**                      | `string` | `Maafannu`    
 **`WardAbbreviationEnglish`**   | `string` | `M`           
 **`WardAbbreviationDhivehi`**   | `string` | `މ`           
 **`Country`**                   | `string` | `Maldives`    
 **`CountryISOThreeDigitCode`**  | `string` | `462`         
 **`CountryISOThreeLetterCode`** | `string` | `MDV`         

The `EfaasAddress` class also has the following methods:

```php
$permanent_address = $efaas_user->permanent_address;

$permanent_address->getFormattedAddress(); // Get the address with the ward abbreviation. eg: M. Blue Light
$permanent_address->getDhivehiFormattedAddress(); // Get the address in Dhivehi with the ward abbreviation. eg: މ. ބުލޫ ލައިޓް
```

## Testing

You can run the tests with

``` bash
./vendor/bin/phpunit
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email info@javaabu.com instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.org)
- [Mohamed Jailam](http://github.com/muhammedjailam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
