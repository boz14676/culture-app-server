<?php

use Illuminate\Database\Seeder;
use App\Models\v2\Race;
use App\Models\v2\RunnerTemporary;

class RunnerTemporaryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清华大学赛事 -> 选手临时数据
        $runners[0] = $this->repository(1);
        RunnerTemporary::importing($runners[0]);
        
        // 北大赛事 u-run -> 选手临时数据
        $runners[1] = $this->repository(2);
        RunnerTemporary::importing($runners[1]);
    
        // 北大赛事 u-training -> 选手临时数据
        $runners[2] = $this->repository(3);
        RunnerTemporary::importing($runners[2]);
    }
    
    public function repository($race_id)
    {
        switch ($race_id) {
            case 1:
                $runners = [];
                $race_sheets = DB::table('race_'.$race_id.'_sheet as race_sheet')->get();
                collect($race_sheets)->map(function ($race_sheet) use ($race_id, &$runners) {
                    $runners[] = [
                        'race_id' => $race_id,
                        'race_category_id' => Race::CATEGORY_WEISAI,
                        'runner_no' => $race_sheet->runner_no,
                        'mobile' => $race_sheet->mobile,
                        'name' => $race_sheet->name,
                        'birthday' => $race_sheet->birthday,
                        'id_number_type' => $race_sheet->id_number_type == '身份证' ? 1 : 0,
                        'id_number' => $race_sheet->id_number,
                        'race_item_id' => DB::table('race_items')->where('race_id', $race_id)->where('name', $race_sheet->race_item_name)->value('id'),
                        'race_group_id' => DB::table('race_groups')->where('race_id', $race_id)->where('name', $race_sheet->race_group_name)->value('id'),
                    ];
                });
                return $runners;
                break;
            case 2:
            case 3:
                $runners = [];
                $race_sheets_01 = DB::table('race_2_sheet_01 as race_sheet')->get();
                $race_sheets_02 = DB::table('race_2_sheet_02 as race_sheet')->get();
                $race_sheets_03 = DB::table('race_2_sheet_03 as race_sheet')->get();
                $race_sheets = array_merge($race_sheets_01, $race_sheets_02, $race_sheets_03);
                
                collect($race_sheets)->map(function ($race_sheet) use ($race_id, &$runners) {
                    $runners[] = [
                        'race_id' => $race_id,
                        'race_category_id' => Race::CATEGORY_NORMAL,
                        'mobile' => $race_sheet->mobile,
                        'name' => $race_sheet->name,
                        'birthday' => isset($race_sheet->birthday) ? $race_sheet->birthday : '',
                        'id_number_type' => isset($race_sheet->id_number_type) && $race_sheet->id_number_type == '居民身份证' ? 1 : 0,
                        'id_number' => isset($race_sheet->id_number) ? $race_sheet->id_number : '',
                    ];
                });
                return $runners;
                break;
        }
    }
}