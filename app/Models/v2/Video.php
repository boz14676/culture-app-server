<?php

namespace App\Models\v2;

use App\Helper\Token;
use Illuminate\Database\Eloquent\Builder;
use App\Models\BaseModel;
use App\Services\Proxy\Video as VideoProxy;
use App\Services\Wechat\Wechat;
use Log;
use Cache;

class Video extends BaseModel
{
    const TASK_NEW = 0; // 新增任务
    const TASK_DOING = 1; // 进行中的任务
    const TASK_FAILED = 2; // 任务失败
    const TASK_FINISHED = 4; // 任务完成
    
    protected $table = 'videos';
    
    protected $fillable = ['race_id', 'user_id', 'file_url', 'task_id', 'task_status'];
    protected $visible = ['id', 'title', 'file_url', 'updated_at'];
    protected $appends = ['title'];
    
    /**
     * 数据模型的启动方法
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        
        static::addGlobalScope('status', function(Builder $builder) {
            $builder->where('task_status', self::TASK_FINISHED);
        });
    }
    
    /**
     * 生成视频
     */
    public static function createVideo($race_id, array $photos = [], $form_id)
    {
        if (!$user = User::using($race_id)) {
            self::errorMsg(trans('message.user.cannot_found'));
            return false;
        }
        
        // 如果用户的赛事 和 用户的参赛信息不为空
        if (!$user->raceWithUser->isEmpty()) {
            // 组装 用户的赛事相关信息
            $create_video_attributes['metas'] = [
                'event_name' => $user->raceWithUser[0]->name,                                   // 赛事名称
                'runner_id' => $user->runner_no,                                                // 用户的参赛号
                'runner_name' => $user->name,                                                   // 用户的姓名
                'runner_group' => (function($user) {                                            // 用户的赛事成绩
                    // 如果用户有选手的参赛信息
                    if($user->runnerInfoWithRace) {
                        $user->runnerInfoWithRace->raceItem->name . ' / ' . $user->runnerInfoWithRace->raceGroup->name;
                    }
    
                    return '';
                })($user),                                                                      // 用户的参赛项目&&组别名称
                'runner_score' => (function($user) {                                            // 用户的赛事成绩
                    // 如果是维赛的赛事 并且 用户有成绩
                    if($user->raceWithUser[0]->isWs() && $user->resultWithRace) {
                        return $user->resultWithRace->result_time;
                    }
                    
                    return '';
                })($user),
                'runner_rank' => (function($user) {                                              // 用户的赛事的分组排名
                    // 如果是维赛的赛事 并且 用户有成绩
                    if($user->raceWithUser[0]->isWs() && $user->resultWithRace) {
                        return $user->resultWithRace->group_rank;
                    }
    
                    return '';
                })($user),
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ];
    
            // 组装 照片地址
            if (!$photos = Photo::whereIn('id', $photos)->get()) {
                self::errorMsg(trans('message.photo.cannot_found'));

                return false;
            }
            $resources = [];
            $photos->map(function ($photo) use(&$resources) {
                $resources[] = [
                    'type' => 'img',
                    'url' => $photo->full_url
                ];
            });
            $create_video_attributes['resources'] = $resources;
            $create_video_attributes['callback_url'] = route('video.receive');
            // 如果请求视频接口成功
            if ($task_id = VideoProxy::push($create_video_attributes)) {
                self::create(['race_id' => $race_id, 'user_id' => $user->id, 'task_id' => $task_id]);
                
                // 写入用户生成视频的form_id
                $user->cv_form_id = $form_id;
                return true;
            } else {
                self::errorMsg(trans('message.video.push_error'));
                return false;
            }
        }
        
        // 未找到该赛事
        self::errorMsg(trans('message.race.not_found'));
        return false;
    }
    
    /**
     * 删除视频
     */
    public static function deleteVideos($videos)
    {
        $user_id = User::getId();
        return self::withoutGlobalScope('status')->where('user_id', $user_id)->whereIn('id', $videos)->delete();
    }
    
    /**
     * 接收视频
     */
    public static function receiveVideo(array $attributes = [])
    {
        $task_id = $attributes['task_id'];
        $task_status = $attributes['task_status'];
        $export_video = $attributes['export_video'];
        
        // 如果任务状态为已完成
        if ($task_status == self::TASK_FINISHED) {
            // 更新视频对象
            if ($video = self::withoutGlobalScope('status')->where('task_id', $task_id)->first()) {
                $video->task_status = self::TASK_FINISHED;
                $video->file_url = $export_video;
                $video->save();
    
                Log::info('视频生成成功 [task_id: '.$task_id.']');
                // 通知微信用户 视频已生成成功
                self::pushToUser($video);

                return true;
            }
            
            Log::info('没有根据task_id找到视频 [task_id: '.$task_id.']');
            self::errorMsg('没有根据task_id找到视频');
            
            return false;
        }
        
        Log::info('callback视频任务状态失败 [task_id:'.$task_id.' task_status: '.$task_status.']');
        self::errorMsg('视频任务状态失败');
        
        return false;
    }
    
    /**
     * 视频列表
     */
    public static function listsWithUser($per_page)
    {
        $user = User::using();
        if ($videos = $user->videos()->with('race')->paginate($per_page)) {
            return $videos;
        }
        
        return false;
    }
    
    public function getTitleAttribute() {
        if ($this->race) {
            return $this->race->name;
        }
        return '';
    }
    
    // 视频所属的赛事对象
    public function race()
    {
        return $this->belongsTo(Race::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 通知微信用户
     */
    public static function pushToUser($video)
    {
        if (!$video->user) {
            return false;
        }
        
        $wechat = new Wechat(2);
        $attributes = [
            'open_id' => $video->user->weapp_openid,
            'template_id' => 'UvGj8GR0vj1UxEKV5AFe9LW5q0IOp3g9S9mBH0jc4SU',
            'form_id' => $video->user->cv_form_id, // 获取用户生成视频的form_id
            'page' => 'pages/myVideo/index',
            'data' => [
                'keyword1' => [
                    'value' => 'U-run小程序视频已生成',
                ],
                'keyword2' => [
                    'value' => date('Y-m-d G:i:s', $video->updated_at->getTimestamp()),
                ],
            ]
        ];
        
        return $wechat->sendTemplateMsg($attributes);
    }
}
