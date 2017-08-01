<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Integral extends BaseModel
{
    protected $table = 'integrals';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = ['id', 'foo', 'created_at', 'updated_at'];

    protected $with = [];
}
