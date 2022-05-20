<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupDataTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_data_types', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_group_id')->comment('ユーザグループ参照ID');
            $table->string('data_type_name')->comment('データ区分名');
            $table->integer('disp_order')->nullable()->comment('画面表示順');
            $table->integer('deleter')->nullable()->comment('削除者ユーザID');
            $table->integer('creater')->default(0)->comment('作成者ユーザID');
            $table->integer('updater')->default(0)->comment('更新者ユーザID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_group_data_types');
    }
}
