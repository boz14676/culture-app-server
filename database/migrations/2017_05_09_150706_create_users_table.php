<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/*Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('weapp_openid')->nullable()->unique()->comment('用户小程序 openid');
			$table->string('wechat_openid')->nullable()->unique()->comment('用户微信 openid');
			$table->string('unionid')->nullable()->unique()->comment('用户微信&小程序 unionid');
			
			$table->enum('is_bind', array('1','2'))->default('2')->comment('是否被绑定（1、绑定 2、未绑定）');
			$table->bigInteger('mobile')->unsigned()->nullable()->unique('phone_UNIQUE')->comment('用户手机号码');
			$table->string('nickname', 30)->comment('用户昵称');
			$table->string('avatar')->comment('用户头像');
			$table->enum('gender', array('0','1','2'))->nullable()->default('0')->comment('用户性别');
			$table->string('name', 30)->comment('用户真实姓名');
			$table->date('birthday')->nullable()->comment('用户的出生日期');
			$table->enum('id_number_type', ['1', '2'])->default('1')->comment('用户的证件类型');
			$table->string('id_number', 18)->comment('用户身份证号码');
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
		/*Schema::drop('users');*/
	}

}
