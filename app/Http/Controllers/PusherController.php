<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Jobs\ReciveChatMessagejob;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class PusherController extends Controller
{
    function edit_user(Request $request) {
        if (!$request->header('X-Pusher-Key') === env('PUSHER_APP_KEY')) {
            abort(403, 'Invalid Pusher webhook request');
        }
        $channel = $request->events[0]['channel'];
        $name = $request->events[0]['name'];
        $parts = explode('-', $channel);

        if (count($parts) === 2) {
            $chnnel_name = $parts[0];
            $number = $parts[1];
          if($chnnel_name == 'user')
          {
            $user = User::find($number);
            if($user)
            {
                if($name  =='channel_vacated')
                {
                    $user->online = 0;
                    $user->current_room_chat  = null ;
                }
                else{
                    $user->online = 1;
                    $chats_id = ChatRoom::where('user_id', $user->id)->orWhere('user_id2', $user->id)->get()->pluck('id')->toArray();;
                    $total_unread =  ChatMessage::whereIn('chat_room_id', $chats_id)->where('user_id','not Like',$user->id)->where('status','sended')->get();
                    dispatch(new ReciveChatMessagejob($total_unread , 'received'));
                }
                $user->save();
            }
          }
        }
        return response()->json(['status' => 'Webhook received']);
    }

    function user_status($id) {
        $user = User::find($id);
        if($user)
        {
            return [
                'online' =>$user->online
            ] ;
        }
        else{
            return 'user not found';
        }
    }
}
