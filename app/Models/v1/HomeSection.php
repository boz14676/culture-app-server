<?php
/*
 * 热搜 - Eloquent ORM
 */

namespace App\Models\v1;

use App\Models\BaseModel;

class HomeSection extends BaseModel
{
    protected $table = 'home_sections';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'name',      // 名称
        'thumbnail', // 缩略图
        'extra',     // 扩展字段
        'url'        // url
    ];

    protected $with = [];

    public $timestamps = false;

    /**
     * repositories
     *
     * @param array $confs             # 配置信息
     * @return mixed                   # 首页推荐栏目对象(s)或null
     */
    public static function repositories(array $confs)
    {
        $home_sections = collect();
        collect($confs)->map(function ($numbers, $adr) use (&$home_sections) {
            $home_section = self::where('adr', $adr)
                ->orderBy('sort', 'asc')
                ->take($numbers)
                ->get();

            $home_sections->put($adr, $home_section);
        });

        return $home_sections;
    }


    // 获取[缩略图]属性
    public function getThumbnailAttribute($value)
    {
        return url() . '/file/assets/' . $value;
    }
}
