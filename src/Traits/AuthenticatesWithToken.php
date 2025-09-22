<?php

namespace BukuaAuth\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

trait AuthenticatesWithToken
{
    private function makeAuthenticatedRequest(string $endpoint, array $queryParams = [])
    {
        $token = Auth::guard('web')->user()?->bukua_access_token;

        if (!$token) {
            throw new \RuntimeException('Unable to retrieve access token');
        }

        try {
            $token = Crypt::decryptString($token);
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to decrypt access token');
        }

        try {
            return Http::withToken($token)
                ->acceptJson()
                ->get($this->baseUrl . $endpoint, $queryParams)
                ->throw()
                ->json();
        } catch (RequestException $e) {
            if ($e->response && $e->response->status() === 401) {
                // TODO: Token might be expired, try to refresh it
                throw new \RuntimeException('Access token expired or invalid');
            }

            throw new \RuntimeException("Failed to fetch data from {$endpoint}: " . $e->getMessage());
        }
    }
}
