<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class RaceGroup extends BaseModel
{
    protected $table = 'race_groups';
    protected $hidden = ['race_id'];
}
