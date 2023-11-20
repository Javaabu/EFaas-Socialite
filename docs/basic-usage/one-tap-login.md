This package will automatically add an `/efaas-one-tap-login` endpoint to your web routes which will redirect to eFaas with the eFaas login code. Here is the route that it will register:
```php
Route::middleware(['web', 'guest'])->group(function () {
    Route::get('/efaas-one-tap-login', [
        'uses' => 'EfaasOneTapLoginController@efaasOneTapLogin',
        'as'   => 'one-tap-login',
    ]);
});
```

Sometimes you may wish to customize the routes defined by the Efaas Provider. To achieve this, you first need to ignore the routes registered by Efaas Provider by adding `EfaasProvider::ignoreRoutes()` to the register method of your application's `AppServiceProvider`:

```php
use Javaabu\EfaasSocialite\EfaasProvider;

public function register(): void
{
    EfaasProvider::ignoreRoutes();
}
```

Then, you may copy the routes defined by Efaas Provider in its routes file to your application's `routes/web.php` file and modify them to your liking:

```php
Route::group([
    'as' => 'efaas.',
    'namespace' => '\Javaabu\EfaasSocialite\Http\Controllers',
], function () {
    // Efaas routes...
});
```
