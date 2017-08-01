<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;

class Likes extends BaseModel
{
    protected $table = 'likes';

    protected $guarded = [];

    protected $appends = [
        'original_user', // 用户对象
    ];

    protected $visible = [
        'id',
        'original_user',          // 用户
    ];

    protected $with = [];

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function likeable()
    {
        return $this->morphTo();
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

        // 点赞的挂载操作
        switch ($likesable_type) {
            case 'comment':
                if ($comment = Comment::find($likseable_id))
                    $comment->liked();
                break;
        }

        $likes = new Likes;
        $likes->user_id = $user->id;
        $likes->likesable_type = $likesable_type;
        $likes->likesable_id = $likseable_id;
        return $likes->save();
    }
}
