<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        
        $this->call('RaceCategoriesTableSeeder');
        $this->call('RacesTableSeeder');
        $this->call('RaceItemsTableSeeder');
        $this->call('RaceGroupsTableSeeder');
        $this->call('UsersTableSeeder');
        $this->call('UserRaceMappingTableSeeder');
        $this->call('VideosTableSeeder');
        $this->call('RunnerTemporaryTableSeeder');
        
        Model::reguard();
    }
}