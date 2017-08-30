<?php

namespace App\Models\v1;

use Log;
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

    /**
     * 微信授权
     * @param $code
     * @return array|bool
     */
    public static function wechatAuth($code)
    {
        $wechat = new Wechat();
        return $wechat->getUser($code);
    }

    public static function auth(array $attributes)
    {
        $userinfo = null;
        switch ($attributes['vendor']) {
            case self::VENDOR_WEIXIN:
                $code = $attributes['code'];
                $wechat = new Wechat();
                $userinfo = $wechat->getUser($code);
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