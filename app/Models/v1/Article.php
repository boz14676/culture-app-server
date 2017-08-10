<?php
/*
 * 文章 - Eloquent ORM
 */


namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;
use DB;

class Article extends BaseModel
{
    protected $table = 'articles';

    protected $guarded = [];

    protected $appends = [
        'activity_numbers',                 // 活动数量
        'client_timed_at',                  // 时间
        'original_article_category',        // 文章分类对象
        'is_cur_user_liked',                // 是否被当前用户点赞
        'is_cur_user_collected',            // 是否被当前用户收藏
    ];

    protected $visible = [
        'id',
        'article_category_id',              // 文章分类ID
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
        'has_commented_numbers',            // 评论数量
        'has_liked_numbers',                // 点赞数量
        'has_read_numbers',                 // 阅读数量
        'client_timed_at',                  // 时间
        'extra',                            // 扩展字段
        'has_photos',                       // 是否有图片 [type: boolean(0, 1)]
        'has_videos',                       // 是否有视频 [type: boolean(0, 1)]
        'is_cur_user_liked',                // 是否被当前用户点赞
        'is_cur_user_collected',            // 是否被当前用户收藏
    ];

    protected $with = [];

    protected $dates = ['timed_at', 'created_at', 'updated_at'];

    protected $casts = [
        'is_hot' => 'integer',
        'has_activity_numbers' => 'integer',
        'has_commented_numbers' => 'integer',
        'has_liked_numbers' => 'integer',
    ];

    /**
     * repositories
     *
     * @param int $per_page # 每页显示记录数
     * @param array $q      # 筛选
     * @param array $s      # 排序
     * @return mixed        # 文章对象(s)或null
     */
    public static function repositories($per_page = 10, $q = [], $s = [])
    {
        /**
         * 显示在一级文章分类下的 [附近推荐] 或 [近期热门]
         * 逻辑实现：获取当前一级文章分类和其下的所有子分类，根据这些分类去查找符合条件的记录
         */
        if (
            (isset($q['article_category_id']) && $q['article_category_id'] != 3)
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
        elseif (
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

        // 标签过滤
        elseif (
            isset($q['label_id'])
        )
        {
            self::$aliveSelf = self::join('labeables', function ($join) use ($q) {
                $join
                    ->on('articles.id', '=', 'labeables.labeable_id')
                    ->where('labeables.labeable_type', '=', 'article')
                    ->where('labeables.label_id', '=', $q['label_id']);
            });
            unset($q['label_id']);

            $article_category = ArticleCategory::find($q['article_category_id']);                   // 获取文章分类对象
            $subclasses_id = collect($article_category->subclasses_id);                             // 文章分类对象 所有子类的ID
            $q['article_category_id'] = $subclasses_id->push($article_category->attributes['id']);  // 文章分类ids

        }

        // 文化服务下面的场馆、活动推荐
        elseif (
            (isset($q['article_category_id']) && $q['article_category_id'] = 3)
            && (isset($q['is_hot']) && $q['is_hot'] = 1)
        )
        {
            $article_category = ArticleCategory::find($q['article_category_id']);                   // 获取文章分类对象
            $subclasses_id = collect($article_category->subclasses_id);                             // 文章分类对象 所有子类的ID
            $full_id = $subclasses_id->push($article_category->attributes['id']);                   // 文章分类ids

            $activities_build = Activity
                ::select('id', 'activitiable_id', 'name', 'thumbnail', 'banner')
                ->whereIn('activitiable_id', $full_id)
                ->where('activitiable_type', 'article_category')
                ->where('is_hot', 1);

            $stadiums_build = Stadium
                ::select('id', 'article_category_id', 'name', 'thumbnail', 'banner')
                ->whereIn('article_category_id', $full_id)
                ->where('is_hot', 1);

            $articles_build = Article
                ::select('id', 'article_category_id', 'name', 'thumbnail', 'banner')
                ->whereIn('article_category_id', $full_id)
                ->where('is_hot', 1)
                ->union($stadiums_build)
                ->union($activities_build);

            self::$aliveSelf = $articles_build;

            unset($q, $s);
        }

        $s['id'] = 'desc'; // ID倒序排序
        return parent::repositories($per_page, isset($q) ? $q : [], isset($s) ? $s : []);
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

    /**
     * 点赞对象
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function likes()
    {
        return $this->morphMany('App\Models\v1\UserLikes', 'likesable');
    }

    /**
     * 收藏对象
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function collects()
    {
        return $this->morphMany('App\Models\v1\UserCollect', 'collectable');
    }

    /**
     * 标签对象
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags()
    {
        return $this->morphToMany('App\Models\v1\Label', 'labeable');
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
        return format_assets($value);
    }

    // 获取[banner] 属性
    public function getBannerAttribute($value)
    {
        return format_assets($value);
    }

    // 获取[标签](s) 属性
    public function getLabelsAttribute($value)
    {
        return $this->tags()->pluck('name');
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

    // 获取[是否被当前用户点赞] 属性
    public function getIsCurUserLikedAttribute()
    {
        if ($cur_user = Auth::user()) {
            return
                $this->likes()
                    ->where('user_id', $cur_user->id)
                    ->count();
        }

        return 0;
    }

    // 获取[是否被当前用户收藏] 属性
    public function getIsCurUserCollectedAttribute()
    {
        if ($cur_user = Auth::user()) {
            return
                $this->collects()
                    ->where('user_id', $cur_user->id)
                    ->count();
        }

        return 0;
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
