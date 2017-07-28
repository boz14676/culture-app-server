<?php
/*
 * 热搜 - Eloquent ORM
 */

namespace App\Models\v1;

use App\Models\BaseModel;

class Hotsearch extends BaseModel
{
    protected $table = 'hotsearches';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'article_category_id', // 文章分类对象 ID
        'name',                // 名称
    ];

    protected $with = [];

    public $timestamps = false;

    /**
     * repositories
     *
     * @param int $article_category_id # 文章分类对象 ID
     * @param int $numbers             # 显示数量
     * @return mixed                   # 热搜对象(s)或null
     */
    public static function repositories($article_category_id=0, $numbers=6)
    {
         return self::where('article_category_id', $article_category_id)
            ->orderBy('searching_numbers', 'desc')
            ->take($numbers)
            ->get();
    }

}
