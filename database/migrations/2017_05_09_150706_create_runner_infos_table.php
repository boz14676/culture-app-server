<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRunnerInfosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('runner_infos', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('race_id')->nullable()->comment('赛事表ID');
			$table->integer('user_id')->nullable()->comment('用户表ID');
			$table->char('runner_no', 10)->nullable()->comment('选手参赛号码');
			$table->integer('race_item_id')->nullable()->comment('赛事项目表ID');
			$table->integer('race_group_id')->nullable()->comment('赛事组别ID');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('runner_infos');
	}

}
