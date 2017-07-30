<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Comment extends BaseModel
{
    protected $table = 'comment';

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
}
