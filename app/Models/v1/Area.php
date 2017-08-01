<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Area extends BaseModel
{
    protected $table = 'areas';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'name',                 // 名称
        'region',               // 大区名称
    ];

    protected $with = [];
}
