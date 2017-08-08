<?php
/*
 * 场馆 - Eloquent ORM
 */


namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;

class Stadium extends BaseModel
{
    protected $table = 'stadiums';

    protected $guarded = [];

    protected $appends = [
        'original_article_category',        // 文章分类对象
        'is_cur_user_liked',                // 是否被当前用户点赞
        'is_cur_user_collected',            // 是否被当前用户收藏
    ];

    protected $visible = [
        'id',
        'original_article_category',            // 文章分类 对象
        'name',                                 // 名称
        'thumbnail',                            // 缩略图
        'banner',                               // banner（包含：背景图）
        'labels',                               // 标签
        'opening_hours',                        // 营业时间
        'contact',                              // 联系方式
        'transport',                            // 公共交通
        'lat',                                  // 坐标精度
        'lng',                                  // 坐标纬度
        'address',                              // 位置
        'distance',                             // 距离（m）*仅做排序使用属性
        'has_photos',                           // 是否有音乐 [type: boolean(0, 1)]
        'has_videos',                           // 是否有视频 [type: boolean(0, 1)]
        'has_commented_numbers',                // 评论数量 *可用作做排序使用的属性
        'has_liked_numbers',                    // 点赞数量 *可用作排序使用的属性
        'has_read_numbers',                     // 阅读数量 *仅做排序使用的属性
        'is_cur_user_liked',                    // 是否被当前用户点赞
        'is_cur_user_collected',                // 是否被当前用户收藏
        'details',                              // 内容
    ];

    protected $with = [];

    protected $casts = [
        'activity_numbers' => 'integer',
        'has_commented_numbers' => 'integer',
        'has_liked_numbers' => 'integer',
        'has_read_numbers' => 'integer',
        'is_cur_user_liked' => 'integer',
        'is_cur_user_collected' => 'integer',
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
     * 文章分类对象
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
     * 点赞对象
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
