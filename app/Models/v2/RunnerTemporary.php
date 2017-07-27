<?php

namespace App\Models\v2;

use App\Models\BaseModel;
use App\Models\v2\Result;
use Yadakhov\InsertOnDuplicateKey;
use DB;

class RunnerTemporary extends BaseModel {
    use InsertOnDuplicateKey;
    
    protected $table      = 'runner_temporary';
    
    protected $visible = ['id','race_id','runner_no','mobile','gender','constellation','name','birthday','id_number','created_at','updated_at'];
    protected $fillable = ['race_id','runner_no','mobile','gender','constellation','name','birthday','id_number'];

    /**
     * 默认使用时间戳戳功能
     *
     * @var bool
     */
    public $timestamps = true;
    
    // 根据手机号匹配若干个选手
    public static function mapToUser($user, $mobile)
    {
        $races_id = [];
        $runners_id = [];
        $runners_info = [];
        $runners = self::where('mobile', $mobile)->get();
        
        collect($runners)->map(function($runner) use ($user, &$races_id, &$runners_id, &$runners_info) {
            // 赛事id
            $races_id[] = $runner['race_id'];
    
            $runners_info[] = [
                'race_id' => $runner['race_id'],
                'user_id' => $user->id,
                'runner_no' => $runner['runner_no'],
                'race_item_id' => $runner['race_item_id'],
                'race_group_id' => $runner['race_group_id'],
            ];
            // 选手所参加的赛事为 维赛计时赛事
            if($runner['race_category_id'] == Race::CATEGORY_WEISAI) {
                // 更新成绩关系
                if ($result = Result::belongsRace($runner['race_id'])->where('runner_no', $runner['runner_no'])->first()) {
                    $result->user_id = $user->id;
                    $result->save();
                }
            }
    
            $runners_id[] = $runner['id'];
        });
        
        // 用户参加了比赛（在选手临时表中有匹配的数据）
        if ($runners_id) {
            // 添加选手的参赛信息
            if ($runners_info) {
                DB::table('runner_infos')->insert($runners_info);
            }
    
            // 组装更新用户的数据
            $user->name = $runners[0]->name;
            $user->birthday = $runners[0]->birthday;
            $user->id_number = $runners[0]->id_number;
    
            // 用户与赛事关联
            $user->races()->attach($races_id);
            
            // 删除选手临时表的数据
            self::destroy($runners_id);
        }

        // 即使没有参加比赛，也绑定手机号
        $user->mobile = $mobile;
        $user->is_bind = '1';
        $user->save();
        
        return true;
    }
    
    // 导入某个赛事的参赛选手数据
    public static function importing($runner_temporaries_attr)
    {
        $insert_attributes = [];
        collect($runner_temporaries_attr)->map(function ($runner) use (&$insert_attributes) {
            // 如果没有匹配，组装插入的参赛选手的数据
            if (!$user = User::mapForRuuner($runner['mobile'], $runner['race_id'])) {
                $insert_attributes[] = $runner;
                
                return false;
            }
            // 如果已匹配到用户 并且 此用户没有参加该赛事
            if (!UserRaceMapping::hasRaceWithUser($runner['race_id'], $user->user_id)) {
                // 建立用户与赛事的关系
                $user->races()->attach($runner['race_id']);
                
                // 有完整的参赛信息 则添加参赛信息
                if (isset($runner['runner_no']) && isset($runner['race_item_id']) && isset($runner['race_group_id'])) {
                    // 添加用户的在该赛事的参赛信息
                    $user->runnerInfoWithRace()->create([
                        'race_id' => $runner['race_id'],
                        'runner_no' => $runner['runner_no'],
                        'race_item_id' => $runner['race_item_id'],
                        'race_group_id' => $runner['race_group_id']
                    ]);
                }
            }
        });
        
        // 没有匹配到的用户，插入到参赛选手临时数据
        if ($insert_attributes) {
            self::insertOnDuplicateKey($insert_attributes);
        }
    }
}
