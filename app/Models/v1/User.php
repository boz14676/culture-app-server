<?php

namespace App\Models\v1;

use App\Models\BaseModel;
use App\Helper\Token;
use App\Services\QcloudSMS\Sms;
use Carbon\Carbon;
use Laravel\Lumen\Auth\Authorizable;
use Hash;
use Auth;
use DB;
use App\Services\Photo;


class User extends BaseModel
{
    use Authorizable;

    protected $table = 'users';

    protected $guarded = ['password'];

    protected $fillable = [
        'vendor',
        'wechat_openid',
        'wechat_unionid',
        'qq_openid',
        'nickname',
        'gender',
        'avatar',
    ];

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
        'nickname',                 // 昵称
        'avatar',                   // 头像
        'is_bind',                  // 是否绑定
        'identification_status',    // 是否认证
        'numbers_count',            // 数据统计
        'signature',                // 个性签名
        'integral_quantities',      // 积分
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

        // 绑定手机号后的用户积分挂载操作
        $user->addIntegral('binded');

        return $user;
    }

    /**
     * 授权登录
     * @param array $attributes
     */
    public static function socialLogin(array $attributes)
    {
        $vendor = $attributes['vendor'];
        switch ($vendor) {
            case Social::VENDOR_WEIXIN:
                $code = $attributes['code'];
                if (!$user_attributes = Social::wechatAuth($code)) {
                    return false;
                }

                break;
            case Social::VENDOR_QQ:
                $access_token = $attributes['access_token'];
                $openid = $attributes['openid'];
                if (!$user_attributes = Social::qqAuth($access_token, $openid)) {
                    return false;
                }
                break;
        }

        if (!$user = self::firstOrCreate(array_merge(
            $user_attributes,
            ['vendor' => $vendor]
        ))) {
            return false;
        }

        return $user->makeVisible(['token']);

    }

    /**
     * 登录
     * @param int $mobile       # 手机号
     * @param string $password  # 密码
     * @param string $code      # 验证码
     * @return mixed
     */
    public static function login($mobile, $password, $code)
    {
        // 查找用户
        if (!$user = self::where('mobile', $mobile)->first()) {
            self::errorMsg(trans('message.user.mobile_not_found'));

            return false;
        }

        // 验证密码
        if ($password) {
            if (!Hash::check($password, $user->password)) {
                self::errorMsg(trans('message.user.password_wrong'));

                return false;
            }
        }

        // 验证验证码
        elseif ($code) {
            // 验证验证码
            if (!Sms::verifySmsCode($mobile, $code)) {
                self::errorMsg(trans('message.user.verify_code_error'));

                return false;
            }
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

    /**
     * 发送验证码
     * @param $mobile
     * @return bool
     */
    public static function sendVerifyCode($mobile)
    {
        return Sms::requestSmsCode($mobile);
    }

    /**
     * 评论对象
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userComment()
    {
        return $this->hasMany('App\Models\v1\UserComment');
    }

    /**
     * 收藏对象
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userCollect()
    {
        return $this->hasMany('App\Models\v1\UserCollect');
    }

    /**
     * 用户评论的数据仓库
     * @param int $per_page
     * @param array $q
     * @param array $s
     * @return mixed
     */
    public function commentRepositories($per_page = 10, $q = [], $s = [])
    {
        self::$aliveSelf = $this->userComment();
        $user_comments = self::repositories();
        $user_comments->map(function ($user_comment) {
            $user_comment->makeHidden(['original_user']);
            $user_comment->makeVisible(['original_commentable']);
        });

        return $user_comments;
    }

    /**
     * 用户收藏的数据仓库
     * @param int $per_page
     * @param array $q
     * @param array $s
     * @return mixed
     */
    public function collectRepositories($per_page = 10, $q = [], $s = [])
    {
        self::$aliveSelf = $this->userCollect();
        $user_collects = self::repositories();
        $user_collects->map(function ($user_collect) {
            $user_collect->makeVisible(['original_collectable']);
        });

        return $user_collects;
    }

    /**
     * 订单对象
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany('App\Models\v1\Order');
    }

    /**
     * 订单商品对象
     * @param int $goods_id
     * @param int $isvalid
     * @return mixed
     */
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
     * 实名认证对象
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function identification()
    {
        return $this->hasOne('App\Models\v1\UserIdentification');
    }

    /**
     * 用户积分记录对象
     */
    public function userIntegral()
    {
        return $this->hasMany('App\Models\v1\UserIntegral');
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

    // 获取[头像] 属性
    public function getAvatarAttribute()
    {
        return format_assets($this->attributes['avatar'], 'file/photos/user');
    }

    // 获取[token] 属性
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

    /**
     * 提交实名认证
     * @param $name
     * @param $id_number
     * @return bool|\Illuminate\Database\Eloquent\Model
     */
    public function identifies($name, $id_number)
    {
        if ($this->identification) {
            self::errorMsg(trans('message.user.opt_repetition'));
            return false;
        }

        // 添加一条实名认证的审核记录
        $this->identification()->create([
            'name' => $name,
            'id_number' => $id_number,
        ]);

        // 更新用户的实名认证审核的状态
        $this->identification_status = UserIdentification::STATUS_WAIT;
        $this->save();

        return true;
    }

    /**
     * POST 意见反馈
     * @param string $details
     * @return bool
     */
    public function postFeedback($details='')
    {
        DB::table('feedbacks')->insert(['details' => $details, 'user_id' => $this->id, 'created_at' => Carbon::now()]);
        return true;
    }

    /**
     * 更改用户资料
     */
    public function putProfile()
    {
        if (isset($this->attributes['photo'])) {
            // 更改头像
            if (!$filename = Photo::uploads($this->photo)) {
                unset($this->attributes['photo']);

                self::errorMsg(Photo::errorMsg());
                return false;
            }

            $this->avatar = $filename;
        }
        unset($this->attributes['photo']);

        return $this->save();
    }

    /**
     * 增加积分
     * @param $code
     * @return bool
     */
    public function addIntegral($code)
    {
        $user_integral = new UserIntegral;

        // 积分任务对象
        $integral_task = IntegralTask::where('code', $code)->first();
        if ($integral_task) {
            $user_integral->integral_task_id = $integral_task->id;      // 积分任务ID
            $user_integral->quantities = $integral_task->quantities;    // 积分量

            // 首次添加 类型
            if ($integral_task->type === IntegralTask::TYPE_FIRSTTIME) {
                $has_added =
                    $this
                    ->userIntegral()
                    ->where('integral_task_id', $integral_task->id)
                    ->count();
                // 只限添加一次
                if ($has_added) {
                    return false;
                }

                // 写入积分记录
                $this->userIntegral()->save($user_integral);

                // 增加用户对象积分
                $this->increment('integral_quantities', $integral_task->quantities);
                $this->save();

                return true;
            }

            // 每日添加 类型
            elseif ($integral_task->type === IntegralTask::TYPE_EVERTYDAY) {
                $has_added_today =
                    $this
                        ->userIntegral()
                        ->where('integral_task_id', $integral_task->id)
                        ->whereBetween('created_at', [
                            Carbon::today(),
                            Carbon::createFromTimestamp(Carbon::tomorrow()->timestamp-1),
                        ])
                        ->count();

                // 只限每天添加一次
                if ($has_added_today) {
                    return false;
                }

                // 写入积分记录
                $this->userIntegral()->save($user_integral);

                // 增加用户对象积分
                $this->increment('integral_quantities', $integral_task->quantities);
                $this->save();

                return true;
            }
        }
    }
}