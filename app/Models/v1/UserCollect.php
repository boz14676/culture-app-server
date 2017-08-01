<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;
use DB;

class UserCollect extends BaseModel
{
    protected $table = 'user_collects';

    protected $guarded = [];

    protected $appends = [
        'original_article',         // 文章对象
        'original_activity',        // 活动对象
        'original_video',           // 视频对象
    ];

    protected $visible = [
        'id',
    ];

    protected $with = [];

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function collectable()
    {
        return $this->morphTo();
    }

    public static function repositories($per_page = 10, $q = [], $s = [])
    {
        $s['id'] = 'desc';

        $user_collects = parent::repositories($per_page, $q, $s);
        $user_collects->map(function ($user_collect) {
            switch ($user_collect->attributes['collectable_type']) {
                case 'article':
                    $user_collect->makeVisible(['original_article']);
                    break;
                case 'activity':
                    $user_collect->makeVisible(['original_activity']);
                    break;
                case 'video':
                    $user_collect->makeVisible(['original_video']);
                    break;
            }
        });

        return $user_collects;
    }

    /**
     * 收藏
     * @param $collectable_type
     * @param $collectable_id
     * @return mixed
     */
    public static function add($collectable_type, $collectable_id)
    {
        // 当前登录用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        if (
            $collect =
                self::where('user_id', $user->id)
                    ->where('collectable_type', $collectable_type)
                    ->where('collectable_id', $collectable_id)
                    ->first()
        )
        {
            self::errorMsg(trans('message.user.operation_error'));

            return false;
        }

        $likes = new self;
        $likes->user_id = $user->id;
        $likes->collectable_type = $collectable_type;
        $likes->collectable_id = $collectable_id;
        return $likes->save();
    }

    /**
     * 取消收藏
     */
    public static function remove($collectable_type, $collectable_id)
    {
        // 当前登录用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        if (
            !$collect =
                self::where('user_id', $user->id)
                ->where('collectable_type', $collectable_type)
                ->where('collectable_id', $collectable_id)
                ->first()
        )
        {
            self::errorMsg(trans('message.user.operation_error'));

            return false;
        }

        return $collect->delete();
    }

    /**
     * 文章对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function article()
    {
        return $this->belongsTo('App\Models\v1\Article', 'collectable_id');
    }

    /**
     * 活动对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo('App\Models\v1\Activity', 'collectable_id');
    }

    /**
     * 视频对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function video()
    {
        return $this->belongsTo('App\Models\v1\Video', 'collectable_id');
    }

    // 获取[文章对象] 属性
    public function getOriginalArticleAttribute()
    {
        return $this->article;
    }

    // 获取[活动对象] 属性
    public function getOriginalActivityAttribute()
    {
        return $this->activity;
    }

    // 获取[视频对象] 属性
    public function getOriginalVideoAttribute()
    {
        return $this->video;
    }
}
