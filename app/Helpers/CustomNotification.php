<?php

namespace App\Helpers;

use App\Models\Vip;
use App\Models\Gift;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Family;
use App\Models\OfficialMessage;
use Modules\Reals\Entities\Real;
use Illuminate\Support\Facades\DB;
use Modules\Moment\Entities\Moment;
use App\Models\OfficialMessageAdmin;
use Modules\Public\Http\Services\UserCounterServices;

class CustomNotification
{


    public function follow(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        $body_ar = __('api.followed_you', ['name' => $user->name], 'ar');
        $body_en = __('api.followed_you', ['name' => $user->name], 'en');
        $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;

        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notfacion, __('api.tik_chat'), $firebaseBody, icon: $icon, data: $data,messageType: 'follow');
        
    }

    public function followBack(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        $body_ar = __('api.follow_back', ['name' =>  $user->name], 'ar');
        $body_en = __('api.follow_back', ['name' =>  $user->name], 'en');
        $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notfacion, __('api.tik_chat'), $firebaseBody, icon: $icon, data: $data,messageType: 'followBack');
       
    }

    
    

    
    


}