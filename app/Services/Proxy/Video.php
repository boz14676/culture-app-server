<?php

/**
 * 视频接口请求
 */

namespace App\Services\Proxy;

use Log;
use Curl;

class Video
{
    const API = 'http://api.alphacut.me/';
    
    // 访问第三方接口获取数据
    public static function push($attributes)
    {
        $uri = 'api/ustory/new_task';
        $result = Curl::to(self::API.$uri, 'POST')
            ->withData($attributes)
            ->asJson(true)
            ->post();
        if($result['succeed'] == 1) {
            Log::info('视频生成成功 [task_id:'.$result['task_id'].']');

            return $result['task_id'];
        } else {
            Log::info('视频生成失败 [Code:'.$result['error_code'].', Msg: '.$result['message'].']');
            
            return false;
        }
    }
}
