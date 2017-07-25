<?php

$app->get('weapi/push', ['namespace' => 'App\Http\Controllers\v2', 'uses' => 'v2\WePushController@valid']);

$app->get('/', function () use ($app) {
    return $app->version();
});


// 第三方服务接口
$app->group(['namespace' => 'App\Http\Controllers\v2', 'middleware' => ['xss']], function($app)
{
    /* 维赛回调接口 */
    $app->post('ws.result-events.insert', 'WsPushController@insertEvents'); // 成绩回调接口
    $app->post('ws.photo.uploads', 'WsPushController@uploadsWs'); // 上传图片回调接口
    $app->post('ws.results.insert', 'WsPushController@insertResults'); // 上传图片回调接口
    
    /* BTV回调接口 接收视频 */
    $app->post('urun.video.receive', [
        'as' => 'video.receive',
        'uses' => 'VideoController@receiveVideo',
    ]);
});


// Api - Guest
$app->group(['namespace' => 'App\Http\Controllers\v2','prefix' => 'v2', 'middleware' => ['xss']], function($app)
{
    /* 小程序授权 */
    $app->post('urun.auth.social', 'UserController@weappAuth');
    
    /* 发送手机验证码 */
    $app->post('urun.user.mobile.send', 'UserController@sendVerifyCode');
    
    /* JSDK */
    $app->post('urun.config.get', 'ConfigController@index');
    
    /* 微信OAuth授权 */
    $app->get('urun.auth.web', 'WePullController@webOauth');
    $app->get('urun.auth.web.callback', 'WePullController@webCallback');
    
    $app->post('urun.test', 'H5Controller@test'); // 测试
});

// Api - Authorization
$app->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers\v2', 'middleware' => ['token', 'xss']], function($app)
{
    /* 绑定手机号 */
    $app->post('urun.user.mobile.bind', 'UserController@bind_mobile');
    
    /* 获取用户的信息 */
    $app->post('urun.user.profile.get', 'UserController@get');
    
    /* 赛事 -> 成绩、照片、视频 */
    $app->post('urun.race.lists', 'RaceController@lists'); // 获取赛事列表
    $app->post('urun.race.get', 'RaceController@get'); // 获取赛事详情
    $app->post('urun.user.race.lists', 'UserController@raceLists'); // 获取用户参加的赛事
    
    $app->post('urun.result.get', 'ResultController@getWithUser'); // 获取赛事成绩
    
    $app->post('urun.photo.lists', 'PhotoController@lists'); // 获取照片列表
    $app->post('urun.photo.upload', 'PhotoController@uploads'); // 上传图片
    
    $app->post('urun.video.lists', 'VideoController@listsWithUser'); // 视频列表
    
    /* 生成、删除视频 */
    $app->post('urun.video.create', 'VideoController@createVideo'); // 生成视频
    $app->post('urun.video.delete', 'VideoController@deleteVideos'); // 删除视频
    
});
