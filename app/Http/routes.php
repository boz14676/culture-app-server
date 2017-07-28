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
    $app->get('hotsearches', 'OthersController@getHotsearches');                 // 获取热搜(s)
    $app->get('home_sections', 'OthersController@getHomeSections');   // 获取首页推荐栏目
});

// Api - Authorization
$app->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\v1', 'middleware' => ['token', 'xss']], function($app)
{

});
