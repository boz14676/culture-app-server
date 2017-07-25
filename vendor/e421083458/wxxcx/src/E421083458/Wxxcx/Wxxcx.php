<?php
namespace E421083458\Wxxcx;

use Ixudra\Curl\Facades\Curl;
use Log;

class Wxxcx
{
    /**
     * @var string
     */
    private $appId;
    private $secret;
    private $code2session_url;
    private $openId;
    private $sessionKey;
    private $authInfo;

    /**
     * Wxxcx constructor.
     * @param $code 登录凭证（code）
     */
    function __construct($wxConfig)
    {
        $this->appId = isset($wxConfig["appid"]) ? $wxConfig["appid"] : "";
        $this->secret = isset($wxConfig["secret"]) ? $wxConfig["secret"] : "";
        $this->code2session_url = isset($wxConfig["code2session_url"]) ? $wxConfig["code2session_url"] : "";
    }

    /**
     * Created by e421083458@163.com
     * @return mixed
     */
    public function getLoginInfo($code){
        $res = $this->authCodeAndCode2session($code);
        if($res['error_code'] == 1) {
            return $res;
        }
        $res['data']['session_key'] = $this->authInfo['session_key'];
        return $res;
    }

    /**
     * Created by e421083458@163.com
     * @param $encryptedData
     * @param $iv
     * @return string
     * @throws \Exception
     */
    public function getUserInfo($encryptedData, $iv){
        $pc = new WXBizDataCrypt($this->appId, $this->sessionKey);
        $decodeData = "";
        $errCode = $pc->decryptData($encryptedData, $iv, $decodeData);
        
        if ($errCode !=0 ) {
            return ['error_code' => 1 , 'error_desc' => 'weixin_decode_fail'];
        }
        $decodeData = json_decode($decodeData, true);
        $decodeData['open_id'] = isset($decodeData['unionId']) ? $decodeData['unionId'] : $decodeData['openId'];
    
        $returnData = [
            'unionid' => $decodeData['unionId'],
            'weapp_openid' => $decodeData['openId'],
            'nickname' => $decodeData['nickName'],
            'gender' => $decodeData['gender'],
            'avatar' => $decodeData['avatarUrl']
        ];
        
        return ['error_code'=>0, 'data'=>$returnData];
    }

    /**
     * Created by e421083458@163.com
     * @throws \Exception
     */
    private function authCodeAndCode2session($code){
        
        $querys = http_build_query([
            'appid' => $this->appId,
            'secret' => $this->secret,
            'js_code' => $code,
            'grant_type' => 'authorization_code'
        ]);
        $code2session_url = $this->code2session_url . '?' . $querys;
        
        $jsonData = Curl::to($code2session_url)->get();
        
        $this->authInfo = json_decode($jsonData,true);
        if(!isset($this->authInfo['openid'])){
            return ['error_code' => 1 , 'error_desc' => 'weixin_session_expired'];
            // throw new \Exception('weixin_session_expired');
        }
        $this->openId = $this->authInfo['openid'];
        $this->sessionKey = $this->authInfo['session_key'];
        return ['error_code' => 0];
    }

}