<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class ArticleCategory extends BaseModel
{
    protected $table = 'article_categories';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'topid',  // 上级ID
        'name',   // 名称
        'desc',   // 简介
    ];

    protected $with = [];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [];

    public static function repositories($topid=0)
    {
        return self::where('topid', $topid)->get();
    }
}
