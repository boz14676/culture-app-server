<?php

use Illuminate\Database\Seeder;
// use App\Models\v2\Race;

class RaceCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 赛事类别数据
        $race_categories_data = [
            ['name' => '维赛计时赛事'],
            ['name' => '普通赛事'],
        ];
        db::table('race_categories')->insert($race_categories_data);
    }
}