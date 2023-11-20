---
titie: Changing Efaas Login Prompt Behavior
---

The eFaas login prompt behaviour can be customized by modifying the prompt option on your redirect request.

```php
return Socialite::driver('efaas')->with(['prompt' => 'select_account'])->redirect();
```

The available prompt options are:

 Option                  | Description                                    
------------------------ |----------------------------------------------- 
**`login`**              | Forces the user to enter their credentials on that request, regardless of whether the user is already logged into eFaas.
**`none`**               | Opposite of the `login` option. Ensures that the user isn't presented with any interactive prompt. If the request can't be completed silently by using single-sign on, the Microsoft identity platform returns an interaction_required error.                                     
**`consent`**            | Triggers the OAuth consent dialog after the user signs in, asking the user to grant permissions to the app.
**`select_account`**     | Interrupts the single sign-on, providing account selection experience listing all the accounts either in session or any remembered account or an option to choose to use a different account altogether
