<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Cache;

class CacheHelper{
    public static function put($key,$value){
        if (config ('app.cache') == 'enabled'){
            Cache::put ($key,$value);
        }
    }

    public static function get($key,$repo){
        if (config ('app.cache') == 'enabled'){
            if (Cache::has ($key)){
                $data = Cache::get ($key);
            }else{
                $data = $repo->all();
                Cache::put ($key,$data);
            }
        }else{
            $data = $repo->all();
        }
        return $data;
    }

    public static function forget($key){
        Cache::forget ($key);
    }
}
