<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatRoom;
use App\Models\PinToTop;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PinToTopController extends Controller
{

    public function index(Request $request)
    {
        $user = User::with('chats')->find($request->user()->id);
        return $user->chats;
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'chat_id' => 'required|exists:chat_rooms,id',
        ]);
        $user = $request->user();
        $check_room = ChatRoom::where('user_id', $user->id)->where('user_id2', $request->user_id)
            ->orWhere('user_id', $request->user_id)->where('user_id2', $user->id)->first();
        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        $chat = new PinToTop();
        $chat->chat_room_id = $check_room->id;
        $chat->user_id = $user->id;
        $chat->save();

        return response()->json([
            'status' => 200,
            'message' => 'chat added to top',
        ]);
    }


    public function destroy(Request $request ,string $id)
    {
        $user = $request->user();
        $check_room = ChatRoom::where('user_id', $user->id)->orWhere('user_id2', $user->id)->first();
        if (!$check_room) {
            return response()->json([
                'status' => 404,
                'status' => 'Chat not Found',
            ], 404);
        }
        PinToTop::where('chat_room_id',$id)->where('user_id',$user->id)->delete();
        return response()->json([
            'status' => 200,
            'status' => 'Chat Removed',
        ]);
    }
}
