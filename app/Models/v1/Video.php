<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Video extends BaseModel
{
    protected $table = 'videos';

    protected $guarded = [];

    protected $appends = [
        'url' // 视频地址
    ];

    protected $visible = [
        'id',
        'name',                 // 名称
        'url',                  // 地址
        'wathcing_numbers',     // 观看次数
        'particular_year',      // 年份
        'episode_numbers',      // 集数
    ];

    protected $with = [];

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    // 获取[地址] 属性
    public function getUrlAttribute($value)
    {
        if ($value) {
            $path_pre = 'file/videos/';
            return format_photo($path_pre.$this->attributes['original'], $path_pre.$this->attributes['thumbnail']);
        }
    }
}
