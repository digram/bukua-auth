### Introduction

This package enables you to implement `Login with Bukua` in your Laravel project.

### Configuration

Add the following to your `.env` file:

```bash
BUKUA_USER_ACCESS_CLIENT_ID=your-client-id
BUKUA_USER_ACCESS_CLIENT_SECRET=your-client_secret
BUKUA_USER_ACCESS_CALLBACK_URL=http://your-app-url/bukua-auth/callback
BUKUA_BASE_URL=https://bukua-core.apptempest.com/
BUKUA_USER_MODEL="App\\Models\\User"
```

#### User model configuration

To ensure your `User` model can handle the necessary data, you need to update the `fillable` property to include the following fields:

```php
     protected $fillable = [
          ...
         'bukua_user_id',
         'bukua_access_token',
         'bukua_refresh_token',
         'name',
     ];
```

#### Users table configuration

Update your `users` table migration to ensure it includes the following fields. Ensure the fields are nullable.

 ```php
     Schema::table('users', function ($table) {
         ...
         $table->char('bukua_user_id', 36)->nullable();
         $table->text('bukua_access_token')->nullable();
         $table->text('bukua_refresh_token')->nullable();
         $table->string('name')->nullable();
     });
```

Execute the migration to apply the changes to your database:

```bash
    php artisan migrate
```

### Installation

1. Download this repository to your computer.

2. Create a folder in your Laravel project called `packages`.

3. Copy the downloaded folder `bukua-auth-main` into the `packages` folder of your Laravel project.

4. Add the following to your `composer.json` file and save it:

```json
    "repositories": [
       {
        "type": "path",
        "url": "packages/bukua-auth-main"
        }
    ]
```

5. In your terminal, run 

```bash
composer require rango-tech/bukua-auth
```

6. Clear your configuration cache by running

```bash
php artisan cache:clear
```

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

When the button is clicked, the user should be redirected to the auth server for authorization, and then returned to your application as a logged in user.

You can view the logic for logging in a user to your application in the `packages/bukua-auth/src/Controllers/BukuaAuthController@callback` controller method.