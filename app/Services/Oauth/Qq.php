<?php

namespace App\Services\Oauth;

use Log;
use Cache;

class Qq {
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

    private $host = '';

    /**
     * Qq constructor.
     *
     * @param null $appid
     * @param null $secret
     */
    public function __construct($appid = null, $secret = null) {
        $this->appid = $appid ? : env('QQ_APP_ID');
        $this->secret = $secret ? : env('QQ_APP_SECRET');
        return $this;
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
     * @return bool|mixed
     */
    function request($url, $method, $parameters) {
        return curl_request($url, $method, $parameters);
    }
}