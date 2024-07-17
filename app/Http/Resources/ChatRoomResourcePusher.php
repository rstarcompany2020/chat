<?php

namespace App\Http\Resources;


use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResourcePusher extends JsonResource
{

    public function toArray(Request $request)
    {
        if($this->user_id == $request->user()->id)
        {
            $user2 = User::find($this->user_id);  // reciver_id
        }
        else{
            $user2 = User::find($this->user_id2);  // reciver_id
        }

        $total_undread_message = ChatMessage::where('chat_room_id',$this->id)->where('user_id',$user2->id)->where('status','not Like','seen')->count();
        return [
            'user_id'             => $user2->id??0,
            'name'                => $user2->name?? __('api_responses.fakeName'),
            'img'                 => @$user2->profile->avatar,
            'chat_id'             => $this->id,
            'unread_message'      => $total_undread_message,
            'last_message'        => @ new ChatMessageResource( $this->messages[0]),
        ];
    }
}
