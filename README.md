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
Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider::class;
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

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('efaas')->redirect();
```

### Available Methods

``` php
//TODO
```

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
