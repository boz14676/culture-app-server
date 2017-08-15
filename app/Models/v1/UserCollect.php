<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Relations\Relation;

class UserCollect extends BaseModel
{
    protected $table = 'user_collects';

    protected $guarded = [];

    protected $appends = [
        'original_collectable',         // 主题对象
        'collected_at',                 // 收藏时间
    ];

    protected $visible = [
        'id',
        'collectable_type',
        'collectable_id',
        'collected_at'
    ];

    protected $with = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 多态关联 类名映射
        Relation::morphMap([
            'article' => Article::class,
            'activity' => Activity::class,
            'stadium' => Stadium::class,
            'video' => Video::class,
        ]);
    }

    /**
     * 获取所有拥有的 collectable 模型
     */
    public function collectable()
    {
        return $this->morphTo();
    }

    public static function  repositories($per_page = 10, $q = [], $s = [])
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
        $likes->save();

        // 收藏的用户积分挂载操作
        $user->addIntegral('collected');

        return true;
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

    // 获取[主题对象] 属性
    public function getOriginalCollectableAttribute()
    {
        return $this->collectable;
    }

    // 获取[收藏时间] 属性
    public function getCollectedAtAttribute()
    {
        return $this->created_at->toDateString();
    }
}
