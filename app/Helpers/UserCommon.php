<?php
namespace App\Helpers;

use App\Models\OVip;
use App\Models\Vip;
use App\Models\Gift;
use App\Models\Pack;
use App\Models\Room;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Config;
use App\Models\Target;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use App\Models\Country;
use App\Models\GiftLog;
use App\Models\PackLog;
use App\Models\UserVip;
use App\Models\UserSallary;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Request;
use Kreait\Firebase\Factory;
use App\Models\UserLuckyGift;
use Illuminate\Support\Carbon;
use App\Models\OfficialMessage;
use Encore\Admin\Facades\Admin;
use App\Models\Owner_pid_target;
use App\Models\UserCodeInvitation;
use App\Models\UserEarnInvitation;
use Illuminate\Support\Facades\DB;
use App\Models\AgencyMangerPullingOut;
use App\Traits\HelperTraits\InfoTrait;
use App\Traits\HelperTraits\RoomTrait;
use App\Traits\HelperTraits\ZegoTrait;
use Twilio\Rest\Client as TwilioClint;

use App\Http\Resources\CountryResource;
use App\Traits\HelperTraits\AdminTrait;
use App\Traits\HelperTraits\CalcsTrait;
use App\Traits\HelperTraits\MoneyTrait;
use App\Traits\HelperTraits\FilterTrait;
use App\Traits\HelperTraits\AttributesTrait;
use Illuminate\Database\Eloquent\Collection;
use App\Classes\Facades\Agency as FacadesAgency;

class UserCommon{


    public static function arabicToEnglishNumbers($string) {
        $newNumbers = range(0, 9);
        // الأرقام العربية
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        return str_replace($arabicNumbers, $newNumbers, $string);
    }

    // public static function englishToArabicNumbers($string) {
    //     // الأرقام الإنجليزية
    //     $englishNumbers = range(0, 9);
    //     // الأرقام العربية
    //     $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    
    //     return str_replace($englishNumbers, $arabicNumbers, $string);
    // }
    

    public static function englishToArabicNumbers($string) {
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        $createdAt = Carbon::parse($string)->locale('ar_SA')->isoFormat('h:mm:ss A');
        $createdAt = str_replace($numbers, $arabicNumbers, $createdAt);

        return $createdAt;
    }

    public static function englishToArabicNumbersDate($string) {
        $numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

        $createdAt = Carbon::parse($string)->locale('ar_SA')->format('Y-m-d');
        $createdAt = str_replace($numbers, $arabicNumbers, $createdAt);

        return $createdAt;
    }

    public static function convertArabicNumbers($string) {
        $newNumbers = range(0, 9);
        $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        return str_replace($arabicNumbers, $newNumbers, $string);
    }

}
