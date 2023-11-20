---
title: Installation & Setup
pagination_next: efaas-socialite/basic-usage/getting-started
---

# Installation
You can install the package via composer:

```bash
composer require javaabu/Efaas-Socialite
```

Laravel 5.5 and above uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

After updating composer, add the ServiceProvider to the providers array in `config/app.php`

```php
Javaabu\EfaasSocialite\Providers\EfaasSocialiteServiceProvider::class,
```

# Configuration
Add configuration to your project's `config/services.php` file.
```php
'efaas' => [    
    'client_id'     => env('EFAAS_CLIENT_ID'),  
    'client_secret' => env('EFAAS_CLIENT_SECRET'),  
    'redirect'      => env('EFAAS_REDIRECT_URI'),
    'mode'          => env('EFAAS_MODE', 'development'), // supports production, development            
],
```

:::warning

Make sure to add the above environment variables to your `.env` file

:::
