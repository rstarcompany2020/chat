<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;
    protected $guarded =['id'];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id','desc');
    }

    public function unReadMessages()
    {
        return $this->hasMany(ChatMessage::class)->where('status','not Like','seen');
    }
    public function getLastMessageCreatedAtAttribute()
    {
        $lastMessage = $this->messages()->latest()->first();
        return $lastMessage ? $lastMessage->created_at : null;
    }

    public function userOne(){
        return $this->belongsTo(User::class,'user_id');
    }

    public function userTwo(){
        return $this->belongsTo(User::class,'user_id2');
    }
}
