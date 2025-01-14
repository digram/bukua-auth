### Introduction

This package enables you to implement `Login with Bukua` in your Laravel project.

### Configuration

At the root of your project, add the following to your `.env` file:

`BUKUA_USER_ACCESS_CLIENT_ID=your-client-id`<br>
`BUKUA_USER_ACCESS_CLIENT_SECRET=your-client_secret`<br>
`BUKUA_USER_ACCESS_CALLBACK_URL=http://your-app-url/bukua-auth/callback`<br>
`BUKUA_BASE_URL=https://bukua-core.apptempest.com/`<br>
`BUKUA_USER_MODEL="App\\Models\\User"`<br>

#### User model configuration

Update the `fillable` property of your User model to ensure it includes the following values:

`bukua_user_id`<br>
`bukua_access_token`<br>
`bukua_refresh_token`<br>
`first_name`<br>
`last_name`<br>
`email`<br>

#### Users table configuration

Update your `users` table to ensure it includes the following fields:

`bukua_user_id` : char(36) NULL<br>
`bukua_access_token` : text NULL<br>
`bukua_refresh_token` : text NULL<br>
`first_name` : varchar(255) NULL<br>
`last_name` : varchar(255) NULL<br>
`email` : varchar(255) NULL<br>

### Installation

1. Download this repository to your computer.

2. Create a folder in your Laravel project called `packages`.

3. Copy the downloaded folder `bukua-auth` into the `packages` folder of your Laravel project.

4. Add the following to your `composer.json` file and save it:

```json
    "repositories": [
       {
        "type": "path",
        "url": "packages/bukua-auth"
        }
    ]
```

5. In your terminal, run `composer require rango-tech/bukua-auth`.

6. Clear your configuration cache by running `php artisan cache:clear`.

7. To display the `Login with Bukua` button, add the following anywhere in your Blade file:

```php

            <!-- Login with Bukua-->
            @if (Route::has('bukua-auth.authorize'))
            <form action="{{ route('bukua-auth.authorize') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">Login with Bukua</button>
            </form>
            @endif

```

When the button is clicked, the user should be redirected to the auth server for authentication and authorization, and then returned to your application as a logged in user.

You can view the logic for logging in a user to your application in the `packages/bukua-auth/src/Controllers/BukuaAuthController@callback` controller method.