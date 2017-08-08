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
    $app->get('home_sections', 'OthersController@getHomeSections');   // 获取首页推荐栏目(s)
    $app->get('areas', 'OthersController@getAreas');                  // 获取区域(s)


    /**************************************************** 用户 **********************************************/
    $app->post('user/code', 'UserController@sendCode');      // 发送验证码
    $app->post('user/register', 'UserController@register');  // 注册
    $app->post('user/login', 'UserController@login');         // 登录

    $app->get('user', 'UserController@get');
    $app->get('user/{attribute:agree|refuse}/update', 'UserController@update');

    /**************************************************** 场馆 **********************************************/
    $app->get('stadiums', 'StadiumController@_lists');
    $app->get('stadium/{id}', 'StadiumController@get');

    /**************************************************** 活动 **********************************************/
    $app->get('activities', 'ActivityController@_lists'); // 活动列表
    $app->get('activity/{id}', 'ActivityController@get'); // 活动详情

    /**************************************************** Media **********************************************/
    $app->get('videos', 'VideoController@_lists'); // 获取视频列表
    $app->get('video/{id}', 'VideoController@get'); // 获取视频详情
    $app->get('musics', 'MusicController@_lists'); // 音乐列表
    $app->get('music/{id}', 'MusicController@get'); // 获取音乐详情
    $app->get('photos', 'PhotoController@_lists'); // 获取图片列表


    /**************************************************** 评论 **********************************************/
    $app->get('comments', 'CommentController@_lists'); // 评论列表
    $app->get('likes', 'LikesController@_lists'); // 点赞列表
});

// Api - Authorization
$app->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['auth', 'xss']], function($app)
{
    /**************************************************** 用户 **********************************************/
    /**** 修改密码 ****/
    $app->get('user/check_original_password', 'UserController@chekcOriginalPassword');   // 验证原始密码
    $app->put('user/password/update', 'UserController@updatePassword');                  // 修改密码

    $app->get('user', 'UserController@get');                                             // 获取用户
    $app->get('user/comments', 'UserController@commentLists');                           // 用户的评论列表
    $app->post('user/comment', 'UserController@writeComment');                           // 写评论
    $app->post('user/like', 'UserController@likes');                                     // 点赞
    $app->delete('user/like', 'UserController@unlikes');                                 // 取消点赞

    /**** 积分 ****/
    $app->get('integral_tasks', 'IntegralController@getIntegralTasks');                  // 获取积分任务(s)
    $app->get('user/integrals', 'IntegralController@getIntegrals');                      // 获取用户积分记录(s)

    $app->get('user/collects', 'UserController@getUserCollects');                        // 获取用户的收藏(s)
    $app->post('user/collect', 'UserController@UserCollects');                           // 收藏
    $app->delete('user/collect', 'UserController@UserUncollects');                       // 取消收藏

    $app->post('user/identification', 'UserController@identifies');                      // 提交实名认证
    $app->post('user/feedback', 'UserController@postFeedback');                          // 意见反馈


    /**************************************************** 订单 **********************************************/
    $app->get('user/order/{id}', 'OrderController@get');     // 获取订单
    $app->get('user/orders', 'OrderController@_lists');      // 获取订单(s)

    // **************************************** 购物 ***************************************
    $app->post('shopping/orders', 'ShoppingController@orders');  // 下单
    $app->post('shopping/pays', 'ShoppingController@pays');      // 付款
});
