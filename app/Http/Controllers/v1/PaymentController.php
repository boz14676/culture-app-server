<?php

namespace App\Http\Controllers\v1;

use App\Jobs\OrderStatus;
use App\Services\Proxy\Notify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Alipay\v1\AlipayRSA;
use App\Services\Alipay\v1\AlipayNotify;
use App\Services\Alipay\v1\AlipaySubmit;
use App\Services\Alipay\v2\AopClient;
use App\Services\Alipay\v2\Builder\AlipayTradeWapPayContentBuilder;
use App\Services\Alipay\v2\Request\AlipayTradeWapPayRequest;
use App\Services\Alipay\v2\Request\AlipayTradeRefundRequest;
use App\Services\Wxpay\WxPay;
use App\Services\Wxpay\WxResponse;
use App\Services\Wxpay\JSSDK;
use App\Models\v1\Order;
use App\Models\v1\Shopping;
use Log;
use Amqp;
use Queue;

class PaymentController extends Controller
{
    /**
    * GET /payments
    */
    public function _list()
    {
        $payment_config = config('payment.channel');

        $data = [];

        foreach ($payment_config as $key => $value) {
            if ($value['enabled'] == true) {
                $data[] = str_replace('_', '.', $key);
            }
        }

        return $this->body(['payments' => $data]);
    }

    public function notify($code)
    {
        Log::info('notified: ['.json_encode($_REQUEST).'], code: '.$code);

        if ($code == 'alipay.app') {

            $config = config('payment.channel.alipay_app');
            $out_trade_no = isset($_POST['out_trade_no']) ? $_POST['out_trade_no'] : 0;

            if (!$order = Order::where('order_no', $out_trade_no)->first()) {
                return $this->error(self::BAD_REQUEST, '订单不存在');
            }

            $alipay_config = array(
                "partner"           => $config['partner_id'],
                "alipay_public_key" => keyToPem($config['public_key']),
                "sign_type"         => strtoupper('RSA'),
                "input_charset"     => strtolower('utf-8'),
                "cacert"            => app()->basePath() . '/app/Services/Alipay/cacert.pem',
                "transport"         => "http",
                "notify_url"        => config('payment.notify_host') . '/v1/payment.notify.alipay.app',
            );

            $alipayNotify = new AlipayNotify($alipay_config);
            $verify_result = $alipayNotify->verifyNotify();

            if($verify_result) {
                //验证成功
                if (empty($_POST['out_trade_no']) && !empty($_POST['notify_data'])) {
                    $_POST = json_decode(json_encode(simplexml_load_string($_POST['notify_data'])), true);
                }

                $trade_status = $_POST['trade_status'];
                $trade_no = $_POST['trade_no'];

                if($_POST['trade_status'] == 'TRADE_FINISHED') {
                    $this->updateOrderStatus($order->id, $trade_no, 1);
                }
                else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                    $this->updateOrderStatus($order->id, $trade_no, 1);
                }
                else
                {
                    Log::error('订单支付回调处理异常: '.$out_trade_no);
                    Log::error('TRADE_STATUS:'.$_POST['trade_status']);
                }

                echo "success";

            } else {
                Log::info('订单支付回调故障: '.$out_trade_no);
                echo "fail";
            }

            exit;
        }


        if ($code == 'alipay.web') {

            $config = config('payment.channel.alipay_web');
            $out_trade_no = isset($_POST['out_trade_no']) ? $_POST['out_trade_no'] : 0;

            $alipay_config = array(
                "partner"           => $config['partner_id'],
                "alipay_public_key" => keyToPem($config['public_key']),
                "sign_type"         => strtoupper('RSA'),
                "input_charset"     => strtolower('utf-8'),
                "cacert"            => app()->basePath() . '/app/Services/Alipay/cacert.pem',
                "transport"         => "http",
                "notify_url"        => config('payment.notify_host') . '/v1/payment.notify.alipay.app',
            );

            $alipayNotify = new AlipayNotify($alipay_config);
            $verify_result = $alipayNotify->verifyNotify();

            // TODO: 换上正确的key加上verify_result的验证

            // if($verify_result) {
                //验证成功
                if (empty($_POST['out_trade_no']) && !empty($_POST['notify_data'])) {
                    $_POST = json_decode(json_encode(simplexml_load_string($_POST['notify_data'])), true);
                }

                $trade_status = $_POST['trade_status'];
                $trade_no = $_POST['trade_no'];

                if($_POST['trade_status'] == 'TRADE_FINISHED') {
                    // 更新订单的支付状态
                    if (!Shopping::completeTransaction($out_trade_no, $trade_no, $code)) {
                        Log::error(Shopping::errorMsg());
                    }
                }
                else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                    // 更新订单的支付状态
                    if (!Shopping::completeTransaction($out_trade_no, $trade_no, $code)) {
                        Log::error(Shopping::errorMsg());
                    }
                }
                else
                {
                    Log::error('订单支付回调处理异常: '.$out_trade_no);
                    Log::error('TRADE_STATUS:'.$_POST['trade_status']);
                }

