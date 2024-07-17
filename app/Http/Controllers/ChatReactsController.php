<?php

namespace App\Http\Controllers;

use Modules\Chat\Events\ReactMessageEvent;
use App\Http\Controllers\Controller;
use Modules\Chat\Http\Resources\ChatMessageResource;
use Modules\Chat\Http\Resources\ChatRoomResourcePusher;
use Modules\Chat\Entities\ChatMessage;
use Modules\Chat\Entities\ChatRoom;
use Modules\Chat\Entities\React;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatReactsController extends Controller
{

    public function store(Request $request)
    {
        try {
            $request->validate([
                'message_id' => 'required|exists:chat_messages,id',
                'react' => 'required',
            ]);
            $user = $request->user();
            $message = ChatMessage::find($request->message_id);
            $chat_room = ChatRoom::find($message->chat_room_id);
            $react = React::where('chat_room_id',$chat_room->id)->where('chat_message_id',$request->message_id)->where('user_id',$user->id)->first();
            if( !$message ||  !$chat_room)
            {
                return 2020;
            }
    
            $status = 0 ;
            if($request->react )
            {
                if($react &&  $react->react == $request->react)
                {
                    $react->delete();
                    $status = 'react removed';
                }
    
                if( $react &&  $react->react !== $request->react)
                {
                    $react->delete();
                    $data = new React();
                    $data->chat_message_id  = $request->message_id;
                    $data->chat_room_id  = $chat_room->id;
                    $data->user_id  = $user->id;
                    $data->react  = $request->react;
                    $data->save();
                    $status = 'react changed';
    
                }
    
                else if (!$react) {
                    $data = new React();
                    $data->chat_message_id  = $request->message_id;
                    $data->chat_room_id  = $chat_room->id;
                    $data->user_id  = $user->id;
                    $data->react  = $request->react;
                    $data->save();
                    $status = ' react added';
    
                }
            }
            if($message && $chat_room)
            {
                if($chat_room->user_id == $user->id)
                {
                    $user2 =User::with('profile')->find($chat_room->user_id2);
                }
                else{
                    $user2 =User::with('profile')->find($chat_room->user_id);
                }
                $item = new ChatMessageResource($message);
                $room_resource =  new ChatRoomResourcePusher($chat_room) ;
                try {
                    event(new ReactMessageEvent( $item->toResponse(request())->getData()->data  , $user2 , $room_resource ));

                } catch (\Throwable $th) {
                   return $th->getMessage();
                }
            }
    
            return response()->json([
                'status' => 200,
                'react' => $status,
                'message' => $item
            ]);
        } catch (\Throwable $th) {
           //Log::info($th->getMessage());
           return $th->getMessage();
        }
       
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'message_id' => 'required|exists:chat_messages,id',
    //             'react' => 'required',
    //         ]);

    //         $user = $request->user();
    //         $message = ChatMessage::find($request->message_id);
    //         $chat_room = ChatRoom::find($message->chat_room_id);

    //         if (!$message || !$chat_room) {
    //             return response()->json(['status' => 2020], 2020);
    //         }

    //         $react = React::where('chat_room_id', $chat_room->id)
    //             ->where('chat_message_id', $request->message_id)
    //             ->where('user_id', $user->id)
    //             ->first();

    //         $status = 'react added';  // Default action
    //         if ($react) {
    //             if ($react->react == $request->react) {
    //                 $react->delete();
    //                 $status = 'react removed';
    //             } else {
    //                 $react->react = $request->react;
    //                 $react->save();
    //                 $status = 'react changed';
    //             }
    //         } else {
    //             $react = new React([
    //                 'chat_message_id' => $request->message_id,
    //                 'chat_room_id' => $chat_room->id,
    //                 'user_id' => $user->id,
    //                 'react' => $request->react
    //             ]);
    //             $react->save();
    //         }

    //         if ($chat_room->user_id == $user->id) {
    //             $user2 = User::with('profile')->find($chat_room->user_id2);
    //         } else {
    //             $user2 = User::with('profile')->find($chat_room->user_id);
    //         }

    //         $item = new ChatMessageResource($message);
    //         $room_resource = new ChatRoomResourcePusher($chat_room);

    //         event(new ReactMessageEvent($item->toArray($request), $user2->profile, $room_resource->toArray($request)));

    //         return response()->json([
    //             'status' => 200,
    //             'react' => $status,
    //             'message' => $item
    //         ]);
    //     } catch (\Throwable $th) {
    //         Log::error($th->getMessage());
    //         return response()->json(['error' => $th->getMessage()], 500);
    //     }
    // }


}
