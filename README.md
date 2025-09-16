# Login with Bukua OAuth for Laravel  

This package simplifies **OAuth authentication** using Bukua in Laravel applications.

## Prerequisites  

**Bukua Developer Account**:  
   - Create a **User Access App** in the [Bukua Developer Dashboard](https://www.bukuaplatform.com/login).  
   - Obtain your:  
     - `client_id`  
     - `client_secret`  

## Configuration  

Add these variables to your `.env` file:  

```bash
# The client ID and seecret for your developer dashboard
BUKUA_USER_ACCESS_CLIENT_ID=your-client-id
BUKUA_USER_ACCESS_CLIENT_SECRET=your-client-secret

# The URL you configured while creating this app
BUKUA_USER_ACCESS_APP_URL="http://your-app-url/"

# The API endpoint foe the auth server
# development: https://bukua-core.apptempest.com/
# production: https://app.bukuaplatform.com/
BUKUA_BASE_URL="https://bukua-core.apptempest.com/"

# Your User model class. This is the default namespace for laravel.
BUKUA_USER_MODEL="App\\Models\\User"  

# Where to redirect the user after successful login. Leave as null to implement your own redirect logic using events documented below.
BUKUA_REDIRECT_AFTER_LOGIN="/dashboard"  
```

### Key Notes:  
✅ **`BUKUA_USER_MODEL`**: Ensure this matches your application’s `User` model location.  
✅ **App URL**: Must be registered when creating a `user access app` in the developer dashboard.

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

Update your `users` table migration to ensure it includes the new fields. Ensure all fields in your users table are nullable to prevent errors when adding a new user.

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

1. In your terminal, run 

```bash
composer require digram/bukua-auth
```

2. Clear your configuration cache by running

```bash
php artisan cache:clear
```

### Adding the Login Button  

To implement the **"Login with Bukua"** button in your Blade template:  

```html
<!-- Login with Bukua Button -->
@if (Route::has('bukua-auth.authorize'))
    <form action="{{ route('bukua-auth.authorize') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            Login with Bukua
        </button>
    </form>
@endif
```

If using Laravel with Inertia:
```html
<!-- Login with Bukua Button -->
<Link className="block w-full cursor-pointer" method="post" href={route('bukua-auth.authorize')} as="button">
    Login with Bukua
</Link>
```

### Events (Optional Customization)  

Listen for the `BukuaUserLoggedInEvent` event to extend functionality:  

```php
// In your EventServiceProvider.php
protected $listen = [
    \BukuaAuth\Events\BukuaUserLoggedInEvent::class => [
        \App\Listeners\HandleBukuaUserLoggedIn::class, // Your listener
    ],
];
```

```php
use BukuaAuth\Facades\BukuaAuth;

// \App\Listeners\HandleBukuaUserLoggedIn
class HandleBukuaUserLoggedIn
{
    /**
     * Handle the event.
     *
     * @param  \BukuaAuth\Events\BukuaUserLoggedInEvent  $event
     * @return void
     */
    public function handle(BukuaUserLoggedInEvent $event)
    {
        // Access the user from the event
        $user = $event->user;

        // Write in laravel log
        \Log::info("Bukua user logged in: {$user->uid}", [
            'user_uid' => $user->uid,
            'email' => $user->email,
            'timestamp' => now(),
        ]);

        // Fetch the user's school
        $mySchool = BukuaAuth::school();

        // Redirect the user to your custom dashboard
        return redirect()->route('dashboard.custom', [
            'school_uid' => $mySchool->school->uid,
            'role_uid' => $mySchool->role->uid,
    ]);
    }
}
```

**Example Use Cases:**  
- Make further calls, e.g, to fetch which school the user belongs to.  
- Write to a log file whenever someone logs in from Bukua

## User Information Methods

Methods for fetching information about the currently authenticated user.

### Basic Profile

Retrieves the basic profile information of the logged-in user such as name and email.

```php
use BukuaAuth\Facades\BukuaAuth;

try {
    $me = BukuaAuth::me();
    dd($me);
} catch (\Exception $e) {
    // Handle error
}
```

### Current School

Returns the school where the user belongs to with their role.

```php
use BukuaAuth\Facades\BukuaAuth;

try {
    $mySchool = BukuaAuth::school();
    dd($mySchool);
} catch (\Exception $e) {
    // Handle error
}
```

### User Subjects

Fetches the subjects associated with the authenticated user.

```php
use BukuaAuth\Facades\BukuaAuth;

try {
    $mySubjects = BukuaAuth::subjects();
    dd($mySubjects);
} catch (\Exception $e) {
    // Handle error
}
```