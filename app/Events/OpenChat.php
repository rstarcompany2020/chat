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

class OpenChat implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat , $user2 ,  $check_room ;
    public function __construct($chat , $user2 ,  $check_room )
    {
        $this->chat = $chat;
        $this->user2 = $user2;
        $this->check_room  =  $check_room ;

    }

    public function broadcastOn() :array
    {
        return ['user-'.$this->user2->id ,'conversation-'.$this->check_room->id ];
    }

    public function broadcastAs()
    {
        return 'open_chat';
    }

    public function broadcastWith() : array
    {
        return (array) $this->chat;
    }
}
