<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use App\Services\Oauth\Wechat;

class Social extends BaseModel
{
    const VENDOR_WEIXIN = 1;
    const VENDOR_WEIBO  = 2;
    const VENDOR_QQ     = 3;
    const VENDOR_TAOBAO = 4;
    const VENDOR_WXA    = 5;    //微信小程序

    const GENDER_SECRET = 0;
    const GENDER_MALE   = 1;
    const GENDER_FEMALE = 2;

    public static function auth(array $attributes)
    {
        $userinfo = null;
        switch ($attributes['vendor']) {
            case self::VENDOR_WEIXIN:
                $wechat = new Wechat();
                $userinfo = $wechat->getUserInfo(
                    $attributes['code'],
                    $attributes['openid']
                );
                break;

            case self::VENDOR_WEIBO:
                $userinfo = self::getUserByWeibo($attributes['access_token'], $attributes['open_id']);
                break;

            case self::VENDOR_QQ:
                $userinfo = self::getUserByQQ($attributes['access_token'], $attributes['open_id']);
                break;

            case self::VENDOR_TAOBAO:
                return false;
                break;
            case self::VENDOR_WXA:
                $wxainfo = self::getUserByWXA($attributes['js_code']);
                Log::error('wxainfo: '.var_export($wxainfo,true));
                if($wxainfo)
                {
                    $open_id = $wxainfo['openid'];
                    $session_key = $wxainfo['session_key'];
                    $userinfo['prefix'] = 'wxa';
                    $userinfo['avatar'] = '';
                    $userinfo['gender'] = 0;
                    $userinfo['nickname'] = 'wxa_'+$wxainfo['openid'];
                }

                break;
            default:
                return false;
                break;
        }

        dd($userinfo);

    }


}