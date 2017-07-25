<?php

namespace App\Services\Wechat;
use Log;
use Cache;
use Curl;

class Wechat {
    /**
     * @ignore
     */
    private $appid;
    /**
     * @ignore
     */
    private $secret;
    
    /**
     * Set up the API root URL
     *
     * @ignore
     */
    private $host = "https://api.weixin.qq.com/";
    
    /**
     * @ignore
     */
    private $access_token;
    
    public function __construct($wechat_type=1, $access_token = true) {
        $this->appid = $wechat_type == 1 ? env('wechat_app_id') : env('weapp_app_id');
        $this->secret = $wechat_type == 1 ? env('wechat_app_secret') : env('weapp_app_secret');
        
        if ($access_token) {
            $this->setAccessToken();
        }
        return $this;
    }
    
    /**
     * get access_token
     *
     * @return string
     */
    public function setAccessToken()
    {
        /*if ($this->access_token || $this->access_token = Cache::get('wechat_access_token')) {
            return $this->access_token;
        }*/
        
        $uri = 'cgi-bin/token';
        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;
        $params['grant_type'] = 'client_credential';
        
        $return = $this->request($this->host.$uri, 'GET', $params);
        
        if(!isset($return['errcode'])) {
            Cache::put('wechat_access_token', $return['access_token'], ($return['expires_in'] / 60) - 2);
            return $this->access_token = $return['access_token'];
        } else {
            Log::info('getAccessTokenGlobal Failed [error_code: '.$return['errcode'].', error_msg: '.$return['errmsg'].']');
        }
    }
    
    public function sendTemplateMsg(array $atrributes = [])
    {
        $uri = 'cgi-bin/message/wxopen/template/send?access_token='.$this->access_token;
        
        $params['touser'] = $atrributes['open_id'];
        $params['template_id'] = $atrributes['template_id'];
        $params['page'] = isset($atrributes['page']) ? $atrributes['page'] : '';
        $params['form_id'] = $atrributes['form_id'];
        $params['color'] = isset($atrributes['color']) ? $atrributes['color'] : '';
        $params['data'] = $atrributes['data'];
        
        $return = Curl::to($this->host.$uri, 'POST')
            ->withData($params)
            ->asJson(true)
            ->post();
        
        if ($return['errcode'] == 0) {
            Log::info('sendTemplateMsg successed [msgid: '.$return['msgid'].']');
            
            return true;
        } else {
            Log::info('sendTemplateMsg failed '.json_encode($return));
            
            return false;
        }
    }
    
    /**
     * http request wrapper
     * @param $url
     * @param $method
     * @param $parameters
     * @return \Henter\WeChat\Response
     */
    function request($url, $method, $parameters, $trans = 1) {
        $return = curl_request($url, $method, $parameters);
        if ($trans == 1) {
            return json_decode($return, true);
        }
        
        return $return;
    }
    
    
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        
        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
    
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        
        //extract post data
        if (!empty($postStr)){
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
               the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = "Welcome to wechat world!";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }
            
        }else {
            echo "";
            exit;
        }
    }
    
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        
        $token = 'Ce5_bQ1eGgJa07bHyMoDdtR';
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}