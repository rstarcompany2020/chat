<?php
namespace App\Traits;

use App\Models\ChatRoom;


trait ChatUserTrait {
    public function chats()
    {
        return $this->belongsToMany(ChatRoom::class,'pin_to_tops');
    }

}

