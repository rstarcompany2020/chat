<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if (!$request->user_id) return Common::apiResponse(0, 'missing parameter', 404);
        $user = User::find($request->user_id);
        if (!$user) {
            if ($request->hasFile('image')) {
                $img = $request->file('image');
                $image = Common::upload('profile', $img);
            }
            $user = User::create([
                'name' => $request->name,
                'image' => $image,
                'notification_id' => $request->notification_id,
                'lan' => $request->lan,
            ]);
        }
        $token = $user->createToken('api_token')->plainTextToken;
        $user->auth_token = $token;
        return Common::apiResponse(
            true,
            __('api.logged'),
            [
                'id'            => $user->id,
                'auth_token'    => $user->auth_token
            ]
        );
    }



    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        return Common::apiResponse(1, 'logged out');
    }
}
