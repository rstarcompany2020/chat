<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;
use App\Helpers\Common;
use Illuminate\Http\Request;

use App\Facades\CustomNotification;
use App\Http\Controllers\Controller;

class FollowController extends Controller
{
    public function follow(Request $request)
    {

        if ($request->user()->id == $request->user_id) {
            return Common::apiResponse(false, 'cant follow your self', null, 403);
        }
        if (!User::query()->find($request->user_id)) {
            return Common::apiResponse(false, 'this user not found', null, 404);
        }
        $f = Follow::query()->where(
            [
                'user_id' => $request->user()->id,
                'followed_user_id' => $request->user_id
            ]
        )->first();
        if (!$f) {
            Follow::query()->create(

                [
                    'user_id' => $request->user()->id,
                    'followed_user_id' => $request->user_id,
                    'status' => 1
                ]
            );
            //follow back

            $receiver = User::find($request->user_id);
            $user = $request->user();
            if ($user->followBack($receiver)) {
                CustomNotification::followBack($receiver, $user);

            } else {
                CustomNotification::follow($receiver, $user);
                
            }
        } else {
            $f->status = 1;
            $f->save();
        }

        return Common::apiResponse(true, 'follow done', null, 201);
    }
    public function unFollow(Request $request)
    {
        Follow::query()->where('user_id', $request->user()->id)->where('followed_user_id', $request->user_id)->delete();
        return Common::apiResponse(true, 'unFollow done', null, 201);
    }

}
