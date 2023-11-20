---
title: Getting Started
description: The bare minimum to get started with Efaas-Socialite
sidebar_position: 1
---

:::info

A demo implementation of this package is available [here](https://github.com/ncit-devs/Efaas-Implementation-Javaabu)

:::

You should now be able to use the provider like you would regularly use Socialite.

## Loggin In

To redirect the user to log in with Efaas, you can call the Socialite driver like this:

```php
Route::get('/auth/redirect', function () {
    return Socialite::driver('efaas')->redirect();
});
```

The user will be taken to Efaas to sign in, and after successfully signing in, they will be redirected to your website's callback endpoint. Getting the user's information from the callback is as simple as calling the `user()` method on the driver.

```php
Route::get('/auth/callback', function () {
    $user = Socialite::driver('efaas')->user();
 
    // $access_token = $user->token
});
```

## Signing Out

In your Laravel app's logout endpoint, use the Socialite driver's `logOut()` method using the access token retrieved during login. Here is an example of a logout sequence:

```php
Route::post('/auth/logout', function () {
    $user = auth()->user();
    $access_token = $user->efaas_access_token;

    return Socialite::driver('efaas')->logOut($access_token, route('login'));
});
```











