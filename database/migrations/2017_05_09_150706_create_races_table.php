<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRacesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('races', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('race_category_id')->unsigned()->index('fk_races_races_cat1_idx')->comment('赛事类别表ID');
			$table->string('name', 100)->comment('赛事名称');
			$table->string('sponsor', 100)->comment('主办方');
			$table->string('logo', 150)->nullable()->comment('赛事logo URL');
			$table->string('urun_logo', 150)->nullable()->comment('赛事urun_logo URL');
			$table->string('banner', 150)->nullable()->comment('赛事banner URL');
			$table->string('location', 50)->comment('赛事地点');
			$table->smallInteger('count')->comment('赛事参赛人数');
			$table->dateTime('activity_time_start')->nullable()->comment('赛事开始时间');
			$table->dateTime('activity_time_end')->nullable()->comment('赛事结束时间');
			$table->dateTime('apply_time_start')->nullable()->comment('报名开始时间');
			$table->dateTime('apply_time_end')->nullable()->comment('报名结束时间');
			$table->enum('status', array('1','2','3','4'))->nullable()->default('1')->comment('赛事状态（1、未开始，2、报名中，3、正在进行，4、已完赛）');
			$table->tinyInteger('route_type')->default('1')->comment('赛事类型');
			$table->string('contact_phone', 30)->nullable()->comment('赛事联系电话');
			$table->text('notice', 65535)->nullable()->comment('报名须知');
			$table->integer('sort')->default(0)->comment('排序');
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
		Schema::drop('races');
	}

}
