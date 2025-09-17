# Laravel Bukua OAuth Integration

This package provides seamless **OAuth 2.0 authentication** with Bukua for Laravel applications, handling the complete authentication flow and user management.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Database Setup](#database-setup)
  - [User Model Configuration](#user-model-configuration)
- [Usage](#usage)
  - [Login Button Implementation](#login-button-implementation)
  - [Authentication Routes](#authentication-routes)
- [Events](#events)
- [API Methods](#api-methods)
- [Troubleshooting](#troubleshooting)
- [Support](#support)

## Prerequisites

Before using this package, ensure you have:

1. **Bukua Developer Account**
   - Register as an app developer at [Bukua Platform](https://www.bukuaplatform.com/login)
   - Create a **User Access App** in the [Developer Dashboard](https://www.bukuaplatform.com/dashboard)

2. **Application Credentials**
   - Obtain your `client_id` and `client_secret` from the Bukua Developer Dashboard

3. **Laravel Application**
   - Laravel 8.x or higher
   - Composer for dependency management

## Installation

1. **Install the package via Composer:**
   ```bash
   composer require digram/bukua-auth
   ```

2. **Clear configuration cache:**
   ```bash
   php artisan config:clear && php artisan route:clear
   ```

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```bash
# Bukua OAuth Configuration
BUKUA_USER_ACCESS_CLIENT_ID=your-client-id-here
BUKUA_USER_ACCESS_CLIENT_SECRET=your-client-secret-here
BUKUA_USER_ACCESS_APP_URL="https://your-app-url.com/"
BUKUA_BASE_URL="https://bukua-core.apptempest.com/"  # Development
# BUKUA_BASE_URL="https://app.bukuaplatform.com/"    # Production

# Application Settings
BUKUA_USER_MODEL="App\\Models\\User"
BUKUA_REDIRECT_AFTER_LOGIN="/dashboard"
```

**Configuration Notes:**
- **Environment**: Use the development base URL for testing and production URL for live applications
- **User Access App URL**: Must exactly match the App URL registered in your Bukua Developer Dashboard
- **User Model**: Ensure this matches your application's User model namespace

### Database Setup

1. **Update your User migration:**
   ```php
   Schema::table('users', function ($table) {
       $table->char('bukua_user_id', 36)->nullable()->index();
       $table->text('bukua_access_token')->nullable();
       $table->text('bukua_refresh_token')->nullable();
       $table->string('name')->nullable();
       // Consider adding index for better performance
       $table->index(['bukua_user_id']);
   });
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate
   ```

### User Model Configuration

Update your User model to include the Bukua fields:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bukua_user_id',
        'bukua_access_token',
        'bukua_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'bukua_access_token',
        'bukua_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

## Usage

### Login Button Implementation

**Blade Templates:**
```html
<!-- resources/views/auth/login.blade.php -->
@if (Route::has('bukua-auth.authorize'))
    <form action="{{ route('bukua-auth.authorize') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            Login with Bukua
        </button>
    </form>
@endif
```

**Inertia.js with React/Vue:**
```jsx
// For React components
import { Link } from '@inertiajs/react';

function LoginButton() {
    return (
        <Link 
            method="post" 
            href={route('bukua-auth.authorize')}
            as="button"
            className="btn btn-primary"
        >
            Login with Bukua
        </Link>
    );
}
```

### Authentication Routes

The package automatically registers the following routes:

| Route Name | URL | Method | Purpose |
|------------|-----|--------|---------|
| `bukua-auth.authorize` | `/bukua/authorize` | POST | Initiates OAuth flow |
| `bukua-auth.callback` | `/bukua/callback` | GET | Handles OAuth callback |

## Events

The package dispatches events that you can listen for to extend functionality:

### Available Events

- `BukuaUserLoggedInEvent`: Dispatched when a user successfully logs in

### Event Listener Setup

1. **Register listeners in `EventServiceProvider`:**
   ```php
   protected $listen = [
       \BukuaAuth\Events\BukuaUserLoggedInEvent::class => [
           \App\Listeners\HandleBukuaUserLoggedIn::class,
       ],
   ];
   ```

2. **Example listener implementation:**
   ```php
   <?php

   namespace App\Listeners;

   use BukuaAuth\Events\BukuaUserLoggedInEvent;
   use BukuaAuth\Facades\BukuaAuth;
   use Illuminate\Support\Facades\Log;

   class HandleBukuaUserLoggedIn
   {
       /**
        * Handle the event.
        */
       public function handle(BukuaUserLoggedInEvent $event)
       {
           $user = $event->user;
           
           // Log the login event
           Log::info('Bukua user logged in', [
               'user_id' => $user->id,
               'bukua_user_id' => $user->bukua_user_id,
               'timestamp' => now(),
           ]);

           // Fetch additional user data
           try {
               $mySchool = BukuaAuth::school();
               
               // Redirect the user to your custom dashboard
               return redirect()->route('dashboard.custom', [
                    'school_uid' => $mySchool->school->uid,
                    'role_uid' => $mySchool->role->uid,
               ]);               
           } catch (\Exception $e) {
               Log::error('Failed to fetch user data from Bukua', [
                   'error' => $e->getMessage(),
                   'user_id' => $user->id,
               ]);
           }
       }
   }
   ```

## API Methods

The package provides several methods to interact with Bukua's API:

### Basic User Profile

```php
use BukuaAuth\Facades\BukuaAuth;
use Exception;

try {
    $userProfile = BukuaAuth::me();
    
    // Returns: {
    //   "uid": "user_uid",
    //   "first_name": "John",
    //   "last_name": "Doe",
    //   "email": "user@example.com",
    //   ...other profile fields
    // }
    
} catch (Exception $e) {
    // Handle authentication or API errors
    logger()->error('Failed to fetch user profile', ['error' => $e->getMessage()]);
}
```

### Current School Information

```php
try {
    $schoolInfo = BukuaAuth::school();
    
    // Returns: {
    //   "school": {
    //     "uid": "school_uid",
    //     "name": "School Name",
    //   },
    //   "role": {
    //     "uid": "role_uid",
    //     "name": "Role Name"
    //   }
    // }
    
} catch (Exception $e) {
    // Handle errors
}
```

### User Subjects

```php
try {
    $subjects = BukuaAuth::subjects();
    
    // Returns array of subject objects: [
    //   {
    //     "uid": "subject_uid",
    //     "name": "Subject Name",
    //   }
    // ]
    
} catch (Exception $e) {
    // Handle errors
}
```

## Troubleshooting

### Common Issues

1. **"Invalid redirect_uri" error**
   - Ensure `BUKUA_USER_ACCESS_APP_URL` matches exactly with your Bukua app settings
   - Include trailing slash if used in Bukua dashboard
   - App URL must use HTTPS and be accessible

2. **"Client authentication failed" error**
   - Verify `BUKUA_USER_ACCESS_CLIENT_ID` and `BUKUA_USER_ACCESS_CLIENT_SECRET` are correct
   - Check for extra spaces in environment variables

3. **User model not found**
   - Verify `BUKUA_USER_MODEL` points to the correct namespace
   - Ensure the User model exists and is accessible

4. **User creation errors**
   - Check if users table already has the required columns
   - Ensure all existing fields are nullable as specified

## Support

- **Support Email**: hello@bukuaplatform.com
- **Issue Tracking**: [GitHub Issues](https://github.com/digram/bukua-auth/issues)