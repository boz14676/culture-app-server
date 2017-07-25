<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRunnerTemporaryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('runner_temporary', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_id')->nullable()->comment('赛事ID');
			$table->integer('race_category_id')->nullable()->comment('赛事类别ID');
			$table->string('runner_no', 45)->nullable()->comment('选手参赛号码');
			$table->integer('race_item_id')->comment('赛事项目ID');
			$table->integer('race_group_id')->comment('赛事组别（1、男子组 2、女子组）');
			$table->bigInteger('mobile')->unsigned()->comment('用户手机号码');
			$table->enum('gender', array('0','1','2'))->nullable()->default('0')->comment('用户性别');
			$table->string('name', 30)->comment('用户真实姓名');
			$table->date('birthday')->nullable()->comment('用户的出生日期');
			$table->string('id_number', 18)->comment('用户身份证号码');
            $table->enum('id_number_type', ['1', '2'])->default('1')->comment('用户的证件类型');
            $table->unique(['race_id','mobile']);
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
		Schema::drop('runner_temporary');
	}

}
