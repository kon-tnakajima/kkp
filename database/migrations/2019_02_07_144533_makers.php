<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Makers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('makers', function (Blueprint $table) {
            $table->increments('id')->comment('包装単位ID');
            $table->date('start_date')->nullable()->comment('適用開始日');
            $table->date('end_date')->nullable()->comment('適用終了日');
            $table->string('name', 256)->comment('メーカー名');
            $table->string('name_kana', 256)->comment('メーカー名かな');
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
        Schema::dropIfExists('makers');
    }
}
