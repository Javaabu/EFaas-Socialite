# Upgrade Guide

## Upgrading to 4.0 from 3.x

### Summary

Version 4 improves logout behavior to ensure users are **fully logged out**, even when “remember me” is enabled.

Previously, back-channel logout only destroyed the session, but did **not rotate the remember token**, causing users to be automatically logged back in. This has now been fixed.

---

### What Changed

- **Guard support added to logout methods**
  - `logOut(?string $guard = null)`
  - `logoutSessions(string $sid, ?string $guard = null)`

- **New method added to session handler**
  - `findUserIdByLaravelSessionId(string $laravel_session_id): ?string`  
  Used internally to retrieve the user and rotate the remember token.

- **New config option**
  ```php
  'session_guard' => null,
  ```
  Defines which auth guard is used during logout.

- **Remember token is now rotated on logout**
  - Prevents unintended automatic re-login after back-channel logout

---

### Upgrade Impact

✅ **No action required in most cases**

The package should work out of the box after upgrading.

---

### When You Need to Update Code

Only if you have custom implementations:

- **Custom `EfaasSessionHandler`**
  - Must implement:
    ```php
    findUserIdByLaravelSessionId(string $laravel_session_id): ?string
    ```

- **Custom `EfaasSession` model**
  - Must support:
    ```php
    logOut(?string $guard = null)
    ```

---

### Optional

If you use a non-default auth guard, set:

```php
'session_guard' => 'web',
```

---

That’s it — upgrade and you’re good to go 🚀
