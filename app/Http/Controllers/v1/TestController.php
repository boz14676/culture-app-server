<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Activity;
use App\Models\v1\Article;
use App\Models\v1\ArticleCategory;
use App\Models\v1\Label;
use App\Models\v1\Stadium;
use App\Models\v1\User;
use App\Models\v1\UserIntegral;
use App\Models\v1\Video;
use Carbon\Carbon;
use DB;
use Excel;

class TestController extends Controller
{
    /**
     * GET /urun.test
     */
    public function test()
    {
        // phpinfo();
    }

    public function insertLabels()
    {
        $articles = Article::get();
        $stadiums = Stadium::get();
        $activities = Activity::get();
        $videos = Video::get();

        $labels = collect();
        $labels_relationships = collect();

        $articles->each(function ($article) use (&$labels, &$labels_relationships) {
            if ($article->labels) {
                $original_labels = $article->labels->map(function ($original_label) use ($article) {
                    return [
                        'article_category_id' => $article->articleCategory->top_level_id,
                        'name' => $original_label,
                    ];
                });

                $labels->push($original_labels);
            }


            if ($original_label_ids = Label::whereIn('name', $article->labels)->pluck('id')) {
                $labels_relationship = $original_label_ids->map(function ($original_label_id) use ($article) {
                    return
                        [
                            'label_id' => $original_label_id,
                            'labeable_type' => 'article',
                            'labeable_id' => $article->id,
                        ];
                });
            }

            $labels_relationships->push($labels_relationship);
        });
        $stadiums->each(function ($stadium) use (&$labels, &$labels_relationships) {
            if ($stadium->labels) {
                $original_labels = $stadium->labels->map(function ($original_label) use ($stadium) {
                    return [
                        'article_category_id' => $stadium->articleCategory->top_level_id,
                        'name' => $original_label,
                    ];
                });

                $labels->push($original_labels);
            }

            if ($original_label_ids = Label::whereIn('name', $stadium->labels)->pluck('id')) {
                $labels_relationship = $original_label_ids->map(function ($original_label_id) use ($stadium) {
                    return
                        [
                            'label_id' => $original_label_id,
                            'labeable_type' => 'stadium',
                            'labeable_id' => $stadium->id,
                        ];
                });
            }

            $labels_relationships->push($labels_relationship);
        });

        $activities->each(function ($activity) use (&$labels, &$labels_relationships) {
            if ($activity->labels) {
                $original_labels = $activity->labels->map(function ($original_label) use ($activity) {
                    return [
                        'article_category_id' => $activity->articleCategory->top_level_id,
                        'name' => $original_label,
                    ];
                });

                $labels->push($original_labels);
            }

            if ($original_label_ids = Label::whereIn('name', $activity->labels)->pluck('id')) {
                $labels_relationship = $original_label_ids->map(function ($original_label_id) use ($activity) {
                    return
                        [
                            'label_id' => $original_label_id,
                            'labeable_type' => 'activity',
                            'labeable_id' => $activity->id,
                        ];
                });
            }

            $labels_relationships->push($labels_relationship);
        });

        $videos->each(function ($video) use (&$labels, &$labels_relationships) {

            if ($video->labels) {
                $original_labels = $video->labels->map(function ($original_label) use ($video) {

                    if ($video->videoable instanceof ArticleCategory) {
                        $article_category = $video->videoable;
                    } else {
                        $article_category = $video->videoable->articleCategory;
                    }

                    return [
                        'article_category_id' => $article_category->top_level_id,
                        'name' => $original_label,
                    ];
                });

                $labels->push($original_labels);
            }

            if ($original_label_ids = Label::whereIn('name', $video->labels)->pluck('id')) {
                $labels_relationship = $original_label_ids->map(function ($original_label_id) use ($video) {
                    return
                        [
                            'label_id' => $original_label_id,
                            'labeable_type' => 'video',
                            'labeable_id' => $video->id,
                        ];
                });
            }

            $labels_relationships->push($labels_relationship);
        });

        // DB::table('labeables')->insert($labels_relationships->collapse()->all());

        $out_original_labels = $labels->collapse()->unique()->filter();
        DB::table('labels')->insert($out_original_labels->all());
    }

