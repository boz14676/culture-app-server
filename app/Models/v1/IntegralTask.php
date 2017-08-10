<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class IntegralTask extends BaseModel
{
    protected $table = 'integral_tasks';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        "name",               // 名称
        "type",               // 任务类型
        "quantities",         // 积分量
    ];

    public $timestamps = false;

    protected $with = [];

    protected $casts = [
        'type' => 'integer'
    ];

    const TYPE_FIRSTTIME = 1;
    const TYPE_EVERTYDAY = 2;

    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('showing', function(Builder $builder) {
            $builder->where('enable', 1);
        });
    }

    public static function repositories($per_page=10, $q=[], $s=[])
    {
        return parent::repositories(0, $q);
    }

    // 用户积分记录对象
    public function UserIntegral()
    {
        $this->hasMany('App\Models\v1\UserIntegral', 'integral_task_id');
    }
}
