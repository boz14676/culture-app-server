<?php
/*
 * 文章 - Eloquent ORM
 */

namespace App\Models\v1;

use App\Models\BaseModel;
use DB;

class Article extends BaseModel
{
    protected $table = 'articles';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'article_category_id',  // [文章类别] 对象的ID
        'name',                 // 名称（包含：姓名）
        'thumbnail',            // 缩略图（包含：大师头像）
        'banner',               // banner（包含：背景图）
        'item',                 // 标签
        'distance',             // 距离（m）*可做排序使用属性
        'location',             // 内容所在地
        'desc',                 // 用于：内容描述、专题简介、内容年代、主题、个人简介
        'timed_at',             // 时间
        'activity_numbers',     // 活动数量
        'is_hot',               // 是否热门 [type: boolean(0, 1)] *仅做搜索使用的属性
        'comment_numbers',      // 评论数量 *可用作做排序使用的属性
        'like_numbers',         // 点赞数量 *可用作排序使用的属性
        'reading_numbers',      // 阅读量 *仅做排序使用的属性
        'details'               // 内容
    ];

    protected $with = [];

    protected $dates = ['timed_at', 'created_at', 'updated_at'];

    protected $casts = [
        'is_hot' => 'integer',
        'activity_numbers' => 'integer',
        'comment_numbers' => 'integer',
        'like_numbers' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        DB::enableQueryLog();
    }

    /**
     * repositories
     *
     * @param int $article_category_id  # 文章分类ID
     * @param int $per_page             # 每页显示记录数
     * @param array $s                  # 排序
     * @param array $q                  # 筛选
     * @return mixed                    # 文章对象(s)或null
     */
    public static function repositories($article_category_id=0, $per_page=10, $s=[], $q=[])
    {
         return self::where('article_category_id', $article_category_id)
            ->orderBy('id', 'desc')
            // 排序
            ->when($s, function ($query) use ($s) {
                collect($s)->map(function ($item, $key) use (&$query) {
                    $query->orderBy($key, $item);
                });

                return $query;
            })
            // 筛选
            ->when($q, function ($query) use ($q) {
                collect($q)->map(function ($item, $key) use (&$query) {
                    $query->where($key, $item);
                });

                return $query;
            })

            ->simplePaginate($per_page);
    }

    // 获取[缩略图] 属性
    public function getThumbnailAttribute($value)
    {
        return 'http://spdb.wth689.com/' . $value;
    }

    // 获取[内容] 属性
    public function getDetailsAttribute($value)
    {
        return html_entity_decode($value);
    }

}
