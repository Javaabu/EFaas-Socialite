---
title: Authenticating From Mobile Apps
description: Our take on authenticating via Efaas on mobile apps.
sidebar_position: 2
---

To authenticate users from mobile apps, redirect to the eFaas login screen through a Web View on the mobile app. Then intercept the `code` (authorization code) from eFaas after they redirect you back to your website after logging in to eFaas.

Once your mobile app receives the auth code, send the code to your API endpoint. You can then get the eFaas user details from your server side using the auth code as follows:

```php
$efaas_user = Socialite::driver('efaas')->userFromCode($code);
```

After you receive the eFaas user, you can then issue your own access token or API key according to whatever authentication scheme you use for your API.