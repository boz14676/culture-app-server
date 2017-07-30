<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;

class Comment extends BaseModel
{
    protected $table = 'comments';

    protected $guarded = [];

    protected $appends = [
        'original_user', // 用户对象
    ];

    protected $visible = [
        'id',
        'original_user',          // 用户
        'has_liked_number',       // 被赞数量
        'details',                // 内容
        'created_at'              // 新增时间
    ];

    protected $with = [];

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function commentable()
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
     * 写入一条评论
     * @param $commentable_type
     * @param $commentable_id
     * @param $details
     * @return bool
     */
    public static function write($commentable_type, $commentable_id, $details)
    {
        // 当前登录用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        $comment = new self;
        $comment->user_id = $user->id;                      // 用户ID
        $comment->commentable_type = $commentable_type;     // 主题类型
        $comment->commentable_id = $commentable_id;         // 主题ID
        $comment->details = $details;                       // 内容
        $comment->save();
        return $comment;
    }
}
