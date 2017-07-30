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
                'icon' => $native_article_category->url
                    ? 'http://spdb.wth689.com' . $native_article_category->url
                    : '',
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


        /******************************************** 文章测试开发填充 ********************************************/
        // 文章测试填充
        $article_default_1 = '/public/uploads/20170630/73b4afe0a6d5d799edc53fd2a83efcc4.jpg';

        /*** 文化特色 文章 ***/
        // 文化特色
        $wsts_articles = [
            [
                'article_category_id' => 1,
                'is_activity' => '',
                'name' => '银饰的起源',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'label' => '历史文化',
                'location' => '',
                'address' => '',
                'desc' => '',
                'is_hot' => 0,
                'is_guess' => '',
                'comment_numbers' => '',
                'like_numbers' => '',
                'reading_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 1,
                'is_activity' => '',
                'name' => '最后的匠人',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'label' => '非物质文化',
                'location' => '',
                'address' => '',
                'desc' => '',
                'is_hot' => 0,
                'is_guess' => '',
                'comment_numbers' => '',
                'like_numbers' => '',
                'reading_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];
        // 文化特色【热门】
        $wsts_hot_articles = [
            [
                'article_category_id' => 1,
                'is_activity' => '',
                'name' => '贵州民族民俗博物馆',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'label' => '场馆',
                'location' => '106.762935,26.692892',
                'address' =>'贵州遵义革命路1212号',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'comment_numbers' => '',
                'like_numbers' => '',
                'reading_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 非遗文化
        $wsts_fywh_articles = [
            [
                'article_category_id' => 4,
                'is_activity' => '',
                'name' => '安顺地戏',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'label' => '非遗,戏曲',
                'location' => '106.762935,26.692892',
                'address' =>'黔南布依族苗族自治州',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'comment_numbers' => '',
                'like_numbers' => '',
                'reading_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ]
        ];

        // 文化特色 - 桥梁文化
        $wsts_qlwh_articles = [
            [
                'article_category_id' => 5,
                'is_activity' => '',
                'name' => '青西桥',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'label' => '桥梁,名胜',
                'location' => '106.762935,26.692892',
                'address' =>'遵义市正安县',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'comment_numbers' => '',
                'like_numbers' => '',
                'reading_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ]
        ];



        /*$wsts_total_articles = array_merge($wsts_articles, $wsts_hot_articles, $wsts_up_articles->all());
        DB::table('articles')->insert($wsts_total_articles);*/

    }
}
