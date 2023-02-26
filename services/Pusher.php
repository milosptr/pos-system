<?php

namespace Services;

use Pusher\Pusher as PusherPackage;

class Pusher
{
    private $pusher;

    public function __construct()
    {
        $this->pusher = new PusherPackage(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            ['cluster' => env('PUSHER_APP_CLUSTER', "eu")]
        );
    }

    public function trigger($channel, $event, $data)
    {
        $this->pusher->trigger($channel, $event, $data);
    }
}
