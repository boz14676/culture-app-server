<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRaceGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('race_groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_id')->nullable()->comment('赛事表ID');
			$table->string('name', 45)->nullable()->comment('组别名称');
			$table->integer('numbers')->nullable()->comment('组别人数');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('race_groups');
	}

}
