<?php

$app->get('weapi/push', ['namespace' => 'App\Http\Controllers\v1', 'uses' => 'v1\WePushController@valid']);

$app->get('/', function () use ($app) {
    return $app->version();
});



// 第三方服务接口
$app->group(['namespace' => 'App\Http\Controllers\v1', 'middleware' => ['xss']], function($app)
{
    $app->post('git/push', 'GitPushController@push');
});


// Api - Guest
$app->group(['namespace' => 'App\Http\Controllers\v1','prefix' => 'v1', 'middleware' => ['xss']], function($app)
{
    $app->get('article_categories', 'ArticleController@10001'); // 获取文章类别(s)
    $app->get('articles', 'ArticleController@_lists'); // 获取文章(s)
});

// Api - Authorization
$app->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['token', 'xss']], function($app)
{
    /* 绑定手机号 */


});
