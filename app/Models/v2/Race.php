<?php

namespace App\Models\v2;

use App\Helper\Token;
use App\Models\BaseModel;

class Race extends BaseModel
{
    
    /**
     * 道路类别
     */
    const ROUTE_ROAD = 1; // 公路
    const ROUTE_PLAYGROUND = 2; // 五四操场
    
    /**
     * 比赛类别
     */
    const CATEGORY_WEISAI = 1; // 维赛计时赛事
    const CATEGORY_NORMAL = 2; // 普通赛事
    
    /**
     * 比赛状态
     */
    const STATUS_UNACTION = 1; // 未开始
    const STATUS_APPLYING = 2; // 报名中
    const STATUS_PLAYING = 3; // 正在进行
    const STATUS_FINISHED = 4; // 已完赛
    
    protected $table = 'races';
    
    protected $visible = ['id','race_category_id','status','name','logo','urun_logo','sponsor','banner','location','count','activity_time_start','activity_time_end','apply_time_start','apply_time_end','route_type','contact_phone','notice'];
    protected $appends = ['race_items', 'race_groups', 'has_joined', 'result_with_race', 'user_race_item_id', 'user_race_group_id'];

    protected static function boot()
    {
        parent::boot();
    }

    // 是否是维赛赛事
    public function isWs() {
        return $this->category->id == Race::CATEGORY_WEISAI;
    }
    
    // 赛事类别对象
    public function category()
    {
        return $this->belongsTo(RaceCategories::class, 'race_category_id');
    }
    
    // 当前用户赛事的成绩
    public function ResultWithUser()
    {
        return $this->hasMany(Result::class)->where('user_id', User::getId());
    }
    
    // 获取当前用户参加的指定赛事的成绩
    public function getResultWithRaceAttribute() {
        // 如果是维赛赛事 已结束的赛事
        if ($this->isWs() && $this->hasDone()) {
            if ($result = $this->ResultWithUser()->first()) {
                return $result->result_time;
            }
            return false;
        }
        
        return false;
    }
    
    // 获取比赛的logo
    public function getLogoAttribute() {
        return url('/file/assets/' . $this->attributes['logo']);
    }
    
    // 获取比赛的urun_logo
    public function getUrunLogoAttribute() {
        return url('/file/assets/' . $this->attributes['urun_logo']);
    }
    
    // 赛事项目对象
    public function raceItem()
    {
        return $this->hasMany(RaceItem::class);
    }

    // 获取赛事项目
    public function getRaceItemsAttribute()
    {
        return $this->raceItem;
    }

    // 赛事组别对象
    public function raceGroup()
    {
        return $this->hasMany(RaceGroup::class);
    }

    // 赛事组别对象
    public function getRaceGroupsAttribute()
    {
        return $this->raceGroup;
    }
    
    public function getBannerAttribute()
    {
        $banners = collect(explode(',', $this->attributes['banner']));
        $banners = $banners->map(function($banner) {
            return url('/file/assets/' . $banner);
        });
        return $banners;
        
    }
    
    public function getStatusAttribute()
    {
        // 获取[设置]属性
        $this->status();
        return $this->attributes['status'];
    }

    // 属于当前登录用户的 用户赛事关联表 对象 *$this->user_id needed
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_race_mapping')
            ->wherePivot('user_id', User::getId());
    }

    // 是否被当前用户加入
    public function hasJoined()
    {
        return !$this->user->isEmpty();
    }

    // 获取该赛事是否被当前用户加入
    public function getHasJoinedAttribute()
    {
        if (!$this->hasJoined()) {
            return false;
        }
        return true;
    }
    
    // 是否完赛
    public function hasDone()
    {
        return $this->status == self::STATUS_FINISHED ? 1 : 0;
    }
    
    // 赛事状态 TODO: repository -> status
    protected function status()
    {
        // 已完赛
        if ($this->attributes['status'] == self::STATUS_FINISHED) {
            return ;
        }
        
        // 报名中状态
        $applying = 0;
        if ($this->attributes['apply_time_start'] && $this->attributes['apply_time_end']) {
            $start_time = strtotime($this->attributes['apply_time_start']); $end_time = strtotime($this->attributes['apply_time_end']);
            // 如果当前时间在报名中的时间区间内
            if (time() > $start_time && time() < $end_time) {
                $applying = 1; // 设置为已报名 标识
                $this->attributes['status'] = self::STATUS_APPLYING;
                
                return ;
            }
        }
        
        // 除了报名中的其他几种情况【未开始、正在进行、已完赛】
        if ($applying == 0) {
            $start_time = strtotime($this->attributes['activity_time_start']); $end_time = strtotime($this->attributes['activity_time_end']);
            // 如果当前时间在比赛时间开始前
            if (time() < $start_time) {
                $this->attributes['status'] = self::STATUS_UNACTION;
            }
            // 如果当前时间在比赛时间区间内
            elseif (time() > $start_time && time() < $end_time) {
                $this->attributes['status'] = self::STATUS_PLAYING;
            }
            // 比赛结束
            else {
                if ($this->attributes['status'] != self::STATUS_FINISHED) {
                    $this->attributes['status'] = self::STATUS_FINISHED;
                    $this->save(); // 储存比赛状态
                }
            }
            
            return ;
        }
    }
    
    // 赛事详情 TODO: repository -> get
    public static function get($id)
    {
        $race = self::find($id);
        // 如果该赛事已被当前用户参加
        if($race->hasJoined()) {
            $race->user[0]->race_id = $race->id; // 写入用户对象的赛事ID属性
            $runnerInfoWithRace = $race->user[0]->runnerInfoWithRace;
            // 如果有用户的参赛信息
            if($runnerInfoWithRace) {
                // 赛事项目
                $race->makeVisible(['race_items', 'race_groups', 'has_joined', 'user_race_item_id', 'user_race_group_id']);

                return $race;
            }
        }
        $race->makeVisible(['race_items', 'race_groups', 'has_joined']);
        
        return $race;
    }
    
    // 获取当前用户参加赛事的项目
    public function getUserRaceItemIdAttribute()
    {
        return $this->user[0]->runnerInfoWithRace->race_item_id;
    }
    
    // 获取当前用户参加赛事的组别
    public function getUserRaceGroupIdAttribute()
    {
        return $this->user[0]->runnerInfoWithRace->race_group_id;
    }
    
    // 获取比赛列表
    public static function lists($per_page)
    {
        $races = self::orderBy('sort', 'asc')
            ->orderBy('activity_time_start', 'desc')
            ->select('id','race_category_id','status','name','banner','activity_time_start','activity_time_end','apply_time_start','apply_time_end')
            ->paginate($per_page);
        
        return $races;
    }
    
}
