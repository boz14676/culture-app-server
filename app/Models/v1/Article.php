<?php
/*
 * 文章 - Eloquent ORM
 */

namespace App\Models\v1;

use App\Models\BaseModel;

class Article extends BaseModel
{
    protected $table = 'articles';

    protected $guarded = [];

    protected $appends = ['client_timed_at'];

    protected $visible = [
        'id',
        'article_category_id',  // 文章分类对象 ID
        'name',                 // 名称（包含：姓名）
        'thumbnail',            // 缩略图（包含：大师头像）
        'banner',               // banner（包含：背景图）
        'label',                // 标签
        'distance',             // 距离（m）*可做排序使用属性
        'location',             // 内容所在地
        'desc',                 // 用于：内容描述、专题简介、内容年代、主题、个人简介
        'activity_numbers',     // 活动数量
        'comment_numbers',      // 评论数量 *可用作做排序使用的属性
        'like_numbers',         // 点赞数量 *可用作排序使用的属性
        'reading_numbers',      // 阅读量 *仅做排序使用的属性
        'client_timed_at',      // 时间
        'extra',                // 扩展字段
    ];

    protected $with = [];

    protected $dates = ['timed_at', 'created_at', 'updated_at'];

    protected $casts = [
        'is_hot' => 'integer',
        'activity_numbers' => 'integer',
        'comment_numbers' => 'integer',
        'like_numbers' => 'integer',
    ];

    /**
     * repositories
     * @param int $per_page             # 每页显示记录数
     * @param array $q                  # 筛选
     * @param array $s                  # 排序
     * @return mixed                    # 文章对象(s)或null
     */
    public static function repositories($per_page=10, $q=[], $s=[])
    {
        $s['id'] = 'desc'; // ID倒序排序
        return parent::repositories($per_page=10, $q, $s);
    }

    /**
     * repository
     *
     * @param $id
     * @return mixed    # 文章对象或null
     */
    public static function repository($id)
    {
        return self::find($id)->makeVisible(['details']);
    }

    /**
     * 获取图片
     */
    public function photos()
    {
        return $this->morphMany('App\Models\v1\Photo', 'imageable');
    }


    // 获取[缩略图] 属性
    public function getThumbnailAttribute($value)
    {
        return 'http://spdb.wth689.com' . $value;
    }

    // 获取[标签] 属性
    public function getLabelAttribute($value)
    {
        return explode(',', $value);
    }

    // 获取[内容] 属性
    public function getDetailsAttribute($value)
    {
        return html_entity_decode($value);
    }

    // 获取[时间] 属性
    public function getClientTimedAtAttribute()
    {
        if ($this->timed_at)
            return $this->timed_at->toDateString();
    }

    // 获取[扩展字段] 属性
    public function getExtraAttribute($value)
    {
        return $value ? json_decode($value) : '';
    }
}
