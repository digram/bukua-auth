<?php

namespace BukuaAuth\Traits;

trait Me
{
    public function me()
    {
        return $this->makeAuthenticatedRequest('api/v1/me');
    }

    public function roles()
    {
        return $this->makeAuthenticatedRequest('api/v1/me/roles');
    }

    public function school()
    {
        return $this->makeAuthenticatedRequest('api/v1/me/school');
    }

    public function subjects()
    {
        return $this->makeAuthenticatedRequest('api/v1/me/subjects');
    }
}
