<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\Relation;

class Photo extends BaseModel
{
    protected $table = 'photos';

    protected $guarded = [];

    protected $appends = ['url'];

    protected $visible = [
        'id',
        'name', // 名称
        'url'   // 地址
    ];

    protected $with = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 多态关联 类名映射
        Relation::morphMap([
            'article' => Article::class,
            'stadium' => Stadium::class,
        ]);
    }

    /**
     * 获取所有拥有的 imageable 模型
     */
    public function imageable()
    {
        return $this->morphTo();
    }

    // 获取[地址] 属性
    public function getUrlAttribute()
    {
        $path_pre = 'file/photos';
        return format_assets([
            $this->attributes['thumbnail'],
            $this->attributes['original']
        ], $path_pre);
    }
}
