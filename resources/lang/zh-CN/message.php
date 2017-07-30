<?php

return [
    'token' => [
        'invalid' => 'Token 无效',
        'expired' => 'Token 过期'
    ],
    'sign' => [
        'invalid' => 'Sign 无效',
        'expired' => 'Sign 过期'
    ],
    'user' => [
        'auth_error' => 'OAuth授权失败',
        'verify_code_error' => '验证码输入错误',
        'send_code_error' => '验证码发送失败，请稍后重试',
        'mobile_not_found' => '您输入的手机号不存在',
        'password_wrong' => '您输入的密码和手机号不匹配',
        'user_not_found' => '用户未找到',
        'original_password_wrong' => '您的输入的原始密码不正确',
        'same_original_password' => '新密码不可与原始密码相同'
    ],
    'photo' => [
    ],
    'shopping' => [
        'goods_not_found' => '商品没有找到',
        'stockout' => '商品已售光',
        'purchase_limitation' => '抱歉，您购买的数量已经超过限购的购买数量'
    ],
    'error' => [
        'user_id' => '用户ID',

        'unknown' => '未知错误',
        '404'     => '您请求的资源不存在',
        'unauthorized' => '没有权限',
        'request_encrypt' => '请求的参数加密错误',

        'cannot_found_result' => '没有找到当前比赛的用户成绩',
        'cannot_matching_result' => '没有该设备或符合该设备的站点成绩',
        'upload_failed' => '上传文件失败'
    ]
];
