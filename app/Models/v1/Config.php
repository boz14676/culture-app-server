<?php

namespace App\Models\v1;

use App\Models\BaseModel;

use App\Services\Other\JSSDK;

class Config extends BaseModel
{
    protected $connection = 'shop';
    
    protected $table = 'config';

    protected $guarded = [];

    public  $timestamps   = true;

    public static function getList()
    {

        return self::formatBody(['config' => self::formatConfig()]);
    }

    private static function formatConfig()
    {
        //wxpay.web jssdk
        $jssdk = new JSSDK(env('app_id'), env('app_secret'));
        $arr = $jssdk->GetSignPackage();

        if(is_array($arr)){
            $body['wxpay.web'] = $arr;
        }

        return $body;
    }

}
