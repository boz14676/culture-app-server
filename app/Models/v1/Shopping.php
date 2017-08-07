<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use Carbon\Carbon;
use Auth;
use Log;

class Shopping extends BaseModel
{
    protected $guarded = [];

    protected $appends = [];

    protected $visible = [];

    protected $dates = ['created_at', 'updated_at', 'paymented_at'];

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

    /**
     * 商品下单
     * @param int $goods_id  # 商品ID
     * @param int $numbers   # 商品数量
     * @param array $extra   # 扩展数据
     * @return bool
     */
    public static function orders($goods_id = 0, $numbers = 0, $extra=[])
    {
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));
            return false;
        }

        $now_format = Carbon::now()->toDateString();

        // 查出购买的商品
        if (
            !$goods = Activity::where('start_registered_at', '<=', $now_format)
                ->where('end_registered_at', '>=', $now_format)
                ->find($goods_id)
        )
        {
            self::errorMsg(trans('message.shopping.goods_not_found'));
            return false;
        }

        // 检查商品库存是否充足
        if ($numbers > $goods->stock_numbers) {
            self::errorMsg(tans('message.shopping.stockout'));
            return false;
        }

        // 商品限制用户购买
        if ($goods->limitation_number) {
            // 超出限购的几种情况：1、当次购买数量大于限购数量 2、用户总购买数量大于限购数量
            if (
                $numbers > $goods->limitation_number
                ||
                ($user->getOrderGoodsNumbers($goods_id, 1) + $numbers) > $goods->limitation_number
            )
            {
                self::errorMsg(trans('message.shopping.purchase_limitation'));
                return false;
            }
        }

        // 创建一条订单的记录
        $order = new Order;
        $order->user_id = $user->id;                                     // 用户对象 ID
        $order->goods_id = $goods->id;                                   // 商品对象 ID
        $order->goods_numbers = $numbers;                                // 订单商品数量
        $order->serial_number = rand(111111,999999);                     // 订单编号
        $order->status = Order::STATUS_WAIT_PAY;                         // 订单状态（下单默认为待付款状态）
        $order->amount = $goods->price * $numbers;                       // 订单总额
        $order->save();

        // 创建一条订单商品的记录
        $order_goods = new OrderGoods;
        $order_goods->order_id = $order->id;                                    // 订单对象 ID
        $order_goods->user_id = $user->id;                                      // The ID of User
        $order_goods->order_serial_number = $order->serial_number;              // 订单编号
        $order_goods->goods_id = $goods->id;                                    // The ID of GroupBuying
        $order_goods->price = $goods->price;                                    // 订单商品单价
        $order_goods->amount = $goods->price * $numbers;                        // 订单总额
        $order_goods->numbers = $numbers;                                       // 订单商品数量
        $order_goods->booking_person_name = $extra['booking_person_name'];      // 预约人姓名
        $order_goods->booking_person_mobile = $extra['booking_person_mobile'];  // 预约人手机
        $order_goods->save();

        // 减掉商品库存
        Activity::where('id', $goods_id)->decrement('stock_numbers', $numbers);

        // 添加当前订单的支付超时记录
        $order->wait_pay_ulist = 1;
        $order->makeVisible(['expire_time']);

        return $order;
    }


    // 更改订单状态【已支付】
    public static function completeTransaction($serial_number, $payment_serial_number, $payment_type)
    {
        if (!$order = Order::where('serial_number', $serial_number)->first()) {
            self::errorMsg('订单不存在');
            return false;
        }

        $order->payment_type = $payment_type;
        $order->payment_serial_number = $payment_serial_number;
        $order->status = Order::STATUS_PAID;
        $order->paid_at = Carbon::now();
        $order->save();

        // 删除当前订单的支付超时记录
        $order->wait_pay_ulist = 0;

        // Log::info('order-array-002: '. $order->goods_id); *PASS

        // 查出购买的商品
        if (!$group_buying = GroupBuying::find($order->goods_id))
        {
            self::errorMsg('group_buying_not_found');
            return false;
        }

        // 添加用户优惠券
        if (!$group_buying->coupons->isEmpty()) {
            $group_buying->coupons->map(function ($coupon) use($order) {
                // 遍历购买的团购商品数量
                for ($i=0; $i<$order->goods_numbers; $i++) {
                    Coupon::exchange($coupon,$order);
                }
            });
        }

        return true;
    }

    /**
     * RW-> 退款申请
     *
     * @param null $attributes
     * @param int $operation_type 1 新增退款申请记录 2 查询一个退款申请记录
     * @return RefundApply|bool
     */
    public static function refund($attributes=null, $operation_type=1)
    {
        // 新增退款申请
        if ($operation_type === 1) {
            $order = $attributes['order'];

            // 检查该订单是否可以退款
            if (!$order->isAbleRefund()) {
                self::errorMsg(trans('message.shopping.'.Shopping::errorMsg()));
                return false;
            }

            // 检查订单的状态是否符合退款


            // 写入新退款申请对象
            $refund_apply = new RefundApply;
            $refund_apply->user_id = $attributes['user_id'];                        // 用户ID
            $refund_apply->order_id = $attributes['order_id'];                      // 订单ID
            $refund_apply->serial_number = snowflake_nextid();                      // 退款单号

            $refund_apply->order_serial_number = $order->serial_number;             // 订单编号
            $refund_apply->payment_serial_number = $order->payment_serial_number;   // 第三方交易编号
            $refund_apply->total_fee = $order->amount;                              // 订单总金额

            $refund_apply->name = $attributes['name'];                    // 申请人姓名
            $refund_apply->contactphone = $attributes['contactphone'];    // 申请人联系电话
            $refund_apply->desc = $attributes['desc'];                    // 退款原因
            // 添加一条退款申请记录
            $refund_apply->save();

            // 更改订单状态
            $order->status = Order::STATUS_REFUNDING;
            return $order->save();
        }
        // 查出退款申请记录
        elseif($operation_type === 2) {
            $order = $attributes;

            if (!$refund_apply = RefundApply::where('order_id', $order->id)->first()) {
                return false;
            }

            $append_fields = collect(); // 附加字段
            // 退款申请的状态为已拒绝
            if ($refund_apply->status === RefundApply::STATUS_REFUSED) {
                $append_fields->push('refuse_desc');
            }


            $refund_apply->makeVisible($append_fields->all());

            return $refund_apply;
        }

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