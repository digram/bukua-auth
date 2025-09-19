<?php

namespace BukuaAuth\Traits;

trait Me
{
    public function me()
    {
        return $this->makeAuthenticatedRequest('/api/v1/me');
    }

    public function subjects()
    {
        return $this->makeAuthenticatedRequest('/api/v1/me/subjects');
    }
}
