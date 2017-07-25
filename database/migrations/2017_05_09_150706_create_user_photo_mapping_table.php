<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserPhotoMappingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_photo_mapping', function(Blueprint $table)
		{
			$table->integer('race_id')->unsigned()->index()->comment('赛事表ID');
			$table->integer('user_id')->unsigned()->index()->comment('用户表ID');
			$table->integer('photo_id')->unsigned()->index()->comment('照片表ID');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_photo_mapping');
	}

}
