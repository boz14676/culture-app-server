<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\v2\ResultEvent;
use App\Models\v2\RunnerInfo;
use App\Models\v2\Result;
use App\Models\v2\Photo;
use App\Models\v2\User;

class WsPushController extends Controller
{
    /**
     * POST ws.result-events.insert
     */
    public function insertEvents(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
            'events' => 'required|array',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $events = $request->input('events');
        $race_id = $request->input('race_id');
        $result_events = [];
        
        collect($events)->map(function ($event) use ($race_id, &$result_events) {
            $result_events[] = [
                'race_id' => $race_id, // 比赛ID
                'runner_no' => $event['runner_no'], // 参赛号
                'position' => $event['position'], // 站点
                'result' => $event['result'], // 站点成绩
            ];
        });
        
        $result_events && ResultEvent::insertOnDuplicateKey($result_events, ['result']);
        
        return $this->body();
    }
    
    /**
     * POST ws.photo.uploads
     */
    public function uploadsWs(Request $request)
    {
        $rules = [
            'photo' => 'required|image',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
        
        if (Photo::uploadsWs($request->file('photo'))) {
            return $this->body();
        }
        
        return $this->error(self::UNKNOWN_ERROR);
    }
    
    /**
     * POST ws.result.insert
     */
    public function insertResults(Request $request)
    {
        $rules = [
            'race_id' => 'required|integer',
            'results' => 'required|array',
        ];
        if ($error = $this->validateInput($rules)) {
            return $error;
        }
    
        $results = $request->input('results');
        $race_id = $request->input('race_id');
        
        $results_attributes = [];
        
        collect($results)->map(function ($result) use ($race_id, &$results_attributes) {
            $user_id = 0;
            // 根据参赛号码获取的用户
            if ($user = User::getByRunnerNo($race_id, $result['runner_no'])) {
                $user_id  = $user->id;
                // TODO: 推送成绩消息给用户
            }
            
            $results_attributes[] = [
                'race_id' => $race_id,                         // 比赛ID
                'user_id' => $user_id,                         // 用户ID，没有则为1
                'runner_no' => $result['runner_no'],           // 选手参赛号码
                'result_time' => $result['result_time'],       // 总成绩
                'result_rank' => $result['result_rank'],       // 总排名
                'group_rank' => $result['group_rank'],         // 小组排名
            ];
        });
        
        $results_attributes && Result::insertOnDuplicateKey($results_attributes, ['result_time']);
        
        return $this->body();
    }

}
