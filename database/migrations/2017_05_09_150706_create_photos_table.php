<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePhotosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/*Schema::create('photos', function(Blueprint $table)
		{
			$table->increments('id');
			$table->enum('isfrom', ['1', '2'])->default('1')->comment('来源（1、维赛推入 2、用户自行上传）');
			$table->integer('race_id')->unsigned()->index()->comment('赛事表ID');
			$table->string('filename')->unique('filename')->comment('照片名称');
			$table->integer('position')->unsigned()->comment('站点');
			$table->integer('result')->comment('站点成绩（秒）');
			$table->timestamps();
		});*/
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		/*Schema::drop('photos');*/
	}

}
