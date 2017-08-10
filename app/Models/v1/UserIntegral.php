<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Builder;
use App\Models\BaseModel;
use Auth;
use DB;

class UserIntegral extends BaseModel
{
    protected $table = 'user_integrals';

    protected $guarded = [];

    protected $appends = [
        'name',     // 积分任务名称
        'timed_at'  // 增加积分时间
    ];

    protected $visible = [
        'id',
        'name',             // 积分任务名称
        'quantities',       // 积分量
        'timed_at',         // 增加积分时间
    ];

    protected $with = [];

    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

    }

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

    /**
     * 积分任务对象
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function integralTask()
    {
        return $this->belongsTo('App\Models\v1\IntegralTask', 'integral_task_id');
    }

    // 获取 [积分任务名称] 属性
    public function getNameAttribute()
    {
        if ($this->integralTask) {
            return $this->integralTask->name;
        }
    }

    // 获取 [增加积分时间] 属性
    public function getTimedAtAttribute()
    {
        if ($this->attributes['created_at'])
            return $this->created_at->toDateString();
    }

    // 添加积分
    public function add($code)
    {
    }
}
