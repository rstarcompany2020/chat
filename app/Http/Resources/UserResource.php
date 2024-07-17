<?php

namespace App\Http\Resources;

use App\Helpers\Common;
use App\Models\Family;
use App\Models\Pack;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ImageColor;
use App\Http\Resources\Api\V1\ChatSettingResource;
use App\Http\Resources\Api\V1\MangerTypeResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $data      = [
            'id'      => @$this->id, // both
            // 'chat_id' => @$this->chat_id ?: "", // both                     
            'notification_id'      => @$this->notification_id ?: "",
            'name'                 => @$this->name ??"",
            'image'                => @$this->image ?? "",
            'number_of_followings' => $this->numberOfFollowings(), // both  ---
            'number_of_friends'    => $this->numberOfFriends(), // both  ------

        ];

        return $data;
    }
}
