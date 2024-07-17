<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static senderLevel(int $userId)
 * @method static receiverLevel(int $userId)
 * @method static target(int $userId)
 * @method static momentComment($moment, $user)
 * @method static familyLevelUpgrade(int $id)
 * @method static officialMsg(\App\Models\OfficialMessageAdmin $officialMessageAdmin)
 * @method static family(\App\Models\Family $family, mixed $user)
 */
class CustomNotification extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'CustomNotification';
    }


}