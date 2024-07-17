<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageReplay extends Model
{
    use HasFactory;
    protected $guarded =['id'];

    function message()  {
        return $this->belongsTo(ChatMessage::class, "message_id");
    }

    function from_message()  {
        return $this->belongsTo(ChatMessage::class , "from_message_id") ;
    }
}
