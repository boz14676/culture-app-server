<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVideosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('videos', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('race_id')->unsigned()->index()->comment('赛事表ID');
			$table->integer('user_id')->unsigned()->index()->comment('用户表ID');
			$table->string('file_url', 150)->nullable()->comment('视频文件地址');
            $table->string('task_id', 50)->nullable()->comment('任务ID');
            $table->enum('task_status', ['0', '1', '2', '4'])->default('0')->comment('任务状态（0、新增任务 1、进行中的任务 2、任务失败 4、任务完成）');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('videos');
	}

}
