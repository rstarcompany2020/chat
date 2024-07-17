<?php

namespace App\Http\Resources;

use App\Helpers\UserCommon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatReactResource extends JsonResource
{
    function get_user($id)
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            return [
                'id'   => $user->id,
                'name' => $user->name ?? '',
                'img'  => $user->image ?? null,
            ];
        } else {
            return null;
        }
    }


    public function toArray(Request $request)
    {
        return [
            'id'                  => $this->id,
            'user_id'             => $this->user_id,
            'react'               => $this->react,
            'user'                => $this->get_user($this->user_id),
        ];
    }
}
