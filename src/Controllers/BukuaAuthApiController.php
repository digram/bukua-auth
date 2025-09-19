<?php

namespace BukuaAuth\Controllers;

use Illuminate\Routing\Controller;
use BukuaAuth\Traits\AuthenticatesWithToken;
use BukuaAuth\Traits\Me;

class BukuaAuthApiController extends Controller
{
    use AuthenticatesWithToken;
    use Me;

    protected string $baseUrl;

    public function __construct()
    {
        // ensure it has a trailing slash
        $this->baseUrl = rtrim(config('services.bukua_auth.base_url'), '/') . '/';
    }
}
