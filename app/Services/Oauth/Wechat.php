<?php

namespace App\Services\Oauth;

use Log;
use Cache;

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
     * @ignore
     */
    private $access_token;
    
    /**
     * @ignore
     */
    private $access_token_global;
    
    /**
     * @ignore
     */
    private $refresh_token;
    /**
     * @ignore
     */
    private $expires_in;
    /**
     * @ignore
     */
    private $openid;
    /**
     * @ignore
     */
    private $unionid;
    /**
     * Set up the API root URL
     *
     * @ignore
     */
    private $host = "https://api.weixin.qq.com/";
    /**
     * Set timeout
     *
     * @ignore
     */
    private $timeout = 5;
    /**
     * Set the runner agnet
     *
     * @ignore
     */
    private $runner_agent = 'Henter WeChat OAuth SDK';
    private $error;
    /**
     * @param $appid
     * @param $secret
     * @param null $access_token
     * @return OAuth
     */
    public function __construct($appid = null, $secret = null, $access_token = null) {
        $this->appid = $appid ? : env('WECHAT_APP_ID');
        $this->secret = $secret ? : env('WECHAT_APP_SECRET');
        $this->access_token = $access_token;
        return $this;
    }
    
    public function error($error = NULL){
        if(is_null($error))
            return $this->error;
        $this->error = $error;
        return false;
    }
    
    /**
     * 获取二维码授权页，用于PC端登陆
     * get qrcode authorize url, with callback url and scope
     *
     * @param $redirect_uri
     * @param string $scope
     * @param null $state
     * @return string
     */
    public function getAuthorizeURL($redirect_uri, $scope = 'snsapi_userinfo', $state = null) {
        $params = array();
        $params['appid'] = $this->appid;
        $params['redirect_uri'] = $redirect_uri;
        $params['response_type'] = 'code';
        $params['scope'] = $scope;
        $params['state'] = $state;
        return "https://open.weixin.qq.com/connect/qrconnect?" . http_build_query($params);
    }



    /**
     * 用户授权，用于微信端登陆
     * get authorize url, with callback url and scope
     *
     * @param $redirect_uri
     * @param string $scope
     * @param null $state
     * @return string
     */
    public function getWeChatAuthorizeURL($redirect_uri, $scope = 'snsapi_userinfo', $state = null) {
        $params = array();
        $params['appid'] = $this->appid;
        $params['redirect_uri'] = $redirect_uri;
        $params['response_type'] = 'code';
        $params['scope'] = $scope;
        $params['state'] = $state;

        // exit("https://open.weixin.qq.com/connect/oauth2/authorize?" . http_build_query($params)."#wechat_redirect");

        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . http_build_query($params)."#wechat_redirect";
    }
    /**
     * @param $access_token
     * @return $this
     */
    public function setAccessToken($access_token){
        $this->access_token = $access_token;
        return $this;
    }

    /**
     * 获取用户的信息
     * @param $code
     * @param $openid
     * @return array|bool
     */
    // public function getUserInfo($code, $openid)
    // {
    //     $this->getAccessToken('code', $code);
    //     $api = "https://api.weixin.qq.com/sns/userinfo?access_token={$this->access_token}&openid={$openid}";
    //     $res = curl_request($api);
    //     if (isset($res['errcode'])) {
    //         Log::error('weixin_oauth_log: '.json_encode($res));
    //         return false;
    //     }
    //     dd($res);
    //
    //     return [
    //         'nickname' => $res['nickname'],
    //         'gender' => $res['sex'],
    //         'prefix' => 'wx',
    //         'avatar' => $res['headimgurl']
    //     ];
    // }

    /**
     * get access_token
     *
     * @param string $type [code|token]
     * @param $key [code|refresh_token]
     * @return string
     */
    public function getAccessToken($type = 'code', $key) {
        $params = array();
        $params['appid'] = $this->appid;
        $params['secret'] = $this->secret;
        if ($type === 'token') {
            $uri = 'sns/oauth2/refresh_token';
            $params['appid'] = $this->appid;
            $params['grant_type'] = 'refresh_token';
            $params['refresh_token'] = $key;
        }elseif($type === 'code') {
            $uri = 'sns/oauth2/access_token';
            $params['appid'] = $this->appid;
            $params['secret'] = $this->secret;
            $params['code'] = $key;
            $params['grant_type'] = 'authorization_code';
        }else{
            return $this->error("wrong auth type");
        }
        $return = json_decode($this->request($this->host.$uri, 'GET', $params), true);

        if(!is_array($return) || !$return)
            return $this->error("get access token failed".$return);
        if (!isset($return['errcode'])){
            $this->access_token = $return['access_token'];
            $this->refresh_token = $return['refresh_token'];
            $this->expires_in = $return['expires_in'];
            $this->openid = $return['openid'];
            $this->unionid = isset($return['unionid']) ? $return['unionid'] : null;
        }else{
            return $this->error("get access token failed: " . $return['errmsg']);
        }
        return $this->access_token;
    }
    
    public function getUser($code)
    {
        $this->getAccessToken('code', $code);

        $api = "https://api.weixin.qq.com/sns/userinfo?access_token={$this->access_token}&openid={$this->openid}";
        $res = json_decode(curl_request($api), true);

        if (isset($res['errcode'])) {
            Log::error('weixin_oauth_log: '.json_encode($res));
            return false;
        }
        
        return [
            'wechat_openid' => $res['openid'],
            'wechat_unionid' => $res['unionid'],
            'nickname' => $res['nickname'],
            'avatar' => $res['headimgurl'],
            'gender' => $res['sex'],
        ];
    }
    
    /**
     * refresh access_token
     *
     * @param string $refresh_token
     * @return string
     */
    public function refreshAccessToken($refresh_token){
        return $this->getAccessToken('token', $refresh_token);
    }
    /**
     * get refresh_token
     * @return string
     */
    public function getRefreshToken(){
        return $this->refresh_token;
    }
    /**
     * get expires time (seconds)
     * @return integer
     */
    public function getExpiresIn(){
        return $this->expires_in;
    }
    
    /**
     * get openid
     * @return string
     */
    public function getOpenid(){
        return $this->openid;
    }
    /**
     * get unionid
     * @return string
     */
    public function getUnionid(){
        return $this->unionid;
    }
    /**
     * request api
     *
     * @param $api
     * @param array $params
     * @param string $method
     * @return array|false
     */
    public function api($api, $params = array(), $method = 'GET'){
        if(!isset($params['access_token']) && !$this->access_token)
            return $this->error('access_token error');
        $params['access_token'] = $this->access_token;
        $return = $this->request($this->host.$api, $method, $params);
        if(!is_array($return) || !$return)
            return $this->error("request failed");
        if (!isset($return['errcode'])) {
            return $return;
        }else{
            return $this->error("request failed: " . $return['errmsg']);
        }
    }
    /**
     * http request wrapper
     * @param $url
     * @param $method
     * @param $parameters
     * @return \Henter\WeChat\Response
     */
    function request($url, $method, $parameters) {
        return curl_request($url, $method, $parameters);
    }
}