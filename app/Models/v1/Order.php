<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Carbon\Carbon;
use Auth;
use Log;

class Order extends BaseModel
{
    protected $table = 'orders';

    protected $guarded = [];

    protected $appends = [
        'goods_price',              // 商品金额
        'goods_numbers',            // 商品数量
        'expire_time',              // 过期时间
        'original_activity',        // 预约对象
        'booking_person_name',      // 预约人姓名
        'booking_person_mobile',    // 预约人手机号
    ];

    protected $visible = [
        'id',
        'original_activity',            // 预约对象
        'booking_person_name',          // 预约人姓名
        'booking_person_mobile',        // 预约人手机号
        'serial_number',                // 订单编号
        'status',                       // 订单状态
        'amount',                       // 订单金额
        'payment_serial_number',        // 支付单号
        'payment_type',                 // 支付类型
        'goods_price',                  // 商品金额
        'goods_numbers',                // 商品数量
        'paid_at',                      // 支付时间
        'created_at',                   // 订单创建时间
    ];

    protected $dates = ['paid_at', 'created_at', 'updated_at'];

    protected $with = [];

    protected $casts = [
        'status' => 'integer',
        'type' => 'integer',
    ];

    /**
     * 订单超时的时间 [type: min]
     */
    // const EXPIRE_TIME = 15; // TODO: 放到配置文件中

    /**
     * 订单状态
     */
    const STATUS_CANCELED = -1;   // 已取消
    const STATUS_WAIT_PAY = 0;    // 等待支付
    const STATUS_PAID = 1;        // 已支付
    const STATUS_REFUNDING = 2;   // 退款中
    const STATUS_REFUNDED = 3;    // 退款成功
    const STATUS_FAILED = 4;      // 退款失败


    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
    }

    /**
     * 订单数据仓库
     *
     * @param $id
     * @return mixed
     */
    public static function repository($id)
    {
        return self::find($id);
    }

    // 已取消
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELED;
    }

    /**
     * repositories
     * @param int $per_page             # 每页显示记录数
     * @param array $q                  # 筛选
     * @param array $s                  # 排序
     * @return mixed                    # 实体对象或null
     */
    public static function repositories($per_page=10, $q=[], $s=[])
    {
        if (!Auth::user()) {
            return false;
        }

        $q['user_id'] = Auth::user()->id;   // 用户ID
        $s['id'] = 'desc';                  // 排序

        return parent::repositories($per_page=10, $q, $s);
    }

    /**
     * 订单商品对象
     */
    public function orderGoods()
    {
        return $this->hasOne('App\Models\v1\OrderGoods');
    }

    /**
     * 活动对象
     */
    public function activity()
    {
        return $this->belongsTo('App\Models\v1\Activity', 'goods_id');
    }

    // 获取[订单商品价格] 属性
    public function getGoodsPriceAttribute()
    {
        if ($this->orderGoods)
            return $this->orderGoods->price;
    }

    // 获取[订单商品数量] 属性
    public function getGoodsNumbersAttribute()
    {
        if ($this->orderGoods)
            return $this->orderGoods->numbers;
    }

    // 获取[预约人姓名] 属性
    public function getBookingPersonNameAttribute()
    {
        if ($this->orderGoods)
            return $this->orderGoods->booking_person_name;
    }

    // 获取[预约人电话] 属性
    public function getBookingPersonMobileAttribute()
    {
        if ($this->orderGoods)
            return $this->orderGoods->booking_person_mobile;
    }

    // 获取[活动对象] 属性
    public function getOriginalActivityAttribute()
    {
        if ($this->activity) {
            return $this->activity->setVisible([
                'name',                             // 活动名称
                'thumbnail',                        // 活动缩略图
                'address',                          // 活动地址
                'registered_at',                    // 活动时间
            ]);
        }
    }

    // 获取订单过期时间戳的属性
    public function getExpireTimeAttribute()
    {
        return Carbon::now()->addMinutes(env('ORDER_EXPIRED_TIME'))->timestamp;
    }

    // 取消已经超时的订单
    public static function cancelOvertime()
    {
        // 获取已超时的支付超时记录器
        $wait_pay_ulist = WaitPayUlist::timeout(env('ORDER_EXPIRED_TIME'));
        if ($wait_pay_ulist->isEmpty()) {
            return false;
        }

        // 取消超时支付的订单
        $orders = self::whereIn('id', $wait_pay_ulist->pluck('order_id'))->get();
        $orders->map(function ($order) {
            $order->status = self::STATUS_CANCELED;
            $order->save();

            // 减掉团购商品库存
            Activity::where('id', $order->goods_id)->withoutGlobalScope('showing')->increment('stock_numbers', $order->goods_numbers);

        });

        // 清空支付超时记录器
        WaitPayUlist::destroy($wait_pay_ulist->pluck('id')->toArray());

        Log::info('orders has been canceled at '.Carbon::now());

        return true;
    }

    // 操作 支付超时记录器
    public function setWaitPayUlistAttribute($type=1)
    {
        // 添加当前订单的支付超时记录
        if ($type === 1) {
            $wait_pay_ulist = new WaitPayUlist;
            $wait_pay_ulist->user_id = $this->user_id;
            $wait_pay_ulist->order_id = $this->id;
            $wait_pay_ulist->save();
        }
        // 删除当前订单的支付超时记录
        elseif ($type === 0) {
            WaitPayUlist::where('order_id', $this->id)->delete();
        }
    }

    // 判断该订单对象是否可以退款
    public function isAbleRefund()
    {
        // 检查订单状态是否符合退款
        if ($this->status !== self::STATUS_PAID) {
            self::errorMsg('order_status_error');
            return false;
        }

        // 检查订单中是否有过期的用户优惠券
        $has_expired_uc = 0; // 用户优惠券是否已过期
        $has_already_used = 0; // 用户优惠券是否已使用
        $this->userCoupons->each(function ($user_coupon) use (&$has_expired_uc) {
            // 用户优惠券已过期
            if ($user_coupon->hasExpired()) {
                $has_expired_uc = 1;

                return false;
            }

            // 用户优惠券已使用
            if ($user_coupon->hasAlreadyUsed()) {
                $has_already_used = 1;

                return false;
            }
        });

        // 用户优惠券已过期
        if ($has_expired_uc) {
            self::errorMsg('user_coupon_expired');
            return false;
        }

        // 用户优惠券已使用
        if ($has_already_used) {
            self::errorMsg('user_coupon_used');
            return false;
        }

        return true;
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