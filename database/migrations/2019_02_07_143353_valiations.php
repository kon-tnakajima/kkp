<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Valiations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('valiations', function (Blueprint $table) {
            $table->increments('id')->comment('バリエーションID');
            $table->integer('price_adoption_id')->comment('採用申請ID');
            $table->integer('current_task_id')->comment('現在のタスクID');
            $table->integer('next_task_id')->comment('次ののタスクID');
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
        Schema::dropIfExists('valiations');
    }
}
