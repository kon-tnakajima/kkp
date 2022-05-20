<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserGroupRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_group_relations', function (Blueprint $table) {
            $table->increments('id')->comment('ユーザグループ関連ID');
            $table->integer('user_id')->comment('ユーザID');
            $table->integer('user_group_id')->comment('ユーザグループID');
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
        Schema::dropIfExists('user_group_relations');
    }
}
