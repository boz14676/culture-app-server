<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\Relation;

class Label extends BaseModel
{
    protected $table = 'labels';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'name',                 // 名称
    ];

    protected $with = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // 多态关联 类名映射
        Relation::morphMap([
            'article' => Article::class,
            'stadium' => Stadium::class,
            'activity' => Activity::class,
            'video' => Video::class,
        ]);
    }
}
