<?php

namespace App\Services;

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
use Log;

class Payment
{
    private static $error_msg = '';

    // errorMsg [set&get]
    public static function errorMsg($error_msg = '')
    {
        // set
        if ($error_msg) {
            self::$error_msg = $error_msg;
            return true;
        }
        // get
        else
        {
            if (self::$error_msg) {
                return self::$error_msg;
            }

            return '';
        }
    }

    /**
     * @param int $type
     * @param array $attributes
     * @return mixed
     */
    public function pay($type = 1, $attributes=[])
    {

        $code = isset($attributes['code']) ? $attributes['code'] : '';
        $order_no = isset($attributes['order_no']) ? $attributes['order_no'] : '';
        $client_ip = isset($attributes['client_ip']) ? $attributes['client_ip'] : '';
        $amount = isset($attributes['amount']) ? $attributes['amount'] : '';
        $subject = isset($attributes['subject']) ? $attributes['subject'] : '';
        $body = isset($attributes['body']) ? $attributes['body'] : '';
        $openid = isset($attributes['openid']) ? $attributes['openid'] : '';
        $return_url = isset($attributes['return_url']) ? $attributes['return_url'] : '';
        $expired_at = isset($attributes['expired_at']) ? $attributes['expired_at'] : '';
        $extra = isset($attributes['extra']) ? $attributes['extra'] : '';

        if ($code == 'alipay.app') {

            if (!config('payment.channel.alipay_app.enabled')) {
                self::errorMsg('支付方式未开启');
                return false;
            }

            $config = config('payment.channel.alipay_app');

            $data = [
                "notify_url"     => config('payment.notify_host') . '/v1/payment.notify.alipay.app',
                "partner"        => $config['partner_id'],
                "seller_id"      => $config['seller_id'],
                "out_trade_no"   => $order_no,
                "subject"        => $subject,
                "body"           => $body,
                "total_fee"      => number_format($amount, 2, '.', ''),
                "service"        => "mobile.securitypay.pay",
                "payment_type"   => "1",
                "_input_charset" => "utf-8",
                "it_b_pay"       => "30m",
                "show_url"       => "m.alipay.com"
            ];

            $sign = AlipayRSA::rsaSign(AlipayRSA::getSignContent($data), keyToPem($config['private_key'], true));
            $data['sign'] = $sign;
            $data['sign_type'] = 'RSA';

            return http_build_query($data);
        }

        if ($code == 'wxpay.app') {

            if (!config('payment.channel.wxpay_app.enabled')) {
                self::errorMsg('支付方式未开启');
                return false;
            }

            $config = config('payment.channel.wxpay_app');

            $wxpay = new WxPay();
            $wxpay->init($config['app_id'], $config['app_secret'], $config['mch_key']);
            $nonce_str = str_random(32);
            $time_stamp = time();
            $pack = 'Sign=WXPay';

            $inputParams = [

                //公众账号ID
                'appid' => $config['app_id'],

                //商户号
                'mch_id' => $config['mch_id'],

                'device_info' => '1000',

                //随机字符串
                'nonce_str' => $nonce_str,

                //商品描述
                'body' => $body,

                'attach' => $subject,

                //商户订单号
                'out_trade_no' => $order_no,

                //总金额
                'total_fee' => $amount * 100,
                // 'total_fee' => 1,

                //终端IP
                'spbill_create_ip' => $client_ip,

                //接受微信支付异步通知回调地址
                'notify_url' => config('payment.notify_host') . '/v1/payment.notify.Wxpay.app',

                //交易类型:JSAPI,NATIVE,APP
                'trade_type' => 'APP'
            ];

            $inputParams['sign'] = $wxpay->createMd5Sign($inputParams);

            //获取prepayid
            $prepayid = $wxpay->sendPrepay($inputParams);

            $prePayParams = [
                'appid' => $config['app_id'],
                'partnerid' => $config['mch_id'],
                'prepayid' => $prepayid['prepay_id'],
                'package' => $pack,
                'noncestr' => $nonce_str,
                'timestamp' => $time_stamp,
            ];

            //生成签名
            $sign = $wxpay->createMd5Sign($prePayParams);

            $body = [
                'appid' => $config['app_id'],
                'mch_id' => $config['mch_id'],
                'prepay_id' => $prepayid['prepay_id'],
                'nonce_str' => $nonce_str,
                'timestamp' => $time_stamp,
                'packages' => $pack,
                'sign' => $sign,
            ];
            return $body;
        }

        if ($code == 'wxpay.web') {

            if (!config('payment.channel.wxpay_web.enabled')) {
                self::errorMsg('支付方式未开启');
                return false;
            }

            $config = config('payment.channel.wxpay_web');

            $wxpay = new WxPay();
            $wxpay->init($config['app_id'], $config['app_secret'], $config['mch_key']);
            $nonce_str = str_random(32);
            $time_stamp = (string)time();

            $inputParams = [

                //公众账号ID
                'appid' => $config['app_id'],

                //商户号
                'mch_id' => $config['mch_id'],

                //openid
                'openid' => $openid,

                'device_info' => '1000',

                //随机字符串
                'nonce_str' => $nonce_str,

                //商品描述
                'body' => $body,

                'attach' => $subject,

                //商户订单号
                'out_trade_no' => $order_no,

                //总金额
                'total_fee' => $amount * 100,
                // 'total_fee' => 1,

                //终端IP
                'spbill_create_ip' => $client_ip,
                //接受微信支付异步通知回调地址
                'notify_url' => config('payment.notify_host') . '/v1/payment.notify.wxpay.web',

                //交易类型:JSAPI,NATIVE,APP
                'trade_type' => 'JSAPI'
            ];

            $inputParams['sign'] = $wxpay->createMd5Sign($inputParams);

            //获取prepayid
            $prepayid = $wxpay->sendPrepay($inputParams);

            $pack = 'prepay_id='.$prepayid['prepay_id'];

            $prePayParams = [
                'appId'     => $config['app_id'],
                'timeStamp' => $time_stamp,
                'package'   => $pack,
                'nonceStr'  => $nonce_str,
                'signType'  => 'MD5'
            ];

            // $jssdk = new JSSDK($config['app_id'], $config['app_secret']);
            // $js_config = $jssdk->GetSignPackage();

            //生成签名
            $sign = $wxpay->createMd5Sign($prePayParams);

            $body = [
                'app_id' => $config['app_id'],
                'time_stamp' => $time_stamp,
                'nonce_str' => $nonce_str,
                'package' => $pack,
                'sign_type' => 'MD5',
                'pay_sign' => $sign,
            ];

            return $body;
        }

        if ($code == 'alipay.web') {

            if (!config('payment.channel.alipay_web.enabled')) {
                self::errorMsg('支付方式未开启');
                return false;
            }

            $config = config('payment.channel.alipay_web');
            if ($config['app_ver'] == 'v2') {
                //builder
                $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
                $payRequestBuilder->setBody($body);
                $payRequestBuilder->setSubject($subject);
                $payRequestBuilder->setOutTradeNo($order_no);
                $payRequestBuilder->setTotalAmount($amount);
                $payRequestBuilder->setTimeExpress('15m');

                $request = new AlipayTradeWapPayRequest();
                $request->setNotifyUrl(config('payment.notify_host') . '/v1/payment.notify.alipay.web');
                $request->setReturnUrl($return_url);
                $request->setBizContent ($payRequestBuilder->getBizContent());

                $aop = new AopClient();
                $aop->appId = $config['app_id'];
                $aop->rsaPrivateKey =  $config['private_key'];
                $aop->alipayrsaPublicKey = $config['public_key'];
                $html_text = $aop->pageExecute($request,"post");
                return $html_text;
            }

            if ($config['app_ver'] == 'v1') {

                //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://openhome.alipay.com/platform/keyManage.htm?keyType=partner
                $alipay_config['partner']       = $config['partner_id'];

                //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
                $alipay_config['seller_id']     = $alipay_config['partner'];

                //商户的私钥,此处填写原始私钥去头去尾，RSA公私钥生成：https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.nBDxfy&treeId=58&articleId=103242&docType=1
                $alipay_config['private_key']   = keyToPem($config['private_key'], true);

                //支付宝的公钥，查看地址：https://b.alipay.com/order/pidAndKey.htm
                $alipay_config['alipay_public_key']= keyToPem($config['public_key']);

                // 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
                $alipay_config['notify_url'] = config('payment.notify_host') . '/v1/payment.notify.alipay.web';

                // 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
                $alipay_config['return_url'] = $return_url;

                //签名方式
                $alipay_config['sign_type']    = strtoupper('RSA');

                //字符编码格式 目前支持 gbk 或 utf-8
                $alipay_config['input_charset']= strtolower('utf-8');

                //ca证书路径地址，用于curl中ssl校验
                $alipay_config['cacert']    = app()->basePath() . '/app/Services/Alipay/cacert.pem';

                //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                $alipay_config['transport']    = 'http';

                // 支付类型 ，无需修改
                $alipay_config['payment_type'] = "1";

                // 产品类型，无需修改
                $alipay_config['service'] = "create_direct_pay_by_user";

                //↓↓↓↓↓↓↓↓↓↓ 请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓

                // 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
                $alipay_config['anti_phishing_key'] = "";

                // 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
                $alipay_config['exter_invoke_ip'] = "";

                //构造要请求的参数数组，无需改动
                $parameter = array(
                    "service"           => $alipay_config['service'],
                    "partner"           => $alipay_config['partner'],
                    "seller_id"         => $alipay_config['seller_id'],
                    "payment_type"      => $alipay_config['payment_type'],
                    "notify_url"        => $alipay_config['notify_url'],
                    "return_url"        => $alipay_config['return_url'],
                    "anti_phishing_key" => $alipay_config['anti_phishing_key'],
                    "exter_invoke_ip"   => $alipay_config['exter_invoke_ip'],
                    "out_trade_no"      => $order_no,
                    "subject"           => $subject,
                    "total_fee"         => $amount,
                    "body"              => $body,
                    "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
                );

                //建立请求
                $alipaySubmit = new AlipaySubmit($alipay_config);
                $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
                return $html_text;
            }
        }
    }

