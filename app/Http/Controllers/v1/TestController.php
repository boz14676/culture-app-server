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
            $data = [
                'level' => $native_article_category->level,
            ];
            db::table('article_categories')->where('id', $native_article_category->id)->update($data);
        });*/

        // 文章
        /*$articles = collect();
        $native_articles = collect(db::connection('native')->table('cloud_cultural_content')->get());
        $native_articles->map(function ($native_article) use ($articles) {
            $data = [
                'address' => $native_article->address
            ];

            db::table('articles')->where('id', $native_article_category->id)->update($data);

        });*/


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
