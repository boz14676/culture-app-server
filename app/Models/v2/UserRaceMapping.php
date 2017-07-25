<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class UserRaceMapping extends BaseModel
{
    protected $table      = 'user_race_mapping';
    
    public function resultEvent()
    {
        return $this->hasMany(ResultEvent::class, 'runner_no', 'runner_no');
    }
    
    // 查询指定的赛事是否有指定的用户
    public static function hasRaceWithUser($race_id, $user_id)
    {
        return UserRaceMapping::where('race_id', $race_id)->where('user_id', $user_id)->count();
    }
}
