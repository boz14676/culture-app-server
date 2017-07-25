<?php

namespace App\Models\v2;

use App\Models\BaseModel;

class RunnerInfo extends BaseModel {
    
    protected $table      = 'runner_infos';
    
    public $timestamps = false;
    
    // 用户在当前比赛的项目
    public function raceItem()
    {
        return $this->BelongsTo(RaceItem::class, 'race_item_id');
    }
    
    // 用户在当前比赛的组别
    public function raceGroup()
    {
        return $this->BelongsTo(RaceGroup::class, 'race_group_id');
    }
    
    // 根据用户的参赛号获取user_id
    public static function getUserIdByRunnerNo($race_id, $runner_no)
    {
        if ($runner_info = self::belongsRace($race_id)->where('runner_no', $runner_no)->first()) {
            return $runner_info->user_id;
        }
        
        return 0;
    }
}
