<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BaseModel;
use Carbon\Carbon;
use Auth;
use DB;

class Activity extends BaseModel
{
    protected $table = 'activities';

    protected $guarded = [];

    protected $appends = [
        'original_article_category',        // 文章分类对象
        'registered_at',                    // 预约有效时间区间
        'is_cur_user_liked',                // 是否被当前用户点赞
        'is_cur_user_collected',            // 是否被当前用户收藏
    ];

    protected $visible = [
        'id',
        'original_article_category',        // 文章分类对象
        'status',                           // 状态
        'is_free',                          // 是否为免费(type: boolean[0, 1])
        'name',                             // 名称
        'lat',                              // 坐标 经纬
        'lng',                              // 坐标 纬度
        'labels',                           // 标签(s)
        'thumbnail',                        // 缩略图
        'banner',                           // banner
        'price',                            // 价格
        'address',                          // 地址
        'registered_at',                    // 活动的开始和结束时间
        'contact',                          // 咨询电话
        'has_commented_numbers',            // 评论数量
        'has_liked_numbers',                // 点赞数量
        'has_read_numbers',                 // 阅读数量
        'is_cur_user_liked',                // 是否被当前用户点赞
        'is_cur_user_collected',            // 是否被当前用户收藏
    ];

    protected $with = [];

    protected $dates = ['start_registered_at', 'end_registered_at'];

    protected $casts = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 多态关联 类名映射
        Relation::morphMap([
            'article_category' => ArticleCategory::class,
            'stadium' => Stadium::class,
        ]);
    }

    /**
     * 活动状态
     */
    const STATUS_BOOKABLE = 1;      // 可预约
    const STATUS_BOOKEDUP = 2;      // 已订满
    const STATUS_HAVENT_START = 3;  // 未开始
    const STATUS_FINISHED = 4;      // 已结束

    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('showing', function(Builder $builder) {
            $builder->where('is_active', 1);
        });
    }

    /**
     * 获取相关活动列表
     * @param $per_page
     * @return mixed
     */
    public function relatedsRepository($per_page)
    {
        self::$aliveSelf = self
            ::leftjoin('labeables', function ($join) {
                $join
                    ->on('activities.id', '=', 'labeables.labeable_id')
                    ->where('labeables.labeable_type', '=', 'activity')
                    ->whereIn('labeables.label_id', $this->tags()->pluck('id')->all());
            })
            ->where('activities.id', '<>', $this->attributes['id'])
            ->where(function ($query) {
                $query
                    ->whereNotNull('labeables.label_id')
                    ->orWhere('activities.area_id', '=', $this->attributes['area_id']);
            });

        return self::repositories($per_page);
    }

    /**
     * 获取所有拥有的 activitiable 模型
     */
    public function activitiable()
    {
        return $this->morphTo();
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

    // 获取[活动开始和结束时间] 属性
    public function getRegisteredAtAttribute()
    {
        if ($this->start_registered_at && $this->start_registered_at) {
            return [
                $this->start_registered_at->toDateString(),
                $this->end_registered_at->toDateString()
            ];
        }
    }

    // 获取[文章分类对象] 属性
    public function getOriginalArticleCategoryAttribute()
    {
        if ($this->activitiable) {
            if ($this->activitiable instanceof ArticleCategory) {
                return $this->activitiable;
            } else {
                return $this->activitiable->articleCategory;
            }
        }
    }

    // 获取[标签](s) 属性
    public function getLabelsAttribute($value)
    {
        return $this->tags()->pluck('name');
    }

    // 获取[缩略图] 属性
    public function getThumbnailAttribute($value)
    {
        if ($value)
            return format_assets('file/assets/'.$value);
    }

    // 获取[banner] 属性
    public function getBannerAttribute($value)
    {
        if ($value)
            return format_assets('file/assets/'.$value);
    }

    // 获取[内容] 属性
    public function getDetailsAttribute($value)
    {
        return html_entity_decode($value);
    }

    // 获取[状态] 属性
    public function getStatusAttribute()
    {
        // 如果后台设置了状态
        if ($this->attributes['status']) {
            return $this->attributes['status'];
        }

        // 如果没有剩余数量
        if (!$this->attributes['stock_numbers']) {
            return self::STATUS_BOOKEDUP;
        }

        // 如果小于可预约时间的开始时间
        elseif (Carbon::now()->getTimestamp() < $this->start_registered_at->getTimestamp()) {
            return self::STATUS_HAVENT_START;
        }

        // 如果大于可预约时间的结束时间
        elseif (Carbon::now()->getTimestamp() > $this->end_registered_at->getTimestamp()) {
            return self::STATUS_FINISHED;
        }

        // 可预约
        else {
            return self::STATUS_BOOKABLE;
        }
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
}
