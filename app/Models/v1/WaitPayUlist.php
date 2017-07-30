<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Carbon\Carbon;
use DB;

class WaitPayUlist extends BaseModel
{
    protected $table = 'wait_pay_ulist';

    protected $guarded = [];

    protected $appends = [];

    protected $visible = [];

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

    public static function timeout($payment_overtime)
    {
        $subtract_create_at = DB::raw('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(created_at)');
        return self::where($subtract_create_at, '>=', $payment_overtime*60)->get();
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