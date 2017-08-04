<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Builder;
use App\Models\BaseModel;
use Carbon\Carbon;

class Activity extends BaseModel
{
    protected $table = 'activities';

    protected $guarded = [];

    protected $appends = [
        'original_article_category',    // 文章分类对象
        'registered_at',        // 预约有效时间区间
    ];

    protected $visible = [
        'id',
        'original_article_category',    // 文章分类对象
        'status',               // 状态
        'is_free',              // 是否为免费(type: boolean[0, 1])
        'name',                 // 名称
        'labels',               // 标签(s)
        'thumbnail',            // 缩略图
        'banner',               // banner
        'price',                // 价格
        'address',              // 地址
        'registered_at',        // 活动的开始和结束时间
        'contact',              // 咨询电话
    ];

    protected $with = [];

    protected $dates = ['start_registered_at', 'end_registered_at'];

    protected $casts = [];

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
     * 获取所有拥有的 activitiable 模型
     */
    public function activitiable()
    {
        return $this->morphTo();
    }

    /**
     * 文章对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function articleCategory()
    {
        return $this->belongsTo('App\Models\v1\ArticleCategory', 'activitiable_id');
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
        if ($this->activitiable_type === 'article_category') {
            return $this->articleCategory;
        }
    }

    // 获取[标签(s)] 属性
    public function getLabelsAttribute($value)
    {
        return explode(',', $value);
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
}
