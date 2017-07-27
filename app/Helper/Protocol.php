<?php

namespace App\Helper;

use Log;

class Protocol
{
    public static function generateSign($data, $timestamp = false)
    {
        if (!$timestamp) {
            $timestamp = time();
        }

        ksort($data);
        
        $i = 0;
        $string_to_sign = '';
        foreach ($data as $k => $v) {
            if ($i == 0) {
                $string_to_sign .= $k . "=" . rawurlencode($v);
            } else {
                $string_to_sign .= "&" . $k . "=" . rawurlencode($v);
            }
            $i++;
        }

        if (config('app.debug')) {
            Log::error('data:'.$string_to_sign);
        }
        
        $key = config('security.sign_key');
        return hash_hmac('sha256', $timestamp.$string_to_sign, $key);
    }


    public static function verifySign()
    {
        $request = app('request');
        $header_sign = $request->header('X-'.config('app.name').'-Sign');
        $requests = $request->input();

        if (config('app.debug')) {
            Log::error('sign:'.$header_sign);
        }
        
        @list($sign, $timestamp) = explode(',', $header_sign);

        if ($sign && $timestamp) {
            $right_sign = self::generateSign($requests, $timestamp);

            if (config('app.debug')) {
                Log::error('right_sign:'.$right_sign);
                Log::error('right_timestamp:'.$timestamp);
            }

            if ($sign == $right_sign) {
                if ((time()-$timestamp) > 300) {
                    Log::error('time:'.time());
                    return 'sign_expired';
                }

                return true;
            }
        }

        return false;
    }
}