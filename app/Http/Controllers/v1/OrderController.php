<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\Order;
use App\Models\v1\UserCoupon;

class OrderController extends Controller
{

    /**
     * GET order/:id 获取订单
     */
    public function get($id)
    {
        if($order = Order::repository($id)) {
            return self::body(['data' => $order]);
        }

        return self::error(self::NOT_FOUND);
    }

    /**
     * api.order.get 订单列表
     */
    public function _lists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            's.*' => 'string',
            'q.*' => 'string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 筛选
        $s = $this->request->input('s');                                        // 排序

        if ($order = Order::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $order]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }
}