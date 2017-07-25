<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/*Schema::create('results', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_id')->index()->comment('比赛ID');
			$table->integer('user_id')->index()->comment('用户表ID');
			$table->string('runner_no', 45)->nullable()->comment('选手参赛号码');
			$table->smallInteger('result_rank')->comment('总排名');
			$table->smallInteger('group_rank')->comment('小组排名');
			$table->integer('result_time')->comment('总成绩（Unix时间戳）');
			$table->timestamps();
            $table->unique(['race_id','runner_no']);
		});*/
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		/*Schema::drop('results');*/
	}

}
