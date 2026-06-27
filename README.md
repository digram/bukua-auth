# Login with Bukua Laravel Integration

This package provides seamless **OAuth 2.0 authentication** with Bukua for Laravel applications, handling the complete authentication flow and user management.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
  - [Environment Variables](#environment-variables)
  - [Database Setup](#database-setup)
  - [User Model Configuration](#user-model-configuration)
  - [CORS Configuration](#cors-configuration)
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
   - Register as an app developer at [Bukua Platform - Development Environment](https://bukua-core.apptempest.com/login) or [Bukua Platform - Production Environment](https://app.bukuaplatform.com/login)
   - Create a **User Access App** in the selected environment above

2. **Application Credentials**
   - Obtain your `client_id`, `client_secret` and `app_url` from the Bukua Developer Dashboard

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
   # For development
   php artisan config:clear && php artisan route:clear

   # For production
   php artisan config:cache && php artisan route:cache
   ```

## Configuration

### Environment Variables

Add the following variables to your `.env` file:

```bash
# Bukua OAuth Configuration
BUKUA_USER_ACCESS_CLIENT_ID=your-client-id-here
BUKUA_USER_ACCESS_CLIENT_SECRET=your-client-secret-here
BUKUA_USER_ACCESS_APP_URL="https://your-app-url.com"
BUKUA_BASE_URL="https://bukua-core.apptempest.com"  # Development
# BUKUA_BASE_URL="https://app.bukuaplatform.com"    # Production

# Application Settings
BUKUA_USER_MODEL="App\\Models\\User"
BUKUA_REDIRECT_AFTER_LOGIN="/dashboard" # Your authenticated user dashboard URL
```

**Configuration Notes:**

- **Environment**: Use the development base URL for testing and production URL for live applications
- **User Access App URL**: Must exactly match the App URL from your Bukua Developer Dashboard
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
}
```

### CORS Configuration

To handle cross-origin requests properly, configure your Laravel CORS settings in `config/cors.php`:

If your Laravel application doesn't have the CORS configuration file, generate it using:

```bash
php artisan config:publish cors
```

This will create the `config/cors.php` file if it doesn't already exist.

Update your `config/cors.php` file with the following settings:

```php
return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'bukua-auth/callback', // Bukua OAuth callback
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://bukua-core.apptempest.com', // Bukua development environment
        'https://app.bukuaplatform.com',     // Bukua production environment
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-Inertia-Location', // Bukua OAuth redirects
        'X-Inertia',          // Bukua OAuth responses
    ],

    'max_age' => 0,

    'supports_credentials' => false,
];
```

**CORS Configuration Notes:**

- Ensure the `paths` array includes `'bukua-auth/callback'` to handle OAuth callbacks
- Add both Bukua domains to `allowed_origins` for proper cross-origin requests
- Include `'X-Inertia-Location'` and `'X-Inertia'` in `exposed_headers` for OAuth redirection

## Usage

### Login Button Implementation

**Blade Templates:**

```html
<!-- resources/views/auth/login.blade.php -->
@if (Route::has('bukua-auth.authorize'))
<form action="{{ route('bukua-auth.authorize') }}" method="POST">
  @csrf
  <button type="submit" class="btn btn-primary">Login with Bukua</button>
</form>
@endif
```

**Inertia.js with React/Vue:**

```jsx
// For React components
import { Link } from "@inertiajs/react";

function LoginButton() {
  return (
    <Link
      method="post"
      href={route("bukua-auth.authorize")}
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

| Route Name             | URL                | Method | Purpose                |
| ---------------------- | ------------------ | ------ | ---------------------- |
| `bukua-auth.authorize` | `/bukua/authorize` | POST   | Initiates OAuth flow   |
| `bukua-auth.callback`  | `/bukua/callback`  | GET    | Handles OAuth callback |

## Events

The package dispatches events that you can listen for to extend functionality:

### Available Events

- `BukuaUserLoggedInEvent`: Dispatched when a user successfully logs in

### Event Listener Setup

Create an example listener in Laravel using:

**Name**: `HandleBukuaUserLoggedIn`
**Event**: `\BukuaAuth\Events\BukuaUserLoggedInEvent`:

```bash
php artisan make:listener
```

**Example listener implementation:**

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

        // Log the event
        Log::info('Bukua user logged in', [
            'bukua_user_id' => $user->bukua_user_id,
            'timestamp' => now(),
        ]);

        try {
            // Fetch the authenticated user's Bukua profile
            $profile = BukuaAuth::me();
            $data = $profile['response'];

               $firstName = $data['user']['first_name'];
               $workspaceName = $data['context']['name'];
               $workspaceUid = $data['context']['uid'];
               $appRoles = $data['app']['roles'] ?? [];

               // Run your business logic ...
        } catch (\Exception $e) {
            Log::error('Failed to fetch user data from Bukua', [
                'error' => $e->getMessage(),
                'bukua_user_id' => $user->bukua_user_id,
            ]);
        }
    }
}
```

## API Methods

The package provides several methods to interact with Bukua's API. All responses use Bukua's standard envelope:

```json
{
  "code": 200,
  "error": null,
  "response": {}
}
```

Always read data from the `response` key. Errors appear in `error` with a non-200 `code`.

### Basic User Profile

```php
use BukuaAuth\Facades\BukuaAuth;

try {
    $payload = BukuaAuth::me();
    $profile = $payload['response'];

    print_r($profile);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

#### Response shape

The profile reflects the user's **active workspace** on Bukua (school or organization tenant) and the **calling app's install state and roles** within that workspace.

```json
{
  "user": {
    "uid": "01932f1a-…",
    "first_name": "Jane",
    "last_name": "Rose",
    "email": "jane@example.com",
    "avatar": { "thumb": "https://…", "medium": "https://…" },
    "phone_number": "+254712345678"
  },
  "context": {
    "kind": "school",
    "uid": "01932f1a-…",
    "name": "Jitahidi Secondary School",
    "logo": "https://…",
    "organization": {
      "uid": "01932f1a-…",
      "type": {
        "uid": "01932f1a-…",
        "name": "School",
        "abbreviation": "SCH"
      }
    }
  },
  "app": {
    "roles": ["Teacher", "Class Admin"],
    "status": "active",
    "expires_at": null,
    "trial_ends_at": "2026-07-15T00:00:00+00:00"
  },
  "enrolment_number": "ADM/2024/001",
  "is_verified": true
}
```

When the access token is not from a User Access App OAuth client, `app` is `null`.

#### Field reference

| Field                  | Description                                                                                                                               |
| ---------------------- | ----------------------------------------------------------------------------------------------------------------------------------------- |
| `user`                 | Core identity fields for the logged-in Bukua user.                                                                                        |
| `user.phone_number`    | Only present when the token includes the `phones:view:own` scope. Otherwise `null`.                                                       |
| `context`              | The user's currently active workspace.                                                                                                    |
| `context.kind`         | `"school"` or `"organization"`.                                                                                                           |
| `context.uid`          | UID of the active workspace entity (school UID when `kind` is `school`, organization UID when `kind` is `organization`).                  |
| `context.name`         | Display name of the active workspace.                                                                                                     |
| `context.logo`         | Logo URL for the workspace, or `null`.                                                                                                    |
| `context.organization` | Parent tenant. For school workspaces this is the owning organization; for organization workspaces it is the organization itself.          |
| `app`                  | Calling User Access App in the active workspace, or `null` when the token is not from an app OAuth client.                                |
| `app.roles`            | Role names this user holds **in your app only** (e.g. `["Teacher"]`, `["Teacher", "Class Admin"]`, or `[]`).                              |
| `app.status`           | Organization install status for your app: `active`, `trialling`, `suspended`, `expired`, `uninstalled`, or `null` if not installed.     |
| `app.expires_at`       | Subscription expiry (ISO 8601), or `null`.                                                                                                 |
| `app.trial_ends_at`    | Trial end date (ISO 8601), or `null`.                                                                                                     |
| `enrolment_number`     | Admission or membership number in the active workspace, when applicable.                                                                  |
| `is_verified`          | Whether the user's enrolment in the active workspace is verified.                                                                         |

#### Common integration patterns

**Check install status before granting access**

Only `active` and `trialling` installs should receive full access. Other statuses mean the organization no longer has access to your app:

```php
$profile = BukuaAuth::me()['response'];
$app = $profile['app'] ?? null;

if ($app === null) {
    // Token is not from a User Access App client
}

$status = $app['status'] ?? null;

if (! in_array($status, ['active', 'trialling'], true)) {
    // Handle suspended, expired, uninstalled, or not installed
}
```

**Read the user's roles in your app**

`app.roles` is a flat array of role name strings, already scoped to your app:

```php
$profile = BukuaAuth::me()['response'];

$appRoles = $profile['app']['roles'] ?? []; // e.g. ["Teacher"] or ["Teacher", "Class Admin"]

if (in_array('Teacher', $appRoles, true)) {
    // ...
}
```

An empty `roles` array can mean the user has no assigned roles, or that the install is not accessible (`suspended`, `expired`, etc.). Check `app.status` to tell the difference.

**Resolve the active school**

When `context.kind` is `"school"`, the school is the workspace itself:

```php
$context = $profile['context'];

if ($context['kind'] === 'school') {
    $schoolUid = $context['uid'];
    $schoolName = $context['name'];
    $organizationUid = $context['organization']['uid'];
}
```

When `context.kind` is `"organization"`, the user is working at the organization level (no single school selected).

#### Required OAuth scopes

| Endpoint           | Scope                        |
| ------------------ | ---------------------------- |
| `me`               | `profiles:view:own`          |
| Phone number field | `phones:view:own` (optional) |

#### Error responses

| HTTP code | Meaning                                                                                           |
| --------- | ------------------------------------------------------------------------------------------------- |
| `401`     | Missing or invalid access token.                                                                  |
| `403`     | Token is valid but lacks `profiles:view:own`.                                                     |
| `404`     | User has no active workspace on Bukua. Prompt them to select or join a school/organization first. |

### User Subjects

Only available when the user's active workspace is a **school**. Requires the `subjects:view:own` scope.

```php
try {
    $payload = BukuaAuth::subjects();
    $subjects = $payload['response']['subjects'];

    print_r($subjects);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

Example subject object:

```json
{
  "uid": "01932f1a-…",
  "name": "Mathematics",
  "school_level": {
    "uid": "01932f1a-…",
    "name": "Form 3"
  },
  "role": {
    "uid": "01932f1a-…",
    "name": "Teacher"
  }
}
```

Returns `404` when the active workspace is not a school or the user has no enrolled subjects.

## Troubleshooting

### Common Issues

1. **"Invalid redirect_uri" error**
   - Ensure `BUKUA_USER_ACCESS_APP_URL` matches exactly with your Bukua app credentials
   - App URL must use **HTTPS** and be accessible

2. **"Client authentication failed" error**
   - Verify `BUKUA_USER_ACCESS_CLIENT_ID` and `BUKUA_USER_ACCESS_CLIENT_SECRET` are correct
   - Check for extra spaces in environment variables

3. **User model not found**
   - Verify `BUKUA_USER_MODEL` points to the correct namespace
   - Ensure the User model exists and is accessible

4. **User creation errors**
   - Check if users table already has the required columns
   - Ensure all existing fields in the users table are nullable as specified

5. **CORS issues**
   - Verify `bukua-auth/callback` is added to CORS paths
   - Ensure Bukua domains are in allowed_origins
   - Check that X-Inertia headers are exposed

## Support

- **Support Email**: hello@bukuaplatform.com
- **Issue Tracking**: [GitHub Issues](https://github.com/digram/bukua-auth/issues)
