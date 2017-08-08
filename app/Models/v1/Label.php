<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Label extends BaseModel
{
    protected $table = 'labels';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'name',                 // 名称
    ];

    protected $with = [];
}
