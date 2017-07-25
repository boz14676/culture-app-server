<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class RaceCategories extends BaseModel
{
    protected $table = 'race_categories';
    
    protected $visible = ['id','name'];
    
}
