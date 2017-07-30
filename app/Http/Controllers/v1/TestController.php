<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use DB;

class TestController extends Controller
{
    /**
     * GET /urun.test
     */
    public function test()
    {
        // 文章分类 数据填充 -更新
        /*$article_categoires = collect();
        $native_article_categories = collect(db::connection('native')->table('cloud_culture_title')->get());
        $native_article_categories->map(function ($native_article_category) use (&$article_categoires) {
            $data = [
                'icon' => 'http://spdb.wth689.com' . $native_article_category->url,
            ];
            db::table('article_categories')->where('id', $native_article_category->id)->update($data);
        });*/

        // 文章 数据填充
        /*$articles = collect();
        $native_articles = collect(db::connection('native')->table('cloud_cultural_content')->get());
        $native_articles->map(function ($native_article) use ($articles) {
            $article = [
                'name' => $native_article->title,
                'label' => implode(',', explode('，', $native_article->label)),
                'details' => $native_article->content,
                'address' => $native_article->address,
                'thumbnail' => $native_article->cover,
                'banner' => $native_article->url,
                'article_category_id' => $native_article->pid,
            ];

            $articles->push($article);
        });

        DB::table('articles')->insert($articles->all());*/


        // 热搜 数据填充
        /*$hotsearches = [
            ['article_category_id' => 1, 'name' => '舌尖上的村寨'],
            ['article_category_id' => 1, 'name' => '非物质文化遗产'],
            ['article_category_id' => 1, 'name' => '村寨文化起源'],
            ['article_category_id' => 1, 'name' => '文化名城'],
            ['article_category_id' => 1, 'name' => '革命故居'],
            ['article_category_id' => 1, 'name' => '村寨文化起源'],
        ];
        db::table('hotsearches')->insert($hotsearches);*/

        // 文化场馆 数据填充
        /*$data = ['extra' => json_encode([
            'opening_hours' => '10:00-18:00',
            'contact' => '0711-6666666',
            'transport' => '12路、123路、B765',
        ])];
        db::table('articles')->where('article_category_id', 33)->update($data);*/
    }
}
