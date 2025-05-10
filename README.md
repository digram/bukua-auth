# Login with Bukua OAuth for Laravel  

This package simplifies **OAuth authentication** using Bukua in Laravel applications.

## Prerequisites  

**Bukua Developer Account**:  
   - Create a **User Access Client** in the [Bukua Developer Dashboard](https://developer.bukuaplatform.com/).  
   - Obtain your:  
     - `client_id`  
     - `client_secret`  

## Configuration  

Add these variables to your `.env` file:  

```bash
BUKUA_USER_ACCESS_CLIENT_ID=your-client-id
BUKUA_USER_ACCESS_CLIENT_SECRET=your-client-secret

BUKUA_USER_ACCESS_CALLBACK_URL="http://your-app-url/bukua-auth/callback"
BUKUA_BASE_URL="https://bukua-core.apptempest.com/"

BUKUA_USER_MODEL="App\\Models\\User"  # Your User model path
BUKUA_REDIRECT_AFTER_LOGIN="/dashboard"  # Route after successful login
```

### Key Notes:  
✅ **`BUKUA_USER_MODEL`**: Ensure this matches your application’s `User` model location.  
✅ **Callback URL**: Must be registered when creating a user access client in the developer dashboard.

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
            'user_id' => $user->uid,
            'email' => $user->email,
            'timestamp' => now(),
        ]);
    }
}
```

**Example Use Cases:**  
- Make further api calls, e.g, to fetch user subjects.  
- Log logins.