<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use App\Events\Chat;
use App\Models\User;
use App\Helpers\Common;
use App\Models\ChatRoom;
use App\Models\BlackList;
use App\Models\ChatMessage;
use App\Traits\FfmpegTrait;
use Illuminate\Support\Str;
use App\Events\Conversation;

use App\Models\MessageAlbum;
use Illuminate\Http\Request;
use App\Events\DeleteMessage;
use App\Models\MessageReplay;
use App\Events\CardDeleteMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\ChatRoomResource;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatRoomResourcePusher;

class ChatMessagesController extends Controller
{
    use FfmpegTrait ;

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:255',
            'message_id' => 'nullable|exists:chat_messages,id',
        ]);
        $user = $request->user();

        $check =BlackList::where("user_id", $request->user()->id)->where("from_uid", $request->user_id)
        ->orwhere("user_id", $request->user_id)->where("from_uid",$request->user()->id)->first();
        if($check)
        {
            return response()->json([
                'status' => 404,
                'message' => "Unauthorized Block Condition"
            ],404);
        }

        $check_room = ChatRoom::where('user_id', $user->id)->where('user_id2', $request->user_id)
            ->orWhere('user_id', $request->user_id)->where('user_id2', $user->id)->first();

        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        $total_message = ChatMessage::where('chat_room_id',$check_room->id)->where('user_id',$user->id)->count();
        if($check_room->type == 'guest' && $total_message >=3 ){
            return response()->json([
                'status' => 404,
                'status' => 'unauthorized',
            ], 404);
        }

        //get user 2
        if($check_room->user_id == $user->id)
        {
            $user2 =User::find($check_room->user_id2);
        }
        else{
            $user2 =User::find($check_room->user_id);
        }

        //Files Validations
        if ($request->hasFile('file')) {
            $images_extensions = ['jpeg', 'jpg', 'png','gif','mp4','mp3','wav','pdf'];
            foreach ( $request->file('file') as $file) {
                $extension = $file->extension();
                $check = in_array($extension, $images_extensions);
                if (!$check ) {
                    return response()->json([
                        'status' => 404,
                        'message' => "File Doesn't Match our Records",
                    ],404);
                }
            }
        }

        $message = new ChatMessage();
        $message->chat_room_id = $check_room->id;
        $message->user_id = $user->id;
        $message->message = $request->message;
        $message->save();

        //add status for message
        if($user2->online == 1 && $user2->current_room_chat == $check_room->id )
        {
            $message->status = 'seen';
            $message->update();
        }
        else if($user2->online == 1)
        {
            $message->status = 'received';
            $message->update();
        }
        else if($user2->is_logout == 0){
            $tokens_notfacion[] = DB::table('users')->where('id', $user2->id)->value('notification_id');
            $title=$user->name;
            $body= $message->message ;
            Common::send_firebase_notification($tokens_notfacion,$title,$body);
        }

        //insert files to database
        if ($request->hasFile('file')) {
            $files = $request->file('file');
            $images_extensions = ['jpeg', 'jpg', 'png'];
            $count = count($files);

            if($count == 1)
            {
                $extension = $files[0]->extension();
                $check = in_array($extension, $images_extensions);
                $file = $request->file[0];

                //create album
                $album = new MessageAlbum();
                $album->chat_room_id = $check_room->id;
                $album->chat_message_id = $message->id;
                $album->user_id = $user->id;

                if ($check) {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                    $album->file =  $file_name;
                    $album->type =  'img';
                    $album->save();

                    $message->type = 'img';
                    $message->update();

                }
                else if ($extension == 'gif') {
                    $album->file =  $file->getClientOriginalName();
                    $album->type =  'gif';
                    $album->save();

                    $message->type = 'gif';
                    $message->message = null;
                    $message->update();
                }
                else if ($extension == 'mp4') {
                    // $file_name =  Str::uuid().'chat.' .$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name =   Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);

                    $name =  pathinfo($file_name, PATHINFO_FILENAME);

                    $album->file =  $file_name;
                    $album->type =  'video';
                    $videoPath = $file_name;
                    $thumbnailPath =  'Chat_'.env('APP_ENV').'/chat_'.$check_room->id.'/'. $name.'.jpg';

                    try {
                        $this->extract_frame($videoPath, $thumbnailPath);

                    } catch (\Throwable $e) {
                       return $e->getMessage();

                    }
                    $album->frame =  $thumbnailPath;
                    $album->save();

                    $message->type = 'video';
                    $message->message = null;
                    $message->update();
                }

                else if ($extension == 'mp3' ||  $extension == 'wav'||  $extension == 'm4a'||  $extension == 'aac')  {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name =  Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                    $album->file =  $file_name;
                    $album->type =  'voice';
                    $album->save();

                    $message->type = 'voice';
                    $message->message = null;
                    $message->update();
                }
                else if ($extension == 'pdf')  {
                    // $file_name =  Str::uuid().'chat.'.$extension;
                    // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                    $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                    $album->file =  $file_name;
                    $album->type =  'file';
                    $album->save();

                    $message->type = 'file';
                    $message->message = null;
                    $message->update();
                }
            }
            else{
                foreach ($files as $file) {
                    $extension = $file->extension();
                    $check = in_array($extension, $images_extensions);

                    $message->type = 'album';
                    $message->update();

                    //add album
                    $album = new MessageAlbum();
                    $album->chat_room_id = $check_room->id;
                    $album->chat_message_id = $message->id;
                    $album->user_id = $user->id;

                    if ($check) {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                        $album->file =  $file_name;
                        $album->type =  'img';
                        $album->save();
                    }
                    else if ($extension == 'gif') {
                        $album->file =  $file->getClientOriginalName();
                        $album->type =  'gif';
                        $album->save();



                        $album->type =  'gif';
                        $album->save();
                    }
                    else if ($extension == 'mp4') {
                        // $file_name =  Str::uuid().'chat.' .$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                        $name =  pathinfo($file_name, PATHINFO_FILENAME);

                        $album->file =  $file_name;
                        $album->type =  'video';
                        $videoPath = $file_name;
                        $thumbnailPath =  'Chat_'.env('APP_ENV').'/chat_'.$check_room->id.'/'. $name.'.jpg';
                        try {
                            $this->extract_frame($videoPath, $thumbnailPath);

                        } catch (\Throwable $e) {
                           return $e->getMessage();

                        }
                        $album->frame =  $thumbnailPath;
                        $album->save();

                    }
                    else if ($extension == 'mp3' ||  $extension == 'wav'||  $extension == 'm4a'||  $extension == 'aac') {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                        $album->file =  $file_name;
                        $album->type =  'voice';
                        $album->save();
                    }
                    else if ($extension == 'pdf' ) {
                        // $file_name =  Str::uuid().'chat.'.$extension;
                        // $file->move(public_path('upload/chat_'.$check_room->id), $file_name);
                        $file_name = Common::upload('Chat_'.env('APP_ENV').'/chat_'.$check_room->id,$file);
                        $album->file =  $file_name;
                        $album->type =  'file';
                        $album->save();
                    }
                }
            }
        }

        //Replay Message
        if($request->message_id)
        {
            $data = new MessageReplay();
            $data->message_id = $message->id;
            $data->from_message_id  = $request->message_id;
            $data->save();
        }
        $data = ChatMessage::find($message->id);
        $message_resource = new ChatMessageResource($data);
        $room_resource =  new ChatRoomResourcePusher($check_room) ;
        // return $user2;
        try{
        event(new Conversation( $message_resource->toResponse(request())->getData()->data  , $user2 , $room_resource ));
        event(new Chat($room_resource->toResponse(request())->getData()->data , $user2));
    } catch (\Throwable $th) {
        return $th->getMessage();
     }
        return [
         'message'=>    $message_resource,
         'card' =>  new ChatRoomResource($check_room)
        ];
    }

    public function update(Request $request)
    {
        $request->validate([
            'message_id' => 'required|exists:chat_messages,id',
        ]);
        $message = ChatMessage::find($request->message_id);
        $chat_room_id = ChatRoom::find($message->chat_room_id)->id;
        $user =$request->user();

        if(!$message || !$chat_room_id || $message->user_id !== $user->id  ){
            return response()->json([
                'status' => 404,
                'message' =>'unAuthorized',
            ],404);
        }


        $date = Carbon::now()->format('Y-m-d H:i:s');
        $created_at = Carbon::parse($message->created_at)->addMinutes(15)->format('Y-m-d H:i:s');
        if($date >  $created_at)
        {
            return response()->json([
                'status' => 404,
                'message' =>'Deletion is permissible within a 15-minute  after sending.',
            ],404);
        }


        $message->message = $request->message;
        $message->update();
        $data = ChatMessage::find($message->id);
        return new ChatMessageResource($data);
    }

    public function deleteForAll(Request $request )
    {
        $chat_room_id =0 ;
        $user =$request->user();
        $ids = [];
        foreach ($request->id as $id) {
            $ids [] = (int)$id ;
            $message = ChatMessage::find($id);
            if(!$message   ){
                return response()->json([
                    'status' => 404,
                    'message' =>'Message not found',
                ],404);
            }
            if( $message->user_id !== $user->id   ){
                return response()->json([
                    'status' => 404,
                    'message' =>'Delete Forbidden ',
                ],404);
            }

            $date = Carbon::now()->format('Y-m-d H:i:s');
            $created_at = Carbon::parse($message->created_at)->addDay(1)->format('Y-m-d H:i:s');
            if($date >  $created_at)
            {
                return response()->json([
                    'status' => 404,
                    'message' =>'Deletion is permissible within a Day  after sending.',
                ],404);
            }

            $message->delete();
            $chat_room_id = $message->chat_room_id;
        }
        $check_room = ChatRoom::find($chat_room_id);
        if($check_room->user_id == $user->id)
        {
            $user2 =User::find($check_room->user_id2);
        }
        else{
            $user2 =User::find($check_room->user_id);
        }

        $room_resource =  new ChatRoomResourcePusher($check_room) ;
        try{
        event(new DeleteMessage( $ids , $user2 , $room_resource ));
        event(new CardDeleteMessage($room_resource->toResponse(request())->getData()->data , $user2));
    } catch (\Throwable $th) {
        return $th->getMessage();
     }
        return response()->json([
            'status' => 200,
            'message' => 'message deleted',
        ]);
    }

    public function deleteForMe(Request $request )
    {
        $user =$request->user();
        foreach ($request->id as $id) {
            $message = ChatMessage::find($id);
            if(!$message ){
                return response()->json([
                    'status' => 404,
                    'message' =>'message not found',
                ],404);
            }
            $check_room = ChatRoom::find($message->chat_room_id );

            if( $check_room->user_id !== $user->id && $check_room->user_id2 !== $user->id){
                return response()->json([
                    'status' => 404,
                    'message' =>'message not found',
                ],404);
            }

            if($message->user_id == $user->id)
            {
                $message->user_1_deleted = Carbon::now();
            }
            else{
                $message->user_2_deleted = Carbon::now();
            }
            $message->update();
        }
        return response()->json([
            'status' => 200,
            'message' => 'message deleted',
        ]);
    }

}
