<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use App\Helper\Token;
use App\Services\QcloudSMS\Sms;
use Laravel\Lumen\Auth\Authorizable;
use Hash;
use Auth;

class User extends BaseModel
{
    use Authorizable;

    protected $table = 'users';

    protected $guarded = ['password'];

    protected $appends = [
        'token',                    // TOKEN

        /***************************** 数量统计 *****************************/
        'numbers_count',            // 数量总统计
        'order_numbers',            // 订单总数量
        'order_wait_pay_numbers',   // 订单待支付数量
        'order_paid_numbers',       // 订单已预订数量
        'order_refunded_numbers',   // 订单已退款数量
        'enshrine_numbers',         // 收藏数量
        'comment_numbers'           // 评论数量
    ];

    protected $visible = [
        'nickname',             // 昵称
        'avatar',               // 头像
        'is_bind',              // 是否绑定
        'is_identification',    // 是否认证
        'numbers_count',        // 数据统计
    ];

    protected $with = [];

    protected $casts = [
        'is_bind' => 'integer',
        'is_identification' => 'integer'
    ];

    /**
     * 注册
     * @param int $mobile       # 手机号
     * @param string $password  # 密码
     * @param string $code      # 验证码
     * @return User
     */
    public static function register($mobile, $password=null, $code)
    {
        // 验证验证码
        if (!Sms::verifySmsCode($mobile, $code)) {
            self::errorMsg(trans('message.user.verify_code_error'));

            return false;
        }

        // 创建用户对象
        $user = new User;
        $user->mobile = $mobile;                                       // 手机号
        $user->password = $password ? Hash::make($password) : null;    // 密码
        $user->avatar = 'avator-default.png';                          // 默认头像
        $user->nickname = str_random(7);                        // 昵称
        $user->is_bind = 1;                                            // 注册默认绑定
        $user->save();

        return $user;
    }

    /**
     * 登录
     * @param int $mobile       # 手机号
     * @param string $password  # 密码
     * @return mixed
     */
    public static function login($mobile, $password)
    {
        // 查找用户
        if (!$user = self::where('mobile', $mobile)->first()) {
            self::errorMsg(trans('message.user.mobile_not_found'));

            return false;
        }

        // 验证密码
        if (!Hash::check($password, $user->password)) {
            self::errorMsg(trans('message.user.password_wrong'));

            return false;
        }

        return $user;
    }

    /**
     * 验证原始密码
     * @param string $original_password  # 密码
     * @return boolean
     */
    public static function chekcOriginalPassword($original_password)
    {
        // 查找用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        // 验证密码
        if (!Hash::check($original_password, $user->password)) {
            self::errorMsg(trans('message.user.original_password_wrong'));

            return false;
        }

        return $user;
    }

    /**
     * 修改密码
     * @param string $password
     * @return boolean
     */
    public static function updatePassword($password)
    {
        // 查找用户
        if (!$user = Auth::user()) {
            self::errorMsg(trans('message.user.user_not_found'));

            return false;
        }

        // 检查是否和老密码一致
        if (Hash::check($password, $user->password)) {
            self::errorMsg(trans('message.user.same_original_password'));

            return false;
        }

        // 更新用户密码
        $user->password = Hash::make($password);
        return $user->save();
    }

    // 更新用户
    public function updates($attribute='', $ext=null)
    {
        // 更改头像
        if ($attribute === 'avatar') {
            if (!$filename = Photo::uploads($ext)) {
                self::errorMsg(Photo::errorMsg());

                return false;
            }
            $this->avatar = $filename;
            $this->save();

            return $this;
        }
    }

    // 发送验证码
    public static function sendVerifyCode($mobile)
    {
        return Sms::requestSmsCode($mobile);
    }

    // 订单对象
    public function orders()
    {
        return $this->hasMany('App\Models\v1\Order');
    }

    // 订单商品对象
    public function order($goods_id=0, $isvalid=0)
    {
        return $this->hasMany('App\Models\v1\Order')
            ->when($goods_id, function($query) use($goods_id) {
                return $query->where('goods_id', $goods_id);
            })
            ->when($isvalid, function ($query) {
                return $query->where('status', '<>', Order::STATUS_CANCELED);
            });
    }

    /**
     * 获取当前用户 对某个商品的下单总量
     * @param int $goods_id 团购商品ID
     * @param int $isvalid 是否为有效订单
     * @return int 商品数量
     */
    public function getOrderGoodsNumbers($goods_id=0, $isvalid=0)
    {
        return $order_goods = $this->order($goods_id, $isvalid)->sum('goods_numbers') ? : 0;
    }

    // 获取头像属性
    public function getAvatarAttribute()
    {
        return format_photo('file/photos/user/' . $this->attributes['avatar']);
    }

    // 获取token属性
    public function getTokenAttribute()
    {
        return Token::encode(['uid' => $this->attributes['id']]);
    }

    // 获取[数量总统计] 属性
    public function getNumbersCountAttribute()
    {
        return [
            'order_numbers' => $this->order_numbers,
            'order_wait_pay_numbers' => $this->order_wait_pay_numbers,
            'order_paid_numbers' => $this->order_paid_numbers,
            'order_refunded_numbers' => $this->order_refunded_numbers,
            'enshrine_numbers' => $this->enshrine_numbers,
            'comment_numbers' => $this->comment_numbers,
        ];
    }

    // 获取[订单总数量] 属性
    public function getOrderNumbersAttribute()
    {
        return $this->orders()->count();
    }

    // 获取[订单待支付数量] 属性
    public function getOrderWaitPayNumbersAttribute()
    {
        return $this->orders()
            ->where('status', Order::STATUS_WAIT_PAY)
            ->count();
    }

    // 获取[订单已预订数量] 属性
    public function getOrderPaidNumbersAttribute()
    {
        return $this->orders()
            ->where('status', Order::STATUS_PAID)
            ->count();
    }

    // 获取[订单已退款数量] 属性
    public function getOrderRefundedNumbersAttribute()
    {
        return 0;
    }

    // 获取[收藏数量] 属性
    public function getEnshrineNumbersAttribute()
    {
        return 0;
    }

    // 获取[评论数量] 属性
    public function getCommentNumbersAttribute()
    {
        return 0;
    }
}