    /**
     * @param int $opt
     * @param array $ext_attributes
     * @return array|bool
     */
    public function refund($opt=1, $ext_attributes=[])
    {
        $code = $ext_attributes['code'];
        $out_trade_no = $ext_attributes['out_trade_no'];
        $refund_fee = $ext_attributes['refund_fee'];
        $out_refund_no = $ext_attributes['out_refund_no'];
        $total_fee = $ext_attributes['total_fee'];

        if ($code == 'alipay.web') {
            $config = config('payment.channel.alipay_web');
            $aop = new AopClient();
            $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';

            $aop->appId = $config['app_id'];
            $aop->rsaPrivateKey = $config['private_key'];
            $aop->alipayrsaPublicKey=$config['public_key'];
            $aop->apiVersion = '1.0';
            $aop->signType = 'RSA';
            $aop->format='json';

            $request = new AlipayTradeRefundRequest();
            $request->setBizContent(
                json_encode(["out_trade_no" => $out_trade_no,"refund_amount" => $refund_fee])
            );
            $result = $aop->execute($request);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            if(!empty($resultCode)&&$resultCode == 10000){
                $res_arr['_error'] = 1;
            } else {
                $res_arr['_error'] = 0;
            }

            return $res_arr;
        }

        if ($code == 'wxpay.web') {
            $config = config('payment.channel.wxpay_web');
            $params['appid'] = $config['app_id']; //
            $params['mch_id'] = $config['mch_id']; //
            $params['nonce_str'] = str_random(32);; //
            $params['out_trade_no'] = $out_trade_no; //
            $params['out_refund_no'] = $out_refund_no; //
            $params['total_fee'] = $total_fee * 100; //
            $params['refund_fee'] = $refund_fee * 100; //
            $params['op_user_id'] = $config['mch_id']; // 默认商户号

            $wxpay = new WxPay();
            $wxpay->init($config['app_id'], $config['app_secret'], $config['mch_key']);
            $params['sign'] = $wxpay->createMd5Sign($params);

            $url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
            $xmlstr = "<xml><appid>{$params['appid']}</appid><mch_id>{$params['mch_id']}</mch_id><nonce_str>{$params['nonce_str']}</nonce_str><op_user_id>{$params['op_user_id']}</op_user_id><out_refund_no>{$params['out_refund_no']}</out_refund_no><out_trade_no>{$params['out_trade_no']}</out_trade_no><refund_fee>{$params['refund_fee']}</refund_fee><total_fee>{$params['total_fee']}</total_fee><sign>{$params['sign']}</sign></xml>";
            $res = curl_post_ssl($url, $xmlstr);
            $res_arr = $wxpay->xmlToArray($res);

            if ($res_arr['return_code'] == 'SUCCESS') {
                if (isset($res_arr['err_code'])) {
                    $res_arr['_error'] = 1;
                    $res_arr['_error_msg'] = $res_arr['err_code_des'];
                }
                // 内部方法调用
                if ($opt === 2) {
                    return $res_arr;
                }
            } else {
                $res_arr['_error'] = 1;
                $res_arr['_error_msg'] = $res_arr['return_msg'];
                return $res_arr;
            }

            Log::info('order-refund-failed: [ '.json_encode($res_arr).' ] osn: '.$out_trade_no);
            return false;
        }
    }
}
