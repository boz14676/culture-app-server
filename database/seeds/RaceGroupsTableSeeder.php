<?php

use Illuminate\Database\Seeder;

class RaceGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清华大学赛事 -> 组别
        $race_groups[0] = $this->repositories(1);
        DB::table('race_groups')->insert($race_groups[0]);
        
        // 北大赛事-夜奔 -> 组别
        $race_groups[1] = [
            [
                'race_id' => 2,
                'name' => '男子组'
            ],
            [
                'race_id' => 2,
                'name' => '女子组'
            ],
        ];
        DB::table('race_groups')->insert($race_groups[1]);
    }
    
    public function repositories($race_id)
    {
        $race_groups = [];
        $native_race_groups = DB::table('race_'.$race_id.'_sheet')->select(DB::raw('race_group_name, COUNT(*) as numbers'))->groupBy('race_group_name')->get();
        collect($native_race_groups)->map(function ($race_group) use ($race_id, &$race_groups) {
            $race_groups[] = [
                'race_id' => $race_id,
                'name' => $race_group->race_group_name,
                'numbers' => $race_group->numbers
            ];
        });
        
        return $race_groups;
    }
}