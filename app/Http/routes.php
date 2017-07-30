<?php

$app->get('test', ['namespace' => 'App\Http\Controllers\v1', 'uses' => 'v1\TestController@test']); // 测试使用

$app->get('/', function () use ($app) {
    return $app->version();
});



// Api - Guest
$app->group(['namespace' => 'App\Http\Controllers\v1','prefix' => 'v1', 'middleware' => ['xss']], function($app)
{
    /**************************************************** 文章模块 **********************************************/
    $app->get('article_categories', 'ArticleController@categories');  // 获取文章类别(s)
    $app->get('articles', 'ArticleController@_lists');                // 获取文章(s)
    $app->get('article/{id}', 'ArticleController@get');               // 获取文章

    /**************************************************** Others **********************************************/
    $app->get('hotsearches', 'OthersController@getHotsearches');      // 获取热搜(s)
    $app->get('home_sections', 'OthersController@getHomeSections');   // 获取首页推荐栏目

    /**************************************************** 图片资源库 **********************************************/
    $app->get('photos', 'PhotoController@_lists'); // 获取图片(s)

    /**************************************************** 用户 **********************************************/
    $app->post('user/code', 'UserController@sendCode');      // 发送验证码
    $app->post('user/register', 'UserController@register');  // 注册
    $app->get('user/login', 'UserController@login');         // 登录

    $app->get('user', 'UserController@get');
    $app->get('user/{attribute:agree|refuse}/update', 'UserController@update');

    /**************************************************** 活动 **********************************************/
    $app->get('activities', 'ActivityController@_lists');
});

// Api - Authorization
$app->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['auth', 'xss']], function($app)
{
    /**************************************************** 用户 **********************************************/
    /** 修改密码 **/
    $app->get('user/check_original_password', 'UserController@chekcOriginalPassword'); // 验证原始密码
    $app->put('user/password/update', 'UserController@updatePassword');                // 修改密码

    $app->get('user', 'UserController@get');                                      // 获取用户

    /**************************************************** 订单 **********************************************/
    $app->get('order/{id}', 'OrderController@get');     // 获取订单
    $app->get('orders', 'OrderController@_lists');      // 获取订单(s)

    // **************************************** 购物 ***************************************
    $app->post('shopping/orders', 'ShoppingController@orders');  // 下单
    $app->post('shopping/pays', 'ShoppingController@pays');      // 付款
});
