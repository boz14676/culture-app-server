<?php

namespace App\Models\v2;

use App\Models\BaseModel;
use App\Helper\Token;
use \E421083458\Wxxcx\Wxxcx;
use App\Services\QcloudSMS\Sms;
use Cache;
use Carbon\Carbon;

class User extends BaseModel
{
    
    const VENDOR_WEIXIN = 1;
    const VENDOR_WEIBO = 2;
    const VENDOR_QQ = 3;
    const VENDOR_TAOBAO = 4;
    const VENDOR_WEAPP = 5;    //微信小程序
    
    const GENDER_SECRET = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    
    protected $table = 'users';
    
    protected $appends = ['runner_no', 'joined_race_nums', 'cv_form_id'];
    protected $visible = ['id', 'wechat_openid', 'weapp_openid', 'unionid', 'nickname', 'avatar', 'runner_no', 'is_bind', 'joined_race_nums'];
    protected $fillable = ['wechat_openid', 'weapp_openid', 'unionid', 'is_bind', 'nickname', 'gender', 'avatar'];
    
    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    public $timestamps = true;
    
    // 设置用户生成视频的form_id
    public function setCvFormIdAttribute($value)
    {
        // 将用户生成视频的form_id写入缓存
        $this->attributes['cv_form_id'] = $value;
        Cache::put('cv_form_id-'.$this->id, $value, Carbon::now()->addDay(2));
    }
    
    // 获取用户生成视频的form_id
    public function getCvFormIdAttribute()
    {
        // 将用户生成视频的form_id写入缓存
        return Cache::pull('cv_form_id-'.$this->id);
    }
    
    // 用户在当前比赛的图片 对象
    public function photosWithRace()
    {
        return $this
            ->belongsToMany(Photo::class, 'user_photo_mapping')
            ->wherePivot('race_id', $this->race_id);
    }
    
    // 获取比赛列表
    public static function raceLists($per_page)
    {
        $user = self::using();
        $user_races = $user->races()->simplePaginate($per_page);
        $user_races->map(function ($user_race) {
            $user_race->setVisible(['id', 'logo', 'race_category_id', 'status', 'name', 'result_with_race']);
        });
        
        
        return $user_races;
    }
    
    public static function getId()
    {
        return Token::authorization();
    }
    
    // 用户是否匹配维赛的照片
    public function hasWsPhotos()
    {
        return $this
            ->photosWithRace()
            ->where('isfrom', Photo::ISFROM_WS)
            ->count() ? 1 : 0;
    }
    
    // 用户的赛事 对象
    public function races()
    {
        return $this
            ->belongsToMany(Race::class, 'user_race_mapping')
            ->orderBy('activity_time_start', 'desc');
    }
    
    // 用户的视频 对象
    public function videos()
    {
        return $this
            ->hasMany(Video::class)
            ->orderBy('created_at', 'desc');
    }
    
    // 用户的赛事 对象
    public function raceWithUser()
    {
        return $this
            ->belongsToMany(Race::class, 'user_race_mapping')
            ->wherePivot('race_id', $this->race_id);
    }
    
    // 用户的赛事 对象 *$this->race_id needed
    public function videoWithRace()
    {
        return $this
            ->hasMany(Video::class)
            ->where('race_id', $this->race_id);
    }
    
    // 用户的赛事信息 对象
    public function runnerInfoWithRace()
    {
        return $this
            ->hasOne(RunnerInfo::class)
            ->BelongsRace($this->race_id);
    }
    
    // 用户参加赛事的数量
    public function getJoinedRaceNumsAttribute()
    {
        return $this->races()->count();
    }
    
    // 用户的成绩对象 *$this->race_id needed
    public function resultWithRace()
    {
        return $this
            ->hasOne(Result::class)
            ->BelongsRace($this->race_id);
    }
    
    // 用户在当前比赛的参赛号
    public function getRunnerNoAttribute()
    {
        if ($this->runnerInfoWithRace) {
            return $this->runnerInfoWithRace->runner_no;
        }
        
        return false;
    }
    
