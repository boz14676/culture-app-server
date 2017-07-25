<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Example extends BaseModel
{
    protected $table = 'example';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = ['id', 'foo', 'created_at', 'updated_at'];

    protected $with = [];
}
