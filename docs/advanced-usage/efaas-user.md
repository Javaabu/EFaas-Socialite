---
title: Efaas User
description: Deep dive on the Efaas User Object
sidebar_position: 3
---

## Efaas User Object Attributes
The user object received from Socialite after login contains all the information received from Efaas systems to your application. It will contain all of the following attributes.

### Available eFaas data fields
 Field                   | Description                                    | Example
------------------------ |----------------------------------------------- | ---------------------------------------
**`name`**               | Full Name                                      | `Ahmed Mohamed`
**`given_name`**         | First Name                                     | `Ahmed`
**`middle_name`**        | Middle Name                                    | 
**`family_name`**        | Last Name                                      | `Mohamed`
**`idnumber`**           | ID number in case of maldivian and workpermit number in case of expatriates | `A037420`
**`gender`**             | Gender                                         | `M` or `F`
**`address`**            | Permananet Address. Country will contain an ISO 3 Digit country code. | ```["AddressLine1" => "Light Garden", "AddressLine2" => "", "Road" => "", "AtollAbbreviation" => "K", "IslandName" => "Male", "HomeNameDhivehi" => "ލައިޓްގާރޑްން", "Ward" => "Maafannu", "Country" => "462"]```
**`phone_number`**       | Registered phone number                        | `9939900`
**`email`**              | Email address                                  | `ahmed@example.com`
**`fname_dhivehi`**      | First name in Dhivehi                          | `އަހުމަދު`
**`mname_dhivehi`**      | Middle name in Dhivehi                         |
**`lname_dhivehi`**      | Last name in Dhivehi                           | `މުހައްމަދު`
**`user_type`**          | User types <ul><li>1. Maldivian</li> <li>2. Work Permit Holder</li> <li>3. Foreigners</li></ul> | 1
**`user_type_desc`**     | Description of the user type                   | `Maldivian`
**`verification_level`** | Verification level of the user in efaas: <ul><li>100: Not Verified</li>  <li>150: Verified by calling</li>  <li>200: Mobile Phone registered in the name of User</li>  <li>250: Verified in person (Limited)</li>  <li>300: Verified in person</li></ul> | `300`
**`verification_level_desc`**     | Description of the verification level | `Verified in person`
**`user_state`**          | User's state <ul><li>2- Pending Verification</li>  <li>3- Active</li></ul> | `3`
**`user_state_desc`**     | Description of user's state                   | `Active`
**`birthdate`**           | Date of birth. (Carbon instance)              | `10/28/1987`
**`is_workpermit_active`** | Is the work permit active                    | `false`
**`passport_number`**     | Passport number of the individual (expat and foreigners only) | 
**`updated_at`**          | Information Last Updated date. (Carbon instance) | `10/28/2017`  



### Checking the nationality of an Efaas User
We provide a helper method that can determine if an Efaas user is a Maldivian or not. Simply call on the `isMaldivian()` method of the user object retrieved from the Socialite driver.

```php
// Check if user is a Maldivian 
$efaas_user->isMaldivian();
```

### Getting the Dhivehi fill name of an Efaas user.
There is another helper method that can give you the full Dhivehi name of the logged in user. Simply call the `getDhivehiName()` method for the user object.

```php
$dhivehi_name = $efaas_user->getDhivehiName();
```

