<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use DB;

class ArticleCategory extends BaseModel
{
    protected $table = 'article_categories';

    protected $guarded = [];

    protected $appends = [
        'subclasses_id', // 所有子类的ID
        'top_level_id',  // 一级分类ID
    ];

    protected $visible = [
        'id',
        'is_activity',          // 是否可预约
        'topid',                // 上级ID
        'icon',                 // 图标
        'banner',               // banner
        'showing_type_list',    // 文章列表展示类型
        'showing_type_infor',   // 文章详情展示类型
        'name',                 // 名称
        'desc',                 // 简介

        'is_activity',          // 是否有预约 [type: boolean(0, 1)]
        'is_music',             // 是否有音乐 [type: boolean(0, 1)]
        'is_video',             // 是否有视频 [type: boolean(0, 1)]
    ];

    protected $with = [];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'showing_type_list' => 'integer',
        'showing_type_infor' => 'integer'
    ];

    protected static $topmostCateogry;

    /**
     * repositories
     *
     * @param int $topid
     * @return mixed
     */
    public static function repositories($per_page=10, $q=[], $s=[])
    {
        $s['sort'] = 'asc';

        return parent::repositories($per_page, $q, $s);
    }

    // 获取子类的id
    public function getSubclassesIdAttribute()
    {
        $ids = [];

        if (!$this->upCategory()->isEmpty()) {
            $this->upCategory()->map(function ($up_category) use (&$ids) {
                $ids[] = $up_category->id;
                $ids = array_merge($ids, $up_category->subclasses_id);
            });
        }

        return $ids;
    }

    // 获取当前文章分类对象的 下一级文章分类对象
    public function upCategory()
    {
        return self::where('topid', $this->attributes['id'])->get();
    }

    // 获取当前文章分类对象的 上一级文章分类对象
    public function topCategory($topid=0)
    {
        $topid = $topid ? : $this->attributes['topid'];

        if ($topid) {
            return self::find($topid);
        }
    }

    // 第一级别 文章分类对象
    public function topmostCategory()
    {

    }

    // 获取[文章列表展示类型] 属性
    public function getShowingTypeListAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            if ($this->topCategory()) {
                return $this->topCategory()->showing_type_list;
            }
        }

        return ;
    }

    // 获取[文章详情展示类型] 属性
    public function getShowingTypeInforAttribute($value)
    {
        if ($value) {
            return $value;
        } else {
            if ($this->topCategory()) {
                return $this->topCategory()->showing_type_infor;
            }
        }

        return ;
    }

    // 获取 [一级分类ID] 属性
    public function getTopLevelIdAttribute()
    {
        dd($this->topmostCategory());
    }

    // 获取[图标] 属性
    public function getIconAttribute($value)
    {
        return format_assets($value);
    }
}
