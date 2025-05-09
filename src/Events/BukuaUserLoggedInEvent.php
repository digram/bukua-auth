<?php

namespace BukuaAuth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BukuaUserLoggedInEvent
{
    use Dispatchable, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param mixed $data
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
