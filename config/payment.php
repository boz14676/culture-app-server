<?php

return [

    'channel' => [
        /**
         * 支付宝手机支付
         */
        'alipay_app' => [
            'name'        => '支付宝手机支付',
            'enabled'     => env('ALIPAY_APP_ENABLED', false),
            'app_id'      => env('ALIPAY_APP_ID', ''),
            'app_ver'     => env('ALIPAY_APP_VER', 'v1'),
            'partner_id'  => env('ALIPAY_APP_PARTNER_ID', ''),
            'seller_id'   => env('ALIPAY_APP_SELLER_ID', ''),
            'public_key'  => env('ALIPAY_APP_PUBLIC_KEY', ''),
            'private_key' => env('ALIPAY_APP_PRIVATE_KEY', ''),
            'sign_type'   => env('ALIPAY_APP_SIGN_TYPE', 'RSA'),
        ],

        /**
         * 支付宝手机支付
         */
        'alipay_web' => [
            'name'        => '支付宝网页支付',
            'enabled'     => env('ALIPAY_WEB_ENABLED', false),
            'app_id'      => env('ALIPAY_WEB_ID', ''),
            'app_ver'     => env('ALIPAY_WEB_VER', 'v1'),
            'partner_id'  => env('ALIPAY_WEB_PARTNER_ID', ''),
            'seller_id'   => env('ALIPAY_WEB_SELLER_ID', ''),
            'public_key'  => env('ALIPAY_WEB_PUBLIC_KEY', ''),
            'private_key' => env('ALIPAY_WEB_PRIVATE_KEY', ''),
        ],

        /**
         * 微信手机支付
         */
        'wxpay_app' => [
            'name'       => '微信手机支付',
            'enabled'    => env('WXPAY_APP_ENABLED', false),
            'app_id'     => env('WXPAY_APP_APP_ID', ''),
            'app_secret' => env('WXPAY_APP_APP_SECRET', ''),
            'mch_id'     => env('WXPAY_APP_MCH_ID', ''),
            'mch_key'    => env('WXPAY_APP_MCH_KEY', ''),
        ],

        /**
         * 微信公号支付
         */
        'wxpay_web' => [
            'name'       => '微信公号支付',
            'enabled'    => env('WXPAY_WEB_ENABLED', false),
            'app_id'     => env('WXPAY_WEB_APP_ID', ''),
            'app_secret' => env('WXPAY_WEB_APP_SECRET', ''),
            'mch_id'     => env('WXPAY_WEB_MCH_ID', ''),
            'mch_key'    => env('WXPAY_WEB_MCH_KEY', ''),
        ],
    ],

    'notify_host' => env('NOTIFY_HOST', ''),
    'apply_api' => env('APPLY_API', ''),
    'apply_host' => env('APPLY_HOST', ''),
    'apply_key' => env('APPLY_KEY', ''),

];