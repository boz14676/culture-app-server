<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use db;

class TestController extends Controller
{
    /**
     * GET /urun.test
     */
    public function test()
    {
        // 文章分类
        /*$article_categoires = collect();
        $native_article_categories = collect(db::connection('native')->table('cloud_culture_title')->get());
        $native_article_categories->map(function ($native_article_category) use (&$article_categoires) {
            $article_category = [
                'name' => $native_article_category->name,
                'topid' => $native_article_category->pid,
            ];
            $article_categoires->push($article_category);
        });
        db::table('article_categories')->insert($article_categoires->all());*/

        // 文章
        /*$articles = collect();
        $native_articles = collect(db::connection('native')->table('cloud_cultural_content')->get());
        $native_articles->map(function ($native_article) use ($articles) {
            $article = [
                'article_category_id' => $native_article->pid,
                'name' => $native_article->title,
                'thumbnail' => $native_article->cover,
                'item' => implode(',', explode('，', $native_article->label)),
                'details' => $native_article->content
            ];

            $articles->push($article);
        });

        db::table('articles')->insert($articles->all());*/

        // 热搜
        /*$hotsearches = [
            ['article_category_id' => 1, 'name' => '舌尖上的村寨'],
            ['article_category_id' => 1, 'name' => '非物质文化遗产'],
            ['article_category_id' => 1, 'name' => '村寨文化起源'],
            ['article_category_id' => 1, 'name' => '文化名城'],
            ['article_category_id' => 1, 'name' => '革命故居'],
            ['article_category_id' => 1, 'name' => '村寨文化起源'],
        ];
        db::table('hotsearches')->insert($hotsearches);*/
    }
}
