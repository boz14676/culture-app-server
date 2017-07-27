<?php

namespace App\Services\QcloudSMS;

use Cache;
use Log;
use Carbon\Carbon;

class Sms {

    const API = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';

    /**
     * Request SMS codes
     *
     * @param string $mobile
     */
    public static function _requestSmsCode($mobile) {
        Cache::put($mobile, 123456, Carbon::now()->addMinutes(30));
        return true;
    }
    
    public static function requestSmsCode($mobile) {

        $code = rand(111111, 999999);

        $appid = env('SMS_APPID');
        $appkey = env('SMS_APPKEY');

        $time = time();

        $params = [
            'tel' => [
                'nationcode' => '86',
                'mobile' => $mobile
            ],
            'type' => '0',
            'time' => $time,
            'extend' => '',
            'ext' => '',
            'msg' => str_replace('#CODE#', $code, env('SMS_TEMPLATE')),
            'sig' => hash("sha256", "appkey={$appkey}&random={$time}&time={$time}&mobile={$mobile}")
        ];

        $result = curl_request(self::API. "?sdkappid={$appid}&random={$time}", 'POST', json_encode($params));

        $result = json_decode($result, true);

        if(is_array($result) && $result['result'] === 0 ){
            // 发送成功
            Cache::put($mobile, $code, Carbon::now()->addMinutes(10));
            Log::info($mobile."的短信验证码发送成功，验证码：" . $code);
            return true;
        }else{
            Log::info($mobile." 的短信验证码发送失败，[error_msg: ".$result['errmsg'].']');
            // 发送失败
            return false;
        }
    }

    /**
     * Verify SMS codes
     *
     * @param string $mobile
     * @param string $code
     */
    public static function verifySmsCode($mobile, $code) {
        if (Cache::get($mobile) == $code) {
            return true;
        }
    }

}

