<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class RaceItem extends BaseModel
{
    protected $table = 'race_items';
    protected $hidden = ['race_id'];
}
