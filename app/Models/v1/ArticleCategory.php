<?php

namespace App\Models\v1;

use App\Models\BaseModel;

class ArticleCategory extends BaseModel
{
    protected $table = 'article_categories';

    protected $guarded = [];

    protected $appends = [
        'subclasses_id', // 所有子类的ID
    ];

    protected $visible = [
        'id',
        'topid',                // 上级ID
        'icon',                 // icon
        'showing_type_list',    // 文章列表展示类型
        'showing_type_infor',   // 文章详情展示类型
        'name',                 // 名称
        'desc',                 // 简介
    ];

    protected $with = [];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'showing_type_list' => 'integer',
        'showing_type_infor' => 'integer'
    ];

    /**
     * repositories
     *
     * @param int $topid
     * @return mixed
     */
    public static function repositories($topid=0, $q=[], $numbers=0)
    {
        return self::when($topid, function ($query) use ($topid) {
                return $query->where('topid', $topid);
            })
            // 筛选
            ->when($q, function ($query) use ($q) {
                return self::filtering($query, $q);
            })
            // 限制显示数量
            ->when($numbers, function ($query) use ($numbers) {
                return $query->take($numbers);
            })
            ->orderBy('sort', 'asc')
            ->get();
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
    public function topCategory()
    {
        return self::find($this->attributes['topid']);
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
}
