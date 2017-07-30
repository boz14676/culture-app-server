<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\User;

class UserController extends Controller
{
    const VENDOR_WEIXIN = 1;
    const VENDOR_WEIBO = 2;
    const VENDOR_QQ = 3;
    const VENDOR_TAOBAO = 4;

    /**
     * POST user/code 发送验证码
     */
    public function sendCode()
    {
        $rules = [
            'mobile'  => 'required|integer',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $mobile = $this->request->input('mobile');

        $res = User::sendVerifyCode($mobile);

        if ($res) {
            return $this->body();
        } else {
            return $this->error(self::BAD_REQUEST, trans('message.user.send_code_error'));
        }

    }

    /**
     * POST user/register 注册
     */
    public function register()
    {
        $rules = [
            'mobile' => 'required|regex:/^[0-9]{11}$/|unique:users',
            'password' => 'regex:/^[a-zA-Z0-9]{6,10}$/',
            'code' => 'required|string|size:6',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $mobile = $this->request->input('mobile');            // 手机号
        $password = $this->request->input('password', null);  // 密码
        $code = $this->request->input('code');                // 验证码

        if ($user = User::register($mobile, $password, $code)) {
            return self::body(['data' => $user->makeVisible(['token'])]);
        }

        return $this->error(self::BAD_REQUEST, User::errorMsg());
    }

    /**
     * GET user/login 登录
     */
    public function login()
    {
        $rules = [
            'mobile' => 'required|regex:/^[0-9]{11}$/',
            'password' => 'required|regex:/^[a-zA-Z0-9]{6,10}$/',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $mobile = $this->request->input('mobile');      // 手机号
        $password = $this->request->input('password');  // 密码

        if ($user = User::login($mobile, $password)) {
            return self::body(['data' => $user->makeVisible(['token'])]);
        }

        return $this->error(self::BAD_REQUEST, User::errorMsg());
    }


    /**
     * GET user/verify_original_password 验证原始密码
     */
    public function chekcOriginalPassword()
    {
        $rules = [
            'original_password' => 'required|regex:/^[a-zA-Z0-9]{6,10}$/',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $original_password = $this->request->input('original_password');  // 密码

        if (User::chekcOriginalPassword($original_password)) {
            return self::body();
        }

        return $this->error(self::BAD_REQUEST, User::errorMsg());
    }

    /**
     * PUT user/password/update 修改密码
     */
    public function updatePassword()
    {
        $rules = [
            'password' => 'required|regex:/^[a-zA-Z0-9]{6,10}$/',
            'repeat_password' => 'required|regex:/^[a-zA-Z0-9]{6,10}$/|same:password',
        ];

        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $password = $this->request->input('password');  // 密码

        if (User::updatePassword($password)) {
            return self::body();
        }

        return $this->error(self::BAD_REQUEST, User::errorMsg());
    }

    /**
     * GET /api.user.get
     */
    public function get()
    {
        if (!$this->request->user()) {
            return $this->error(self::NOT_FOUND);
        }

        return $this->body(['user' => $this->request->user()]);
    }

    /**
     * GET /api.user.:attribute.update
     */
    public function update($attribute)
    {
        if (!$user = $this->request->user()) {
            return $this->error(self::NOT_FOUND);
        }

        // 更改头像
        if ($attribute === 'avatar') {
            $rules = [
                'photo' => 'required|image',
            ];
            if ($error = $this->validateInput($rules)) {
                return $error;
            }

            $photo = $this->request->file('photo');

            if ($user->updates('avatar', $photo)) {
                return $this->body(['avatar' => $user->avatar]);
            }
        }

        // 更改昵称
        elseif ($attribute === 'profiles') {
            $rules = [
                'nickname' => 'required|string',
            ];
            if ($error = $this->validateInput($rules)) {
                return $error;
            }

            $nickname = $this->request->input('nickname');
            $user->nickname = $nickname;
            $user->save();

            return $this->body();
        }

        return $this->error(self::NOT_FOUND);
    }
}