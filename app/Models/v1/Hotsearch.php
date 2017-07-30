<?php
/*
 * 热搜 - Eloquent ORM
 */

namespace App\Models\v1;

use App\Models\BaseModel;

class Hotsearch extends BaseModel
{
    protected $table = 'hotsearches';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'article_category_id', // 文章分类对象 ID
        'name',                // 名称
    ];

    protected $with = [];

    public $timestamps = false;

    const REPOSITORY_MODE = self::REPOSITORY_MODE_UNPAGE; // 数据仓库返回格式: 非分页

    /**
     * repositories
     * @param int $per_page             # 每页显示记录数
     * @param array $q                  # 筛选
     * @param array $s                  # 排序
     * @return mixed                    # 实体对象或null
     */
    public static function repositories($numbers=6, $q=[], $s=[])
    {
        $s['searching_numbers'] = 'desc';
        return parent::repositories($numbers, $q, $s);
    }

}
