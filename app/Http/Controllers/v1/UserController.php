<?php
namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\v1\UserCollect;
use App\Models\v1\UserComment;
use App\Models\v1\UserLikes;
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
     * GET /user
     */
    public function get()
    {
        if ($this->request->user()) {
            return $this->body(['user' => $this->request->user()]);
        }

        return $this->error(self::NOT_FOUND);
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

    /**
     * GET /user/comments 获取用户的评论(s)
     */
    public function commentLists()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',
            'q' => 'array',
            's' => 'array',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 搜索
        $s = $this->request->input('s');                                        // 排序

        // 获取用户
        if (!$user = $this->request->user()) {
            return $this->error(self::UNKNOWN_ERROR);
        }

        if ($comment = $user->commentRepositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $comment]);
        }

        return $this->error(self::UNKNOWN_ERROR);
    }

    /**
     * POST /user/comment 写评论
     */
    public function writeComment()
    {
        $rules = [
            'commentable_type'  => 'required|string',
            'commentable_id' => 'required|integer',
            'details' => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $commentable_type = $this->request->input('commentable_type');  // 主题类型
        $commentable_id = $this->request->input('commentable_id');      // 主题ID
        $details = $this->request->input('details');                    // 内容

        if ($comment = UserComment::write($commentable_type, $commentable_id, $details)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, UserComment::errorMsg());
    }

    /**
     * POST /user/comment 点赞
     */
    public function likes()
    {
        $rules = [
            'likesable_type'  => 'required|string',
            'likesable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $likesable_type = $this->request->input('likesable_type');  // 主题类型
        $likesable_id = $this->request->input('likesable_id');      // 主题ID

        if (UserLikes::add($likesable_type, $likesable_id)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, UserComment::errorMsg());
    }

    /**
     * DELETE /user/comment 取消点赞
     */
    public function unlikes()
    {
        $rules = [
            'likesable_type'  => 'required|string',
            'likesable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $likesable_type = $this->request->input('likesable_type');  // 主题类型
        $likesable_id = $this->request->input('likesable_id');      // 主题ID

        if (UserLikes::remove($likesable_type, $likesable_id)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, UserLikes::errorMsg());
    }

    /**
     * GET /user/collects 获取用户的收藏(s)
     */
    public function getUserCollects()
    {
        $rules = [
            'page'      => 'required|integer|min:1',
            'per_page'  => 'required|integer|min:1',

            'q.collectable_type'  => 'string',
            'q.collectable_id' => 'integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $per_page = $this->request->input('per_page');                          // 每页显示记录数
        $q = $this->request->input('q');                                        // 筛选
        $s = $this->request->input('s');                                        // 排序

        if ($user_collects = UserCollect::repositories($per_page, $q, $s)) {
            return $this->formatPaged(['data' => $user_collects]);
        }

        return $this->error(self::BAD_REQUEST, UserLikes::errorMsg());
    }

    /**
     * POST /user/collect 收藏
     */
    public function UserCollects()
    {
        $rules = [
            'collectable_type'  => 'required|string',
            'collectable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $collectable_type = $this->request->input('collectable_type');  // 主题类型
        $collectable_id = $this->request->input('collectable_id');      // 主题ID

        if (UserCollect::add($collectable_type, $collectable_id)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, UserComment::errorMsg());
    }

    /**
     * DELETE /user/collect 取消收藏
     */
    public function UserUncollects()
    {
        $rules = [
            'collectable_type'  => 'required|string',
            'collectable_id' => 'required|integer',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        $collectable_type = $this->request->input('collectable_type');  // 主题类型
        $collectable_id = $this->request->input('collectable_id');      // 主题ID

        if (UserCollect::remove($collectable_type, $collectable_id)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, UserComment::errorMsg());
    }

    /**
     * POST /user/collect 提交实名认证
     */
    public function identifies()
    {
        $rules = [
            'name'  => 'required|string',
            'id_number' => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        // 获取用户
        if (!$user = $this->request->user()) {
            return $this->error(self::NOT_FOUND);
        }

        $name = $this->request->input('name');              // 姓名
        $id_number = $this->request->input('id_number');    // 身份证号

        if ($user->identifies($name, $id_number)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST, User::errorMsg());
    }

    /**
     * POST /user/feedback 提交意见反馈
     */
    public function postFeedback()
    {
        $rules = [
            'details'  => 'required|string',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }

        // 获取用户
        if (!$user = $this->request->user()) {
            return $this->error(self::NOT_FOUND);
        }

        $details = $this->request->input('details'); // 内容

        if ($user->postFeedback($details)) {
            return $this->body();
        }

        return $this->error(self::BAD_REQUEST);
    }

}