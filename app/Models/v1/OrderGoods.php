<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Carbon\Carbon;

class OrderGoods extends BaseModel
{
    protected $table = 'order_goods';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [
        'id',
        'price',                    // 商品价格
        'name',                     // 名称
        'numbers',                  // 商品数量
        'booking_person_name',      // 预约人姓名
        'booking_person_mobile'     // 预约人手机号
    ];

    protected $dates = [];

    protected $with = [];

    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }

    public function freshTimestamp()
    {
        return new Carbon;
    }

    public function fromDateTime($value)
    {
        $format = $this->getDateFormat();

        $value = $this->asDateTime($value);

        return $value->format($format);
    }
}