    public function getResultEvents()
    {
        if (
        $result_events = ResultEvent::where('race_id', $this->attributes['race_id'])
            ->where('runner_no', $this->runner_no)
            ->get()
        ) {
            return $result_events;
        }
        
        return false;
    }
    
    /**
     * 微信小程序授权
     * @param array $attributes
     * @return array|mixed
     */
    public static function weappAuth(array $attributes)
    {
        extract($attributes);
        
        // 验证session_key
        $weapp_config = [
            'code2session_url' => 'https://api.weixin.qq.com/sns/jscode2session',
            'appid' => env('weapp_app_id'),
            'secret' => env('weapp_app_secret')
        ];
        $weapp = new Wxxcx($weapp_config);
        $res = $weapp->getLoginInfo($code);
        if ($res['error_code'] == 1) {
            return self::formatError(self::UNAUTHORIZED, $res['error_desc']);
        }
        $signature2 = sha1($rawData . $res['data']['session_key']);
        if ($signature != $signature2) {
            return self::formatError(self::UNAUTHORIZED, trans('message.user.auth_error'));
        }
        
        // 获取完整用户信息
        $user_info = $weapp->getUserInfo($encryptedData, $iv);
        if ($user_info['error_code'] == 1) {
            return self::formatError(slef::UNAUTHORIZED, $user_info['error_desc']);
        }
        $user_info = $user_info['data'];
        
        // 建立用户并判断是否已经绑定手机号
        $user = self::createUser($user_info, 1);
        
        $auth_attributes = [
            'is_bind' => $user['is_bind'],
            'token' => Token::encode(['uid' => $user->id])
        ];
        
        return $auth_attributes;
    }
    
    // 建立用户并判断是否已经绑定手机号
    public static function createUser($user_info, $isfrom)
    {
        // 如果没有用户，创建一个新用户
        if (!$user = User::where('unionid', $user_info['unionid'])->first()) {
            $user = User::create($user_info);
            $user->is_bind = 2;
        }
        
        // 如果来自小程序的用户
        if ($isfrom == 1) {
            // 如果用户没有绑定过小程序
            if (!$user->weapp_openid) {
                $user->weapp_openid = $user_info['weapp_openid'];
                $user->save();
            }
        }
        // 来自微信服务号的用户
        else {
            // 如果用户没有绑定过服务号
            if (!$user->wechat_openid) {
                $user->wechat_openid = $user_info['wechat_openid'];
                $user->save();
            }
        }
        
        return $user;
    }
    
    // 发送验证码
    public static function sendVerifyCode($mobile)
    {
        return Sms::requestSmsCode($mobile);
    }
    
    // 绑定用户手机号
    public static function bind_mobile($mobile, $verify_code)
    {
        // 验证验证码
        if (!Sms::verifySmsCode($mobile, $verify_code)) {
            return 101;
        }
        
        $user = self::using();
        
        // 绑定用户参加的赛事 --> 绑定
        RunnerTemporary::mapToUser($user, $mobile);
        
        return 1;
    }
    
    
    public static function get()
    {
        $user = User::using();
        $user->setVisible(['joined_race_nums']);
        return $user;
    }
    
    // 获取当前登录的用户
    public static function using($race_id = 0)
    {
        $user_id = Token::authorization();
        
        if ($user = self::find($user_id)) {
            if ($race_id) {
                $user->race_id = $race_id;
            }
            
            return $user;
        }
        
        return false;
    }
    
    /**
     * 匹配导入的参赛选手的数据
     *
     * @param integer $mobile
     * @param integer $race_id
     * @return bool
     */
    public static function mapForRuuner($mobile, $race_id)
    {
        if ($user = self::where('mobile', $mobile)->first()) {
            $user->race_id = $race_id;
            return $user;
        }
        
        return false;
    }
    
    // 根据参赛号码获取用户
    public static function getByRunnerNo($race_id, $runner_no)
    {
        if ($user_id = RunnerInfo::getUserIdByRunnerNo($race_id, $runner_no)) {
            return $user = User::find($user_id);
        }
        
        return false;
    }
}
