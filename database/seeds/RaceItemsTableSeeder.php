<?php

use Illuminate\Database\Seeder;

class RaceItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 清华大学赛事项目
        $race_items[0] = $this->repositories(1);
        DB::table('race_items')->insert($race_items[0]);
    
        // 北大赛事 u-run -> 项目
        $race_items[1] = [
            'race_id' => 2,
            'name' => '3公里',
            'numbers' => '2000'
        ];
        DB::table('race_items')->insert($race_items[1]);
    
        // 北大赛事 u-training -> 项目
        $race_items[1] = [
            'race_id' => 3,
            'name' => 'Yoga/Boomfit/DanceParty',
            'numbers' => '1000'
        ];
        DB::table('race_items')->insert($race_items[1]);
        
    }
    
    public function repositories($race_id)
    {
        $race_items = [];
        $native_race_items = DB::table('race_'.$race_id.'_sheet')->select(DB::raw('race_item_name, COUNT(*) as numbers'))->groupBy('race_item_name')->get();
        collect($native_race_items)->map(function ($race_item) use ($race_id, &$race_items) {
            $race_items[] = [
                'race_id' => $race_id,
                'name' => $race_item->race_item_name,
                'numbers' => $race_item->numbers
            ];
        });
        
        return $race_items;
    }
}