<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class Music extends BaseModel
{
    protected $table = 'musics';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'name',                 // 名称
        'thumbnail',            // 缩略图
        'url',                  // 地址
        'lenstening_numbers',   // 观看次数
        'singer_name',          // 歌手名称
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
