<?php

namespace BukuaAuth\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BukuaAuthController extends Controller
{
    public function authorize(Request $request)
    {
        $request->session()->put('bukua_auth_state', $state = str()->random(40));

        $query = http_build_query([
            'client_id'     => config('services.bukua_auth.user_access_client_id'),
            'redirect_uri'  => config('services.bukua_auth.user_access_callback_url'),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect(config('services.bukua_auth.base_url') . 'oauth/authorize?' . $query);
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        $expectedState = session()->pull('bukua_auth_state');
        if ($expectedState !== $request->input('state')) {
            abort(403, 'Invalid state parameter');
        }

        try {
            // Request the access token
            $tokenResponse = Http::asForm()->post(config('services.bukua_auth.base_url') . 'api/bukua-auth/personal-token', [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.bukua_auth.user_access_client_id'),
                'client_secret' => config('services.bukua_auth.user_access_client_secret'),
                'redirect_uri'  => config('services.bukua_auth.user_access_callback_url'),
                'code'          => $request->input('code'),
            ]);

            if ($tokenResponse->failed()) {
                return response()->json(['error' => 'Token request failed'], 400);
            }

            $tokenData = $tokenResponse->json();
            if (!isset($tokenData['access_token'])) {
                return response()->json(['error' => 'No access token received'], 400);
            }

            // Fetch user data with the access token
            $accountResponse = Http::withHeaders([
                'Authorization' => "Bearer " . $tokenData['access_token']
            ])->get(config('services.bukua_auth.base_url') . 'api/me');

            if ($accountResponse->failed()) {
                return response()->json(['error' => 'Failed to fetch user data'], 400);
            }

            $account = $accountResponse->json();
            if (!isset($account['user'])) {
                return response()->json(['error' => 'No user found'], 400);
            }

            $userModel = config('services.bukua_auth.user_model');

            $user = $userModel::firstOrCreate(
                ['bukua_user_id' => $account['user']['uid']],
                [
                    // 'email' => $account['user']['email'],
                    // 'first_name' => $account['user']['first_name'],
                    // 'last_name' => $account['user']['last_name'],
                    'name' => $account['user']['first_name'] . " " . $account['user']['last_name'],
                    'bukua_access_token' => $tokenData['access_token'],
                    'bukua_refresh_token' => $tokenData['refresh_token'],
                ]
            );

            // If user was found (not created), update their information
            if ($user->wasRecentlyCreated === false) {
                $user->update([
                    'bukua_access_token' => $tokenData['access_token'],
                    'bukua_refresh_token' => $tokenData['refresh_token'],
                    'name' => $account['user']['first_name'] . " " . $account['user']['last_name'],
                    // 'email' => $account['user']['email'],
                    // 'first_name' => $account['user']['first_name'],
                    // 'last_name' => $account['user']['last_name'],
                ]);
            }

            Auth::guard('web')->login($user);

            return redirect()->intended('/');
        } catch (\Exception $e) {
            Log::error('Bukua auth callback error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during authentication'], 500);
        }
    }
}
