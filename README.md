# eFaas Laravel Socialite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/efaas-socialite.svg?style=flat-square)](https://packagist.org/packages/javaabu/efaas-socialite)
[![Build Status](https://img.shields.io/travis/javaabu/efaas-socialite/master.svg?style=flat-square)](https://travis-ci.org/javaabu/efaas-socialite)
[![Quality Score](https://img.shields.io/scrutinizer/g/javaabu/efaas-socialite.svg?style=flat-square)](https://scrutinizer-ci.com/g/javaabu/efaas-socialite)
[![Total Downloads](https://img.shields.io/packagist/dt/javaabu/efaas-socialite.svg?style=flat-square)](https://packagist.org/packages/javaabu/efaas-socialite)

[Laravel Socialite](https://github.com/laravel/socialite) Provider for [eFaas](https://efaas.egov.mv/).

## Installation

You can install the package via composer:

``` bash
composer require javaabu/efaas-socialite
```

**Laravel 5.5** and above uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

After updating composer, add the ServiceProvider to the providers array in config/app.php

``` bash
Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider::class,
```


### Add configuration to `config/services.php`

```php
'efaas' => [    
    'client_id' => env('EFAAS_CLIENT_ID'),  
    'client_secret' => env('EFAAS_CLIENT_SECRET'),  
    'redirect' => env('EFAAS_REDIRECT_URI'),
    'mode' => env('EFAAS_MODE', 'development'), // supports production, development            
],
```

### Usage

**Note:** A demo implementation of this package is available [here](https://github.com/ncit-devs/Efaas-Implementation-Javaabu).

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):
Refer to the [Official Social Docs](https://laravel.com/docs/8.x/socialite#routing) for more info.

```php
return Socialite::driver('efaas')->redirect();
```

and in your callback handler, you can access the user data like so.

```
$efaas_user = Socialite::driver('efaas')->user();
$access_token = $efaas_user->token;
```

#### Logging out the eFaas User

In your Laravel logout redirect, redirect with the provider `logOut()` method using the access token saved during login

``` php
return Socialite::driver('efaas')->logOut($access_token, $post_logout_redirect_url);
```

#### Available Methods for eFaas User

``` php
$efaas_user->isMaldivian();
```

#### Getting eFaas data from eFaas User object

``` php
$id_number = $oauth_user->idnumber;
```

#### Available eFaas data fields
 Field                   | Description                                    | Example
------------------------ |----------------------------------------------- | ---------------------------------------
**`name`**               | Full Name                                      | `Ahmed Mohamed`
**`given_name`**         | First Name                                     | `Ahmed`
**`middle_name`**        | Middle Name                                    | 
**`family_name`**        | Last Name                                      | `Mohamed`
**`idnumber`**           | ID number in case of maldivian and workpermit number in case of expatriates | `A037420`                                     | `Ahmed`
**`gender`**             | Gender                                         | `M` or `F`
**`address`**            | Permananet Address. Country will contain an ISO 3 Digit country code. | ```["AddressLine1" => "Light Garden", "AddressLine2" => "", "Road" => "", "AtollAbbreviation" => "K", "IslandName" => "Male", "HomeNameDhivehi" => "ލައިޓްގާރޑްން", "Ward" => "Maafannu", "country" => "462"]```                                     | `Ahmed`
**`phone_number`**       | Registered phone number                        | `9939900`
**`email`**              | Email address                                  | `ahmed@example.com`
**`fname_dhivehi`**      | First name in Dhivehi                          | `އަހުމަދު`
**`mname_dhivehi`**      | Middle name in Dhivehi                         |
**`lname_dhivehi`**      | Last name in Dhivehi                           | `މުހައްމަދު`
**`user_type`**          | User type<br>1- Maldivian<br>2- Work Permit Holder<br>3- Foreigners | 1
**`user_type_desc`**     | Description of the user type                   | `Maldivian`
**`verification_level`** | Verification level of the user in efaas<br>100: Not Verified<br>150: Verified by calling<br>200: Mobile Phone registered in the name of User<br>250: Verified in person (Limited)<br>300: Verified in person | `300`
**`verification_level_desc`**     | Description of the verification level | `Verified in person`
**`user_state`**          | User's state<br>2- Pending Verification<br>3- Active | `3`
**`user_state_desc`**     | Description of user's state                   | `Active`
**`birthdate`**           | Date of birth. (Carbon instance)              | `10/28/1987`
**`is_workpermit_active`** | Is the work permit active                    | `false`
**`passport_number`**     | Passport number of the individual (expat and foreigners only) | 
**`updated_at`**          | Information Last Updated date. (Carbon instance) | `10/28/2017`  

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@javaabu.com instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [Arushad Ahmed (@dash8x)](http://arushad.org)
- [Mohamed Jailam](http://github.com/muhammedjailam)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
