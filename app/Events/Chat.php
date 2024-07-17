<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Chat implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat , $user2;
    public function __construct($chat , $user2)
    {
        $this->chat = $chat;
        $this->user2 = $user2;

    }


    public function broadcastOn()
    {
        return ['user-'.$this->user2?->id];
    }

    public function broadcastAs()
    {
        return 'getChatUsersBloc';
    }

    public function broadcastWith() : array
    {
        return (array) $this->chat;
    }
}