    public function exportRepository()
    {
        /**
         * stadium-repository
         */
        /*$stadiums = collect();
        Excel::load(storage_path('data_repositories/stadium_repositories2.xlsx'), function($reader) use (&$stadiums) {
            $reader->skipRows(1)->get()->each(function ($item, $key) use (&$stadiums) {
                $item->details = htmlspecialchars($item->details);

                $stadium = collect($item->all())->except(0)->put('details', $item->details);
                $stadiums->push($stadium->all());
            });
        });

        DB::table('stadiums')->insert($stadiums->all());*/

        /**
         * article-repository
         */
        $articles = collect();
        Excel::load(storage_path('data_repositories/article_repositories.xlsx'), function($reader) use (&$articles) {
            $reader->skipRows(1)->get()->each(function ($item, $key) use (&$articles) {
                $item->details = htmlspecialchars($item->details);

                $article = collect($item->all())->except(0)->put('details', $item->details);
                $articles->push($article->all());
            });
        });

        DB::table('articles')->insert($articles->all());
    }

    public function _insetArticle()
    {
        /******************************************** 文章测试开发填充 ********************************************/
        // 文章测试填充
        $article_default_1 = 'http://spdb.wth689.com/public/uploads/20170630/73b4afe0a6d5d799edc53fd2a83efcc4.jpg';

        /*** 文化特色 文章 ***/
        // 文化特色
        $wsts_articles = [
            [
                'article_category_id' => 1,
                'name' => '银饰的起源',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/298bbb86b473516dc8062a0d9ee411b5.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/298bbb86b473516dc8062a0d9ee411b5.png',
                'labels' => '历史文化',
                'location' => '',
                'address' => '',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 1,
                'name' => '最后的匠人',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/dca2d4f47125620ed59e146e339de3e7.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/dca2d4f47125620ed59e146e339de3e7.png',
                'labels' => '非物质文化',
                'location' => '',
                'address' => '',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
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
                'name' => '贵州民族民俗博物馆',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '场馆',
                'location' => '106.762935,26.692892',
                'address' =>'贵州遵义革命路1212号',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
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
                'name' => '安顺地戏',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/54739424cdcae340aed612231749e67f.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/54739424cdcae340aed612231749e67f.png',
                'labels' => '非遗,戏曲',
                'location' => '106.762935,26.692892',
                'address' =>'黔南布依族苗族自治州',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 4,
                'name' => '布依族八音座唱',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/4ae4cb0b23e39bc3aedfeaec1c3a0d68.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/4ae4cb0b23e39bc3aedfeaec1c3a0d68.png',
                'labels' => '非遗,戏曲',
                'location' => '106.762935,26.692892',
                'address' =>'黔南布依族苗族自治州',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
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
                'name' => '青西桥',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/bcab76f3b2bcb6386d9610d96ff67b7c.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/bcab76f3b2bcb6386d9610d96ff67b7c.png',
                'labels' => '桥梁,名胜',
                'location' => '106.762935,26.692892',
                'address' =>'遵义市正安县',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 5,
                'name' => '庄子桥',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/96b0920a571bf9a3d557f032f009b2b6.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/96b0920a571bf9a3d557f032f009b2b6.png',
                'labels' => '桥梁,名胜',
                'location' => '106.762935,26.692892',
                'address' =>'遵义市桐梓县',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ]
        ];

        // 文化特色 - 饮食文化
        $wsts_yswh_articles = [
            [
                'article_category_id' => 6,
                'name' => '脆哨粉',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/01a03580bbb803084cadc863ba1f9b67.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/01a03580bbb803084cadc863ba1f9b67.png',
                'labels' => '饮食',
                'location' => '106.762935,26.692892',
                'address' =>'贵阳市',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 6,
                'name' => '贵阳丝娃娃',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/9c4301eedf0d2bf27bfc5c83784e03a2.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/9c4301eedf0d2bf27bfc5c83784e03a2.png',
                'labels' => '饮食',
                'location' => '106.762935,26.692892',
                'address' =>'贵阳市',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ]
        ];

        // 文化特色 - 茶酒文化
        $wsts_cjwh_articles = [
            [
                'article_category_id' => 7,
                'name' => '贵州茅台酒',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/1114bf02674ff8dc7df72e0dd01b5635.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/1114bf02674ff8dc7df72e0dd01b5635.png',
                'labels' => '茶酒',
                'location' => '106.762935,26.692892',
                'address' =>'仁怀市茅台镇',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 7,
                'name' => '邦噹酒',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/6cb58e7029220c3fc17f4b6e62a164a1.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/6cb58e7029220c3fc17f4b6e62a164a1.png',
                'labels' => '茶酒',
                'location' => '106.762935,26.692892',
                'address' =>'清镇市',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 民族村寨
        $wsts_mzcz_articles = [
            [
                'article_category_id' => 8,
                'name' => '中国土家第一村-贵州云舍',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/707452849dd3d0e9ff47176340a3e777.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/707452849dd3d0e9ff47176340a3e777.png',
                'labels' => '村寨,名镇',
                'location' => '106.762935,26.692892',
                'address' =>'铜仁地区江口县',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 8,
                'name' => '堂安侗寨',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/707452849dd3d0e9ff47176340a3e777.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/707452849dd3d0e9ff47176340a3e777.png',
                'labels' => '村寨,名镇',
                'location' => '106.762935,26.692892',
                'address' =>'黎平县肇兴乡',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 文化名镇
        $wsts_whmz_articles = [
            [
                'article_category_id' => 9,
                'name' => '黔东南丹寨',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/b21a795b25669adfa763cb4b03c86fc7.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/b21a795b25669adfa763cb4b03c86fc7.png',
                'labels' => '名镇,非遗',
                'location' => '106.762935,26.692892',
                'address' =>'贵州省东南部黔东南州',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 9,
                'name' => '黔东南丹寨',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/582e0cfab5abff6d0e4443ee8bb48316.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/582e0cfab5abff6d0e4443ee8bb48316.png',
                'labels' => '名镇,非遗',
                'location' => '106.762935,26.692892',
                'address' =>'贵州省东南部黔东南州',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 名胜古迹
        $wsts_msgj_articles = [
            [
                'article_category_id' => 10,
                'name' => '黄果树风景名胜区',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/4d5b48590ceb0aa0f0617237132715ef.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/4d5b48590ceb0aa0f0617237132715ef.png',
                'labels' => '名胜',
                'location' => '106.762935,26.692892',
                'address' =>'镇宁和关岭两自治县交界处',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 10,
                'name' => '马岭河-万峰湖名胜风景区',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/280208c32dbc9ef4ac30caacac3ec6c9.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/280208c32dbc9ef4ac30caacac3ec6c9.png',
                'labels' => '名胜',
                'location' => '106.762935,26.692892',
                'address' =>'贵州省黔西南州兴义市',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 红色文化
        $wsts_hswh_articles = [
            [
                'article_category_id' => 11,
                'name' => '历史转折中的遵义会议',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/8ccfe671f370fef3c4c1727e681e19fd.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/8ccfe671f370fef3c4c1727e681e19fd.png',
                'labels' => '红色文化,名胜',
                'location' => '106.762935,26.692892',
                'address' =>'贵州省遵义市红花岗区老城红旗路',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 11,
                'name' => '重走长征路',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/8ccfe671f370fef3c4c1727e681e19fd.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/8ccfe671f370fef3c4c1727e681e19fd.png',
                'labels' => '',
                'location' => '106.762935,26.692892',
                'address' =>'追寻革命先烈的足迹',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 历史文化
        $wsts_lswh_articles = [
            [
                'article_category_id' => 12,
                'name' => '黔西观音洞文化遗址',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/cd2954d44cf1f4b735b2aa413b5fa63b.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/cd2954d44cf1f4b735b2aa413b5fa63b.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '史前文化遗迹',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 12,
                'name' => '悠久的贵州历史文化',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '中国古人类发祥地之一',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化特色 - 专题列表
        $wsts_ztlb_articles = [
            [
                'article_category_id' => 1,
                'name' => '城市中的匠人',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '历史文化,非物质文化',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 1,
                'name' => '舌尖上的民族',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '历史文化,非物质文化,饮食文化',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 1,
                'name' => '国宝级艺人',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '历史文化,非物质文化,饮食文化',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];


        /*** 文化服务 文章 ***/
        // 文化服务 - 文化机构
        $whfw_whjg_articles = [
            [
                'article_category_id' => 19,
                'name' => '黔剧院',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/de487c9e3101f14cbdf5f7a8e1bac5f2.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/de487c9e3101f14cbdf5f7a8e1bac5f2.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 19,
                'name' => '贵州省演艺集团',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 19,
                'name' => '省文物保护中心',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 19,
                'name' => '非遗中心',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 19,
                'name' => '贵州省文艺人才培训交流中心',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 19,
                'name' => '省文物考古所',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/53c72d7f1881f627829cf4d2ed2d0f2e.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化服务 - 文化场馆
        $whfw_whcg_articles = [
            [
                'article_category_id' => 20,
                'name' => '市博物馆',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/673e362854c08232158d114e506c9c0f.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/673e362854c08232158d114e506c9c0f.png',
                'labels' => '博物馆',
                'location' => '106.762935,26.692892',
                'address' =>'遵义市红花岗区老城红旗路',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 20,
                'name' => '市博物馆',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170730/a23747a31f8819b534aad0a0f46ce203.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170730/a23747a31f8819b534aad0a0f46ce203.png',
                'labels' => '博物馆',
                'location' => '106.762935,26.692892',
                'address' =>'遵义市红花岗区老城红旗路',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化服务 - 文化团体
        $whfw_whtt_articles = [
            [
                'article_category_id' => 21,
                'name' => '贵州省文化馆乐韵女子合唱团',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/4067896a3c2d4414fd7200161c8d5776.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/4067896a3c2d4414fd7200161c8d5776.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 21,
                'name' => '贵州省文化馆风华艺术团',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/88231e276afdeaea24fb1b630c147249.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/88231e276afdeaea24fb1b630c147249.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 21,
                'name' => '贵州青松合唱团',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/2694671e45d1c2ae10f5aa5b20bf2d17.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/2694671e45d1c2ae10f5aa5b20bf2d17.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 21,
                'name' => '贵阳夕阳红广场舞队',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/9fc60c2b3086edad3d7c39b993e739fb.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/9fc60c2b3086edad3d7c39b993e739fb.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化服务 - 文化活动
        // Todu

        // 文化服务 - 文化团体
        $whfw_wxys_articles = [
            [
                'article_category_id' => 23,
                'name' => '删定武库益智录',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/9fc60c2b3086edad3d7c39b993e739fb.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/9fc60c2b3086edad3d7c39b993e739fb.png',
                'labels' => '历史古籍',
                'location' => '',
                'address' =>'',
                'desc' => '明代',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 23,
                'name' => '《八阵合变说》',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/88231e276afdeaea24fb1b630c147249.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/88231e276afdeaea24fb1b630c147249.png',
                'labels' => '历史古籍',
                'location' => '',
                'address' =>'',
                'desc' => '明代',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化服务 - 精准扶贫
        $whfw_jzfp_articles = [
            [
                'article_category_id' => 24,
                'name' => '产业扶贫助力贫困村 变成现代农业示范区',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/12c03e1e90013a5f702ffbb55620fe96.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/12c03e1e90013a5f702ffbb55620fe96.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2017-09-02',
            ],
            [
                'article_category_id' => 24,
                'name' => '投资12亿元实施交通扶贫',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2017-04-16',
            ],
            [
                'article_category_id' => 24,
                'name' => '产业扶贫助力贫困村',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2016-05-01',
            ],
            [
                'article_category_id' => 24,
                'name' => '现代农业示范区',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2016-04-02',
            ],
        ];

        // 文化服务 - 文化政务
        $whfw_whzw_articles = [
            [
                'article_category_id' => 25,
                'name' => '找准文化定位　明确文化担当　开启文化新路——“十三五”多彩贵州民族特色文化强省建设迈出坚实步履',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/12c03e1e90013a5f702ffbb55620fe96.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/12c03e1e90013a5f702ffbb55620fe96.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2017-09-02',
            ],
            [
                'article_category_id' => 25,
                'name' => '贵州省文化厅、贵州省公安厅关于转发《文化部公安部关于进一步加强游戏游艺场所监管促进行业健康发展的通知》的通知',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/3b8324555c1be2c7eba6ede8e0484561.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2017-04-16',
            ],
            [
                'article_category_id' => 25,
                'name' => '贵州省文化厅2017年部门预算及“三公经费”预算信息',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2016-05-01',
            ],
            [
                'article_category_id' => 25,
                'name' => '2016年贵州省文化厅行政审批权力清单',
                'thumbnail' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'banner' => 'http://spdb.wth689.com/public/uploads/20170731/e3c8b6041d4012ed1dd9c73b3c393ff8.png',
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => '2016-04-02',
            ],
        ];

        // 文化服务 - 全国文化共享
        $whfw_qgwhgx_articles = [
            [
                'article_category_id' => 26,
                'name' => '大头儿子和小头爸爸',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '动画',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 26,
                'name' => '小鬼当家4',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '动画',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 26,
                'name' => '熊大大',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '动画',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化服务 - 数字图书馆

        // Tudo


        /*** 文化产业 文章 ***/

        $thumbnail = 'http://appapi.pzjhw.com/file/photos/user/avator-default.png';

        // 文化产业
        $whcy_articles = [
            [
                'article_category_id' => 2,
                'name' => '动画产业的崛起',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '动画娱乐',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 2,
                'name' => '大山里的手艺人',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '非遗大师',
                'location' => '',
                'address' =>'',
                'desc' => '',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ]
        ];

        // 文化产业 - 非遗大师
        $whcy_fyds_articles = [
            [
                'article_category_id' => 13,
                'name' => '张中军',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '纸雕大师，曾获得全国纸雕大赛金奖',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 13,
                'name' => '李云芳',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '银饰工艺大师',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 13,
                'name' => '李天健',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '石刻大师，曾获得全国大赛金奖',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 13,
                'name' => '周力莉',
                'thumbnail' => $thumbnail,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '国画大师，国家美术协会会员',
                'is_hot' => '',
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];

        // 文化产业 - 竞技场
        $whcy_jjc_articles = [
            [
                'article_category_id' => 13,
                'name' => '斗鸡',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '贵州民俗活动',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
            [
                'article_category_id' => 13,
                'name' => '贵州国际斗鸡比赛',
                'thumbnail' => $article_default_1,
                'banner' => $article_default_1,
                'labels' => '',
                'location' => '',
                'address' =>'',
                'desc' => '苗族人民的优良传统',
                'is_hot' => 1,
                'is_guess' => '',
                'has_commented_numbers' => '',
                'has_liked_numbers' => '',
                'has_read_numbers' => '',
                'details' => '',
                'extra' => '',
                'url' => '',
                'timed_at' => null,
            ],
        ];


        $total_articles = array_merge(
            $wsts_articles,$wsts_hot_articles,$wsts_fywh_articles,$wsts_qlwh_articles,$wsts_yswh_articles,$wsts_cjwh_articles,$wsts_mzcz_articles,$wsts_whmz_articles,$wsts_msgj_articles,$wsts_hswh_articles,$wsts_lswh_articles,$wsts_ztlb_articles,$whfw_whjg_articles,$whfw_whcg_articles,$whfw_whtt_articles,$whfw_wxys_articles,$whfw_whtt_articles,$whfw_jzfp_articles,$whfw_whzw_articles,$whfw_qgwhgx_articles,$whcy_fyds_articles,$whcy_articles,$whcy_jjc_articles,$whcy_fyds_articles
        );

        foreach ($total_articles as &$value) {
            $location = array_filter(explode(',', $value['location']));
            $value['lat'] = $location ? $location[0] : '';
            $value['lng'] = $location ? $location[1] : '';
            unset($value['location']);
        }

        DB::table('articles')->insert($total_articles);
    }

    public function _insertArticleCategory()
    {
        /************************* 文章分类 *************************/

        /*$banner = '	http://spdb.wth689.com/public/uploads/20170730/7f937e73e30693e5b1546476b167de60.png';
        // 非遗文化
        $categories['fywh_category'] = [
            'id' => 4,
            'desc' => '非物质文化遗产既是历史发展的见证，又是珍贵的、具有重要价值的文化资源。',
            'banner' => $banner,
        ];

        // 桥梁文化
        $categories['qlwh_category'] = [
            'id' => 5,
            'desc' => '桥文化是指有关于桥梁的文化。中国是桥文化的故乡，自古就有桥的国度之称，发展于隋，兴盛于宋。遍布在神州大地的桥、编织成四通八达的交通网络，连接着祖国的四面八方。',
            'banner' => $banner,
        ];

        // 饮食文化
        $categories['yswh_category'] = [
            'id' => 6,
            'desc' => '民以食为天，世界上任何一个国家都有一个传统的饮食文明与其它文明共同在历史中轮回。每个地区都有与众不同的饮食习惯和味觉倾向，而各自将这些精妙的技艺发展成了一种习俗，一种文化，这使得无数食客流连在世界的每一个角落。',
            'banner' => $banner,
        ];

        // 茶酒文化
        $categories['cjwh_category'] = [
            'id' => 7,
            'desc' => '茶酒文化作为一种特殊的文化形式，在传统的中国文化中有其独特的地位。在几千年的文明史中，几乎渗透到社会生活中的各个领域',
            'banner' => $banner,
        ];

        // 民族村寨
        $categories['mzcz_category'] = [
            'id' => 8,
            'desc' => '少数民族特色村寨是指少数民族世居的、拥有较多少数民族元素的、被现代文化破坏较少的传统村寨。就物质文化而言，少数民族特色村寨的建筑、服饰、饮食等文化元素保存得比较好。',
            'banner' => $banner,
        ];

        // 文化名镇
        $categories['whmz_category'] = [
            'id' => 9,
            'desc' => '中国历史文化名镇是由住房和城乡建设部和国家文物局共同组织评选的，保存文物特别丰富，且具有重大历史价值或纪念意义的，能较完整地反映一些历史时期传统风貌和地方民族特色的镇。',
            'banner' => $banner,
        ];

        // 名胜古迹
        $categories['msgj_category'] = [
            'id' => 10,
            'desc' => '贵州的名胜古迹众多，漫步在这些名山胜水之中，一方面可以领略祖国的大好河山，另一方面也从中感悟祖国博大精深的历史文化。',
            'banner' => $banner,
        ];


        // 红色文化
        $categories['hswh_category'] = [
            'id' => 11,
            'desc' => '红色文化是在革命战争年代，由中国共产党人、先进分子和人民群众共同创造并极具中国特色的先进文化，蕴含着丰富的革命精神和厚重的历史文化内涵。',
            'banner' => $banner,
        ];

        // 历史文化
        $categories['lswh_category'] = [
            'id' => 12,
            'desc' => '贵州是我国古人类发祥地之一，早在若干万年前就有人类劳动、生息、繁衍在这块土地上， 创造了贵州的远古文化。春秋以前，贵州为荆州西南裔，属于"荆楚"或"南蛮"的一部分。',
            'banner' => $banner,
        ];

        collect($categories)->map(function ($category_data) {
            $category_data = collect($category_data);
            DB::table('article_categories')
                ->where('id', $category_data->get('id'))
                ->update($category_data->except('id')->all());
        });*/
    }
}
