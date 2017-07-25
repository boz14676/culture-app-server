<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResultEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/*Schema::create('result_events', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_id')->index()->comment('比赛ID');
			$table->string('runner_no', 10)->index()->comment('用户参赛号码');
			$table->integer('position')->comment('站点');
			$table->integer('result')->comment('成绩');
			$table->timestamps();
			$table->unique(['runner_no','result']);
		});*/
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		/*Schema::drop('result_events');*/
	}

}
