<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRaceMappingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_race_mapping', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned()->index()->comment('用户表ID');
			$table->integer('race_id')->unsigned()->index()->comment('赛事表ID');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_race_mapping');
	}

}
