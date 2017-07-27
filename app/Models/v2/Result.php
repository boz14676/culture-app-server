<?php

namespace App\Models\v2;

use App\Models\BaseModel;
use Yadakhov\InsertOnDuplicateKey;

class Result extends BaseModel
{
    use InsertOnDuplicateKey;
    
    protected $table      = 'results';

    protected $appends = ['per_rank'];
    protected $visible = ['id', 'result_rank', 'per_rank', 'group_rank', 'result_time'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取指定用户的成绩
     */
    public static function getWithUser($race_id)
    {
        $user = User::using($race_id);
        if ($result = $user->resultWithRace) {
            $race_group_with_user = $user->runnerInfoWithRace->raceGroup;
            $result->group_numbers = $race_group_with_user->numbers;
            return $result;
        }
        
        return false;
    }
    
    // 返回小组排名百分比
    public function getPerRankAttribute()
    {
        $per_rank = round($this->group_rank / $this->group_numbers, 2) ? : 0.01;
        return $per_rank;
    }
    
    // 返回格式化时间
    public function getResultTimeAttribute($value)
    {
        return $value ? ticksToTimeStr($value) : $value;
    }
}
