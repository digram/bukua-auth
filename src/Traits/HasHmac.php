<?php

namespace BukuaAuth\Traits;

trait HasHmac
{
    protected function generateHmac($data)
    {
        return hash_hmac('sha256', json_encode($data), config('app.key'));
    }

    protected function verifyHmac($data, $signature)
    {
        return hash_equals($this->generateHmac($data), $signature);
    }
}