                echo "success";

            // } else {
            //     Log::info('订单支付回调故障: '.$out_trade_no);
            //     echo "fail";
            // }

            exit;
        }

        if ($code == 'wxpay.app') {

            $config = config('payment.channel.wxpay_app');

            if (version_compare(PHP_VERSION, '5.6.0', '<')) {
                if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
                    $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
                } else {
                    $postStr = file_get_contents('php://input');
                }
            } else {
                $postStr = file_get_contents('php://input');
            }

            if (empty($postStr)) {
                return $this->error(self::BAD_REQUEST, '请求参数错误');
            }

            /* 创建支付应答对象 */
            $resHandler = new WxResponse();

            $inputParams = $resHandler->xmlToArray($postStr);

            foreach($inputParams as $k => $v) {
                $resHandler->setParameter($k, $v);
            }

            $out_trade_no = $resHandler->getParameter("out_trade_no");

            if (!$order = Order::where('order_no', $out_trade_no)->first()) {
                return $this->error(self::BAD_REQUEST, '订单不存在');
            }

            $resHandler->setKey($config['mch_key']);

            //判断签名
            if($resHandler->isTenpaySign() == true) {

                //支付结果
                $return_code = $resHandler->getParameter("return_code");

                //判断签名及结果
                if ("SUCCESS" == $return_code){

                    //商户在收到后台通知后根据通知ID向财付通发起验证确认，采用后台系统调用交互模式
                    //商户交易单号
                    $out_trade_no = $resHandler->getParameter("out_trade_no");

                    //财付通订单号
                    $transaction_id = $resHandler->getParameter("transaction_id");

                    $this->updateOrderStatus($order->id, $transaction_id, 1);

                } else {
                    Log::error('后台通知失败');
                }
                //回复服务器处理成功
                echo $resHandler->getSucessXml();
            } else {
                echo $resHandler->getFailXml();
                Log::error("验证签名失败");
            }

            exit;
        }

        if ($code == 'wxpay.web') {

            $config = config('payment.channel.wxpay_web');

            if (version_compare(PHP_VERSION, '5.6.0', '<')) {
                if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
                    $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
                } else {
                    $postStr = file_get_contents('php://input');
                }
            } else {
                $postStr = file_get_contents('php://input');
            }

            if (empty($postStr)) {
                return $this->error(self::BAD_REQUEST, '请求参数错误');
            }

            /* 创建支付应答对象 */
            $resHandler = new WxResponse();

            $inputParams = $resHandler->xmlToArray($postStr);

            foreach($inputParams as $k => $v) {
                $resHandler->setParameter($k, $v);
            }

            $out_trade_no = $resHandler->getParameter("out_trade_no");

            $resHandler->setKey($config['mch_key']);

            //判断签名
            if($resHandler->isTenpaySign() == true) {

                //支付结果
                $return_code = $resHandler->getParameter("return_code");

                //判断签名及结果
                if ("SUCCESS" == $return_code){

                    //商户在收到后台通知后根据通知ID向财付通发起验证确认，采用后台系统调用交互模式
                    //商户交易单号
                    $out_trade_no = $resHandler->getParameter("out_trade_no");

                    //财付通订单号
                    $transaction_id = $resHandler->getParameter("transaction_id");

                    // 更新订单的支付状态
                    if (!Shopping::completeTransaction($out_trade_no, $transaction_id, $code)) {
                        $this->error(self::BAD_REQUEST, Shopping::errorMsg(), 1);
                    }
                    Log::info('后台通知成功');
                    /*if (config('payment.apply_host') && config('payment.apply_api')) {
                        $key = config('payment.apply_key');
                        $sign = md5(md5($out_trade_no . $transaction_id) . $key);
                        Queue::push(new OrderStatus($out_trade_no, $transaction_id, $sign));
                    }*/

                } else {
                    Log::error('后台通知失败');
                }
                //回复服务器处理成功
                echo $resHandler->getSucessXml();
            } else {
                echo $resHandler->getFailXml();
                Log::error("验证签名失败");
            }

            exit;
        }
    }
}
