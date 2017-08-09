<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;
use Illuminate\Database\Eloquent\Relations\Relation;

class UserLikes extends BaseModel
{
    protected $table = 'user_likes';

    protected $guarded = [];

    protected $appends = [
        'original_user', // 用户对象
    ];

    protected $visible = [
        'id',
        'original_user',          // 用户
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
            'music' => Music::class,
            'user_comment' => UserComment::class
        ]);
    }

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function likesable()
    {
        return $this->morphTo();
    }

    public static function repositories($per_page = 10, $q = [], $s = [])
    {
        $s['id'] = 'desc';

        return parent::repositories($per_page, $q, $s);
    }

    /**
     * 用户对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\v1\User');
    }

    // 获取[用户] 属性
    public function getOriginalUserAttribute()
    {
        if ($this->user) {
            return $this->user->setVisible(['nickname','avatar']);
        }
    }

    /**
     * 点赞
     */
    public static function add($likesable_type, $likseable_id)
    {
        // 当前登录用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        // 判断用户是否已经对该主题点赞
        if (
            $likes =
                self::where('user_id', $user->id)
                ->where('likesable_type', $likesable_type)
                ->where('likesable_id', $likseable_id)
                ->first()
        )
        {
            self::errorMsg(trans('message.user.operation_error'));

            return false;
        }

        // 点赞的挂载操作
        switch ($likesable_type) {
            case 'comment':
                if ($comment = UserComment::find($likseable_id))
                    $comment->liked();
                break;
            case 'article':
                if ($article = Article::find($likseable_id))
                    $article->liked();
                break;
        }

        $likes = new self;
        $likes->user_id = $user->id;
        $likes->likesable_type = $likesable_type;
        $likes->likesable_id = $likseable_id;
        return $likes->save();
    }

    /**
     * 取消点赞
     */
    public static function remove($likesable_type, $likseable_id)
    {
        // 当前登录用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        // 获取点赞对象
        if (
            !$likes =
                self::where('user_id', $user->id)
                ->where('likesable_type', $likesable_type)
                ->where('likesable_id', $likseable_id)
                ->first()
        )
        {
            self::errorMsg(trans('message.user.operation_error'));

            return false;
        }

        // 取消点赞的挂载操作
        switch ($likesable_type) {
            case 'comment':
                if ($comment = UserComment::find($likseable_id))
                    $comment->unliked();
                break;
            case 'article':
                if ($article = Article::find($likseable_id))
                    $article->unliked();
                break;
        }


        return $likes->delete();
    }
}
