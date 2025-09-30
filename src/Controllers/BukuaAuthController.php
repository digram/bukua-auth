<?php

namespace BukuaAuth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

use BukuaAuth\Events\BukuaUserLoggedInEvent;
use BukuaAuth\Traits\HasHmac;

class BukuaAuthController extends Controller
{
    protected string $baseUrl;
    protected string $userAppUrl;
    use HasHmac;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.bukua_auth.base_url'), '/');
        $this->userAppUrl = rtrim(config('services.bukua_auth.app_url'), '/');
    }

    public function authorize(Request $request)
    {
        $dataStr = str()->random(10);
        $signature = $this->generateHmac($dataStr);
        $state   = urlencode($dataStr . '|' . $signature);

        $query = http_build_query([
            'client_id'     => config('services.bukua_auth.client_id'),
            'redirect_uri'  => $this->userAppUrl . '/bukua-auth/callback',
            'response_type' => 'code',
            'state'         => $state,
        ]);

        $redirectUrl = $this->baseUrl . '/oauth/authorize?' . $query;

        // check if the request is from Inertia (AJAX request)
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', $redirectUrl);
        } else {
            // regular HTTP redirect for non-Inertia requests
            return redirect($redirectUrl);
        }
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        $state = urldecode($request->input('state'));
        $stateParts = explode('|', $state, 2);

        if (count($stateParts) !== 2 || !$this->verifyHmac($stateParts[0], $stateParts[1])) {
            abort(403, 'Invalid state parameter');
        }

        try {
            // request the personal access token
            $tokenResponse = Http::asForm()->post($this->baseUrl . '/api/v1/bukua-auth/personal-token', [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.bukua_auth.client_id'),
                'client_secret' => config('services.bukua_auth.client_secret'),
                'redirect_uri'  => $this->userAppUrl . '/bukua-auth/callback',
                'code'          => $request->input('code'),
            ]);

            if ($tokenResponse->failed()) {
                return response()->json(['error' => 'Token request failed'], 400);
            }

            $tokenData = $tokenResponse->json();
            if (!isset($tokenData['access_token'])) {
                return response()->json(['error' => 'No access token received'], 400);
            }

            // fetch basic user profile
            $accountResponse = Http::withToken($tokenData['access_token'])->get($this->baseUrl . '/api/v1/me');

            if ($accountResponse->failed()) {
                return response()->json(['error' => 'Failed to fetch user data'], 400);
            }

            $account = $accountResponse->json()['response'] ?? [];
            if (!isset($account['user'])) {
                return response()->json(['error' => 'No user found'], 400);
            }

            $userModel = config('services.bukua_auth.user_model');

            $userData = [
                'bukua_access_token'  => Crypt::encryptString($tokenData['access_token']),
                'bukua_refresh_token' => Crypt::encryptString($tokenData['refresh_token']),
                'name'                => $account['user']['first_name'] . ' ' . $account['user']['last_name'],
            ];

            $user = $userModel::updateOrCreate(
                ['bukua_user_id' => $account['user']['uid']],
                $userData
            );

            Auth::guard('web')->login($user);

            event(new BukuaUserLoggedInEvent($user));

            $redirectPath = ltrim(config('services.bukua_auth.redirect_after_login', '/'), '/');
            $redirectUrl = $this->userAppUrl . '/' . $redirectPath;

            return Inertia::location($redirectUrl);

            // return redirect()->intended(
            //     config('services.bukua_auth.redirect_after_login', '/')
            // );
        } catch (\Exception $e) {
            Log::error('Bukua auth callback error: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during authentication'], 500);
        }
    }
}
