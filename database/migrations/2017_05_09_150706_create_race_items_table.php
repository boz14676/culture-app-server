<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRaceItemsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('race_items', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_id')->nullable()->index()->comment('赛事ID');
			$table->string('name', 45)->nullable()->comment('项目名称');
			$table->integer('numbers')->nullable()->comment('项目人数');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('race_items');
	}

}
