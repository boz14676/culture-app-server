<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\Oauth\Wechat;
use App\Models\v2\User;


class WePullController extends Controller
{
    
    /**
     * GET /urun.auth.web
     */
    public function webOauth(Request $request)
    {
        $rules = [
            'referer' => 'required|url'
        ];
        
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $wechat = new Wechat();
        $url = $wechat->getWeChatAuthorizeURL(url('/v2/urun.auth.web.callback/?referer='.$request->input('referer')));
        
        return redirect($url);
    }
    
    /**
     * GET /urun.auth.web.callback
     */
    public function webCallback()
    {
        $wechat = new Wechat();
    
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (!$access_token = $wechat->getAccessToken('code', $code)) {
            return self::formatError(self::BAD_REQUEST, trans('message.runner.auth_error'));
        }
    
        if ($wechat_user = $wechat->getUser($access_token)) {
            User::createUser($wechat_user, 2);
        }
    
        // return redirect('/file/message/message.html');
        return redirect('https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzIwMDQxMTg2NA==&scene=123&from=singlemessage&isappinstalled=0#wechat_redirect');

        // return $this->body(['data' => '绑定成功']);
    }
}
