<?php

namespace App\Models\v2;

use App\Models\BaseModel;
use Yadakhov\InsertOnDuplicateKey;
use DB;

class ResultEvent extends BaseModel
{
    use InsertOnDuplicateKey;
    
    const HAS_CALLON = 1; // 已访问过维赛接口
    const HASNOT_CALLON = 2; // 未访问过维赛接口获取成绩

    protected $table      = 'result_events';

    protected $fillable = ['id', 'race_id', 'runner_no', 'position', 'result'];
    // protected $visible = ['id', 'race_id', 'position', 'runner_no'];

    /**
     * 匹配站点成绩的照片
     */
    public function getPhotos()
    {
        $ret_field = DB::raw('ABS('.$this->result.'-result)');
        $photos = Photo::where('race_id', $this->race_id)
            ->where('position', $this->position)
            ->whereBetween($ret_field, [0, 5])
            ->orderBy($ret_field, 'ASC')
            ->get();
        return $photos;
    }
}
