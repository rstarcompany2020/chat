<?php
use App\Classes\AppSetting;

const LUCKY_REDIS_KEY = "thresholds_lucky_prices";
const PK_IMAGE = 'custom_image/pk.png';
const CINEMA_IMAGE = 'custom_image/back-black.png';
const GAME_COINS_PLAY = 'game_coins_play_#';

function generateSignature($nonce, $appKey, $timestamp)
{
    $data = sprintf("%s%s%d", $nonce, $appKey, $timestamp);
    return md5($data);
}

function generatesignatureNonce()
{
    $tempByte = random_bytes(8);
    $signatureNonce = bin2hex($tempByte);
    return $signatureNonce;
}

function getNonce()
{
    return Str::random(16);
}

if (!function_exists('check')) {
    function check()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                return auth()->guard($guard);
            }
        }
    }
}

if (!function_exists('human_file_size')) {
    function human_file_size($bytes, $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

if (!function_exists('get_file_details')) {
    function get_file_details($path)
    {
        return app('upload.manager')->fileDetails($path);
    }


    if (!function_exists('numToString')) {
        function numToString($number)
        {
            $units = ['', 'K', 'M', 'B', 'T', 'D', 'E', 'F'];
            for ($i = 0; $number >= 1000; $i++) {
                $number /= 1000;
            }
            return round($number, 2) . $units[$i];
        }
    }

    if (!function_exists('numToStringNew')) {
        function numToStringNew($number)
        {
            $units = ['', 'K', 'M', 'B', 'T', 'D', 'E', 'F'];
            for ($i = 0; $number >= 1000 && $i < count($units) - 1; $i++) {
                $number /= 1000;
            }
            $number = floor($number * 10) / 10;
            return $number . $units[$i];
        }
    }

    if (!function_exists('dispatchJobToQueue')) {
        function dispatchJobToQueue($job, $queueName = 'database')
        {
            $connection = config('queue.default');
            $queueNames = config('queue.connections.' . $queueName . '.queue');

            $minQueueSize  = null;
            $selectedQueue = null;

            foreach ($queueNames as $queueName) {
                $queueSize = Queue::connection($connection)->size($queueName);
                //            $queueSize = \DB::table('jobs')->where('queue', $queueName)->count();
                //            $jobCount  = \DB::table('job_statistics')->where('queue', $queueName)->value('job_count');

                if ($minQueueSize === null || $queueSize  < $minQueueSize) {
                    $minQueueSize  = $queueSize;
                    $selectedQueue = $queueName;
                }
            }

            \Illuminate\Support\Facades\Queue::connection($connection)->pushOn($selectedQueue, $job);
        }
    }

    if (!function_exists('settings')) {

        function settings(): AppSetting
        {
            return new AppSetting();
        }
    }

    if (!function_exists('getOfficialMessage')) {

        function getOfficialMessage($message): string
        {
            //api.welcome#name-محمد#app_name-Laravel
            return '';
        }
    }
    if (!function_exists('getImagePath')) {

        function getImagePath(?string $path = null): ?string
        {
            return $path == null ? null : getDriverUrl() . '/' . $path;
        }
    }

    if (!function_exists('getDriverUrl')) {

        function getDriverUrl(): ?string
        {
            return config('filesystems.disks.' . \config('filesystems.default') . '.url');
        }
    }

    if (!function_exists('dispatchRoomsRedis')) {

        function dispatchRoomsRedis(int $roomId, int $userId, $coins = 0, $data = null, string $type = 'charisma'): void
        {

            $key = 'CharismaGift_' . $type . '_' . $userId . '_' . $roomId . '_' . implode($data);
            $data = serialize($data);

            try {

                $rData = Redis::get($key);

                if ($rData) {
                    $rData = unserialize($rData);
                    if (isset($coins)) {
                        $rData['coins'] += $coins;
                        Redis::set($key, serialize($rData));
                    }
                } else {

                    $values = [
                        'user_id'    => $userId,
                        'room_id'    => $roomId,
                        'data'       => $data,
                        'type'       => $type,
                        'coins'      => $coins,
                        'created_at' => now(),
                        'updated_at' => now(),

                    ];
                    \Illuminate\Support\Facades\Redis::set($key, serialize($values));
                }

                //            \Illuminate\Support\Facades\DB::table('room_jobs')->insert($values);
            } catch (Exception $exception) {
            }
        }
    }
}


if (!function_exists('isSubdomain')) {

    function isSubdomain($host = null)
    {
        if (!$host) $host = \request()->getHost();
        $hostParts = explode('.', $host);

        if (count($hostParts) < 2) {
            return false;
        }
        //        $baseDomain = implode('.', array_slice($hostParts, -2));

        return count($hostParts) > 2;
    }

}


if (!function_exists( 'getPusherConfig')) {
    function getPusherConfig()
    {
        return \Illuminate\Support\Facades\Cache::remember('pusher_config', 60 * 60 * 24, function () {
            if (!isSubdomain()) {
                return null;
            }

            $Keys = ['app_id', 'app_key', 'app_secret', 'app_cluster'];
            $configs = \App\Models\Config::whereIn('name', $Keys)->pluck('value', 'name');

            $appId = !empty($configs->get('app_id')) ? $configs->get('app_id') : Config::get('broadcasting.connections.pusher.app_id');
            $appKey = !empty($configs->get('app_key')) ? $configs->get('app_key') : Config::get('broadcasting.connections.pusher.key');
            $appSecret = !empty($configs->get('app_secret')) ? $configs->get('app_secret') : Config::get('broadcasting.connections.pusher.secret');
            $appCluster = !empty($configs->get('app_cluster')) ? $configs->get('app_cluster') : Config::get('broadcasting.connections.pusher.options.cluster');

            return [
                'app_id' => $appId,
                'app_key' => $appKey,
                'app_secret' => $appSecret,
                'app_cluster' => $appCluster,
            ];
        });
    }
}
