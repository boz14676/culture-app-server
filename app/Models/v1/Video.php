<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;
use Illuminate\Database\Eloquent\Relations\Relation;

class Video extends BaseModel
{
    protected $table = 'videos';

    protected $guarded = [];

    protected $appends = [
        'prev_id',                  // 上一个视频对象ID
        'next_id',                  // 下一个视频对象ID
        'is_cur_user_liked',        // 是否被当前用户点赞
        'is_cur_user_collected',    // 是否被当前用户收藏
    ];

    protected $visible = [
        'id',
        'name',                     // 名称
        'labels',                   // 标签
        'thumbnail',                // 缩略图
        'url',                      // 地址
        'particular_year',          // 年份
        'episode_numbers',          // 集数
        'has_read_numbers',         // 观看数量
        'has_commented_numbers',    // 评论数量
        'has_liked_numbers',        // 点赞数量
        'is_cur_user_liked',        // 是否被当前用户点赞
        'is_cur_user_collected',    // 是否被当前用户收藏
    ];

    protected $with = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 多态关联 类名映射
        Relation::morphMap([
            'article_category' => ArticleCategory::class,
            'article' => Article::class,
            'stadium' => Stadium::class,
        ]);
    }

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function videoable()
    {
        return $this->morphTo();
    }

    public static function repository($id)
    {
        if ($video = self::find($id)) {
            return $video->makeVisible(['prev_id', 'next_id']);
        }

        return false;
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

    // 获取[地址] 属性
    public function getUrlAttribute($value)
    {
        if ($value) {
            $path_pre = 'file/videos';
            return format_assets($this->attributes['url'], $path_pre);
        }
    }

    // 获取[标签](s) 属性
    public function getLabelsAttribute($value)
    {
        return $this->tags()->pluck('name');
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

    // 获取[上一个视频对象ID] 属性
    public function getPrevIdAttribute()
    {
        return intval(
            $this
                ->where('id', '<', $this->attributes['id'])
                ->orderBy('id', 'desc')
                ->value('id')
        );
    }

    // 获取[下一个视频对象ID] 属性
    public function getNextIdAttribute()
    {
        return intval(
            $this
                ->where('id', '>', $this->attributes['id'])
                ->orderBy('id', 'asc')
                ->value('id')
        );
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
