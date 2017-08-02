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

    protected $appends = [
        'activity_numbers',                 // 活动数量
        'client_timed_at',                  // 时间
        'original_article_category'         // 文章分类对象
    ];

    protected $visible = [
        'id',
        'original_article_category',        // 文章分类
        'name',                             // 名称（包含：姓名）
        'thumbnail',                        // 缩略图（包含：大师头像）
        'banner',                           // banner（包含：背景图）
        'labels',                           // 标签
        'distance',                         // 距离（m）*可做排序使用属性
        'lat',                              // 坐标 经纬
        'lng',                              // 坐标 纬度
        'address',                          // 地址
        'desc',                             // 用于：内容描述、专题简介、内容年代、主题、个人简介
        'activity_numbers',                 // 活动数量
        'has_commented_numbers',            // 评论数量 *可用作做排序使用的属性
        'has_liked_numbers',                // 点赞数量 *可用作排序使用的属性
        'has_read_numbers',                 // 阅读量 *仅做排序使用的属性
        'client_timed_at',                  // 时间
        'extra',                            // 扩展字段

        'has_photos',                       // 是否有图片 [type: boolean(0, 1)]
        'has_videos',                       // 是否有视频 [type: boolean(0, 1)]
    ];

    protected $with = [];

    protected $dates = ['timed_at', 'created_at', 'updated_at'];

    protected $casts = [
        'is_hot' => 'integer',
        'activity_numbers' => 'integer',
        'comment_numbers' => 'integer',
        'like_numbers' => 'integer',
    ];

    /**
     * repositories
     *
     * @param int $per_page # 每页显示记录数
     * @param array $q # 筛选
     * @param array $s # 排序
     * @return mixed                    # 文章对象(s)或null
     */
    public static function repositories($per_page = 10, $q = [], $s = [])
    {
        /**
         * 显示在一级文章分类下的 [ 附近推荐 ] 或 [ 近期热门 ]
         * 逻辑实现：获取当前一级文章分类下的所有子分类，根据这些分类去查找符合条件的记录
         */
        if (
            isset($q['article_category_id'])
            &&
            (
                isset($q['is_hot'])
                || isset($q['is_hot2'])
                || isset($q['is_guess'])
            )
        )
        {
            $article_category = ArticleCategory::find($q['article_category_id']);                   // 获取文章分类对象
            $subclasses_id = collect($article_category->subclasses_id);                             // 文章分类对象 所有子类的ID
            $q['article_category_id'] = $subclasses_id->push($article_category->attributes['id']);  // 文章分类ids
        }

        // 实现附近推荐
        if (
            isset($q['article_category_id']) &&
            isset($s['distance'])
        )
        {
            $article_category = ArticleCategory::find($q['article_category_id']);                   // 获取文章分类对象
            $subclasses_id = collect($article_category->subclasses_id);                             // 文章分类对象 所有子类的ID

            $q['article_category_id'] = $subclasses_id->push($article_category->attributes['id']);  // 文章分类ids
            $q['expression'] = [
                ['whereNotNull', ['lat']],
                ['whereNotNull', ['lng']]
            ];                                                                                      // 过滤掉为空的经纬度

            $user_lat = app('request')->input('user_lat');                                    // 用户坐标精度
            $user_lng = app('request')->input('user_lng');                                    // 用户坐标纬度

            $distance_field = DB::raw('ROUND( 6378.138 * 2 * ASIN( SQRT( POW( SIN(( lat * PI() / 180 - ' . $user_lat . ' * PI() / 180) / 2) , 2) + COS(lat * PI() / 180) * COS(' . $user_lat . ' * PI() / 180) * POW( SIN(( lng * PI() / 180 - ' . $user_lng . ' * PI() / 180) / 2) , 2))) * 1000)');
            $s['distance'] = [$distance_field, $s['distance']];
        }

        $s['id'] = 'desc'; // ID倒序排序
        return parent::repositories($per_page = 10, $q, $s);
    }

    /**
     * repository
     *
     * @param $id
     * @return mixed    # 文章对象或null
     */
    public static function repository($id)
    {
        if ($article = self::find($id)) {
            return $article->makeVisible(['details']);
        }
        return false;
    }

    /**
     * 图片对象
     */
    public function photos()
    {
        return $this->morphMany('App\Models\v1\Photo', 'activitiable');
    }

    /**
     * 活动对象
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activity()
    {
        return $this->morphMany('App\Models\v1\Activity', 'activitiable');
    }

    /**
     * 文章对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function articleCategory()
    {
        return $this->belongsTo('App\Models\v1\ArticleCategory', 'article_category_id');
    }

    // 获取[文章分类对象] 属性
    public function getOriginalArticleCategoryAttribute()
    {
        return $this->articleCategory;
    }

    // 获取[活动数量] 属性
    public function getActivityNumbersAttribute()
    {
        if ($this->activity) {
            return $this->activity()->count();
        }
        return 0;
    }

    // 获取[缩略图] 属性
    public function getThumbnailAttribute($value)
    {
        return format_photo($value);
    }

    // 获取[banner] 属性
    public function getBannerAttribute($value)
    {
        return format_photo($value);
    }

    // 获取[标签] 属性
    public function getLabelsAttribute($value)
    {
        return explode(',', $value);
    }

    // 获取[内容] 属性
    public function getDetailsAttribute($value)
    {
        return html_entity_decode($value);
    }

    // 获取[时间] 属性
    public function getClientTimedAtAttribute()
    {
        if ($this->timed_at)
            return $this->timed_at->toDateString();
    }

    // 获取[扩展字段] 属性
    public function getExtraAttribute($value)
    {
        return $value ? json_decode($value) : '';
    }


    /**
     * 被点赞后的挂载操作
     */
    public function liked()
    {
        return $this->increment('has_liked_numbers');
    }

    /**
     * 被取消点赞后的挂载操作
     */
    public function unliked()
    {
        return $this->decrement('has_liked_numbers');
    }

    /**
     * 被评论后的挂载操作
     */
    public function commented()
    {
        return $this->increment('has_commented_numbers');
    }
}
