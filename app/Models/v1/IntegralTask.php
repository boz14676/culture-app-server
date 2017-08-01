<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class IntegralTask extends BaseModel
{
    protected $table = 'integral_tasks';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        "name",               // 名称
        "type",               // 任务类型
        "quantity",           // 积分量
    ];

    protected $with = [];

    public static function repositories($per_page=10, $q=[], $s=[])
    {
        $q['enable'] = 1;
        return parent::repositories(0, $q);
    }
}
