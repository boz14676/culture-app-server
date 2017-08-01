<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Auth;

class UserIntegral extends BaseModel
{
    protected $table = 'user_integrals';

    protected $guarded = [];

    protected $appends = ['timed_at'];

    protected $visible = [
        'id',
        'name',             // 名称
        'quantities',       // 积分量
        'timed_at',         // 增加积分时间
    ];

    protected $with = [];

    public static function repositories($per_page=10, $q=[], $s=[])
    {
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));
            return false;
        }

        $q['user_id'] = $user->id;
        $s['id'] = 'desc';
        return parent::repositories($per_page, $q, $s);
    }

    public function getTimedAtAttribute()
    {
        if ($this->attributes['created_at'])
            return $this->created_at->toDateString();
    }

    public function add()
    {
    }
}
