<?php
namespace App\Helpers;

use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Twilio\Rest\Client as TwilioClint;

class Common{



 
 
    public static function apiResponse(bool $success,$message,$data = null,$statusCode = null,$paginates = null, $isPagination = false){

        if ($success == false && $statusCode == null){
            $statusCode = 422;
        }

        if ($success == true && $statusCode == null){
            $statusCode = 200;
        }

//        $arr = ['yai'];
//        $countries = [];
//        if (in_array (\config ('app.app_origin_name'),$arr)){
//            $countries = CountryResource::collection (Country::query ()->where ('status',1)->get ());
//        }

        $arr = [
            'success' => $success,

            'message' => __($message),

            //                'extra_data'=> [
            //                    'storage_base_url'=>self::getConf ('storage_base_url') ?:asset ('storage'),
            //                    'countries'=>$countries
            //                ],


            'paginates' => $paginates
        ];


        if ($isPagination){

            $arr = array_merge($arr, $data->toArray());
         }else{
            $arr['data']  = $data;
        }


        return response ()->json (
            $arr,
            $statusCode
        );
    }


    public static function  getPaginates($collection)
    {
        return [
            'per_page' => $collection->perPage(),
            'path' => $collection->path(),
            'total' => $collection->total(),
            'current_page' => $collection->currentPage(),
            'next_page_url' => $collection->nextPageUrl(),
            'previous_page_url' => $collection->previousPageUrl(),
            'last_page' => $collection->lastPage(),
            'has_more_pages' => $collection->hasMorePages(),
            'from' => $collection->firstItem(),
            'to' => $collection->lastItem(),
        ];
    }



    public static function upload($folder,$file){
        //        $file->store('/',$folder);
        //        $fileName = $file->hashName();
        $extension = $file->getClientOriginalExtension(); // Get the file extension
        $fileName = Str::random(10).'.'.$extension; // Generate a random filename and append the extension
        $file->storeAs($folder.DIRECTORY_SEPARATOR,$fileName, \config('filesystems.default')); // Store the file with the generated filename
        return $folder.DIRECTORY_SEPARATOR.$fileName;
    }


    public static function paginate($req,$data){
        if ($req->pp){
            return static::getPaginates ($data);
        }
        return null;
    }

    // هل اتابعه

    


    




    //تصنيف حالة ترتيب اللعبة
//type 1 users 2 master
    public static function getGmOrdersText($val = null,$type = 1){
        $user=[
            1 => 'to be paid',
            2 => 'Pending orders',
            3 => 'to be served',
            31 => 'The other side applies for immediate service',
            4 => 'in progress',
            5 => 'Completed',
            6 => 'Cancelled',
            7 => 'Rejected',

            81 => 'refund application',
            82 => 'Refund successful',
            83 => 'Refund failed',
            84 => 'Appealing',
        ];

        $master=[
            1 => 'to be paid',
            2 => 'Pending orders',
            3 => 'to be served',
            31 => 'Applied for immediate service',
            4 => 'in progress',
            5 => 'Completed',
            6 => 'The other party has canceled',
            7 => 'Rejected',

            81 => 'refund application',
            82 => 'Agree to refund',
            83 => 'Refused to refund',
            84 => 'The other party is appealing',
        ];
        if($type == 1){
            return $val ? $user[$val] : $user;
        }elseif(in_array($type, [2,3])){
            return $val ? $master[$val] : $master;
        }else{
            return '';
        }
    }
    // public static function sendNotificationAndMessage($user_id,$tokens, $title, $body, $icon = '', $data = [], $action = '', $type = '', $id = '', $notification_type = 'user_notification', $titleAr = null)
    // {
    //     self::send_firebase_notification($tokens, $title, $body, $icon, $data, $action, $type, $id, $notification_type);
    //     self::sendOfficialMessage($user_id, $title, $body, $type, null, $titleAr);
    // }

    public static function send_firebase_notification($tokens, $title, $body, $icon = '', $data = [], $messageType = null, $action = '', $type = '', $id = '', $notification_type = 'user_notification')
    {

        $api_access_key =
            'AAAAYyrfZ8U:APA91bHcAaUhToEPWGpd_DfsUVv6aZLKttTDem_WF0rXJbHZMVERG9MP11G_TzcJKW_xnvzZx2R0t4Y-kCvCZn7UqfX8f6mJmVzTNAJ10lsMLMpje9AXdrCeSQ8l98H_sozao1vw9UeW';

        if (gettype($tokens) == 'string'){
            $tokens = [$tokens];
        }

        $notification = [
            'title'        => $title,
            'body'         => $body,
            'sound'        => 'tiknotifi',
            'visibility' => 'public',
            "alert" => true,

        ];

        $payload = [
            'registration_ids' => $tokens,
            'notification'     => $notification,
           // 'priority'         => 'high',
            'visibility' => 'private',
            //'sound'        => 'tiknotifi',
            'data' => [
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'message-type' => json_encode($messageType ?? ''),
                'data' => !empty($data) ? json_encode($data) : "",
            ],
        ];

        if (!empty($icon)) {
            $payload['notification']['icon'] = $icon;
        }

        if (isset($data['image']) && !empty($data['image'])) {
            $payload['notification']['image'] = $data['image'];
        } else {
            // $payload['notification']['image'] = 'https://kita.rstar-soft.com/storage/images/kitaimg.jpg';
        }

        $headers = [
            'Authorization: key=' . $api_access_key,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST , 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;


    }


   
    // public static function fireBaseFactory(){
    //     return (new Factory)
    //         ->withServiceAccount(public_path ('firebase_credentials.json'))
    //         ->withDatabaseUri('https://yay-chat-c2333-default-rtdb.firebaseio.com');
    // }

    // public static function fireBaseDatabase($path,$obj,$type = 'set'){
    //     $factory = self::fireBaseFactory ();
    //     $database = $factory->createDatabase();
    //     if ($type == 'set'){
    //         $database->getReference($path) ->set($obj);
    //     }else{
    //         return $database->getReference($path)->getSnapshot()->getValue();
    //     }

    // }

    



    
    

    


}
