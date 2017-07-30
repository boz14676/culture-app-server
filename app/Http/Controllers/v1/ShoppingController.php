<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Shopping;
use App\Models\v1\Order;
use App\Services\Payment;
use Log;

class ShoppingController extends Controller
{

    /**
     * POST /shopping/orders 购买活动 商品
     */
    public function orders()
    {
        $rules = [
            'goods_id' => 'required|integer|min:1',
            'goods_numbers' => 'required|integer|min:1|max:5',
            'extra' => 'required|array',
            'extra.booking_person_name' => 'required|string',
            'extra.booking_person_mobile' => 'required|integer',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        // 下单
        $goods_id = $this->request->input('goods_id');       // 商品ID
        $numbers = $this->request->input('goods_numbers');   // 商品数量
        $extra = $this->request->input('extra');             // 扩展数据
        if($order = Shopping::orders($goods_id, $numbers, $extra)) {
            return self::body(['order'=>$order]);
        }

        return self::error(self::BAD_REQUEST, Shopping::errorMsg());
    }

    /**
     * api.shopping.orders 支付订单
     */
    public function pays()
    {
        $rules = [
            'order_id' => 'required|integer|min:1',
            'payment_type' => 'required|string|in:wxpay.web,alipay.web',
            'openid' => 'required_if:payment_type,wxpay.web|string',
            'referer_url' => 'required_if:payment_type,alipay.web|url'
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $order_id = $this->request->input('order_id');
        $payment_type = $this->request->input('payment_type');

        $user = $this->request->user();

        if (!$order = Order::find($order_id)) {
            return $this->error(self::BAD_REQUEST, 'order_not_found', 1);
        }

        // 检查当前用户是否有操作改订单的权限
        if ($user->cannot('operate-order', $order)) {
            return $this->error(self::UNAUTHORIZED);
        }

        // 检查当前订单是否已经被取消，被取消的订单不能进行操作
        if ($order->isCancelled()) {
            return $this->error(self::BAD_REQUEST, 'order_has_cancelled', 1);
        }

        // 微信支付
        if ($payment_type == 'wxpay.web') {
            $openid = $this->request->input('openid');

            // go to pay
            $payment = new Payment;
            $return_data = $payment->pay(2, [
                'code' => $payment_type,
                'openid' => $openid,
                'nonce_str' => str_random(32),
                'body' => $order->goods_name,
                'subject' => $order->type_string,
                'order_no' => $order->serial_number,
                'amount' => $order->amount,
                'client_ip' => $this->request->ip(),
            ]);

            // http请求
            return $this->body([
                'payment_type' => $payment_type,
                'config_data' => $return_data
            ]);
        }
        // 支付宝支付
        elseif ($payment_type == 'alipay.web') {
            $referer_url = $this->request->input('referer_url');

            // go to pay
            $payment = new Payment;
            $return_data = $payment->pay(2, [
                'code' => $payment_type,
                'return_url' => $referer_url,
                'order_no' => $order->serial_number,
                'subject' => $order->goods_name,
                'body' => '',
                'amount' => $order->amount,
            ]);

            return $this->body([
                'payment_type' => $payment_type,
                'html' => $return_data
            ]);
        }
    }

    /**
     * api.shopping.refund.{attribute} 退款申请
     */
    public function refundApply($attribute)
    {
        $rules = [
            'order_id' => 'required|integer|min:1',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        // 用户对象
        if (!$user = $this->request->user()) return $this->error(self::NOT_FOUND);

        $order_id = $this->request->input('order_id'); // 订单ID
        $user_id = $user->id;                          // 用户ID

        // 订单对象
        if (!$order = Order::find($order_id)) return $this->error(self::NOT_FOUND);

        // 检查当前用户是否有操作改订单的权限
        if ($user->cannot('operate-order', $order)) {
            return $this->error(self::UNAUTHORIZED);
        }

        // 新增退款申请
        if ($attribute === 'apply') {
            $rules = [
                'name' => 'required|string',
                'contactphone' => 'required|string',
                'desc' => 'required|string',
            ];

            if ($error = $this->validateInput($rules)) {
                return $error;
            }

            // 组装退款申请对象属性
            $attributes = [
                'order' => $order,
                'order_id' => $order_id,
                'user_id' => $user_id,
                'name' => $this->request->input('name'),
                'contactphone' => $this->request->input('contactphone'),
                'desc' => $this->request->input('desc'),
            ];
            if (!Shopping::refund($attributes, 1)) {
                return $this->error(self::BAD_REQUEST, Shopping::errorMsg(), 1);
            }

            return $this->body();
        }
        // 查询当前订单的退款申请
        elseif ($attribute === 'get') {

            if (!$refund = Shopping::refund($order, 2)) {
                return $this->error();
            }

            return $this->body(['refund' => $refund]);
        }

        return $this->error();
    }
}