<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactHeaders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //テーブル名を変更
        Schema::create('transact_headers', function (Blueprint $table) {
            $table->increments('id')->comment('取引ヘッダID');
            $table->date('claim_month')->nullable()->comment('取引年月');
            $table->integer('facility_id')->nullable()->comment('施設参照ID');
            $table->integer('trader_id')->nullable()->comment('業者参照ID');
            $table->integer('transact_file_storage_id')->comment('取引ファイル管理参照ID');
            $table->date('receive_date')->nullable()->comment('受取日');
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
        Schema::dropIfExists('transact_headers');
    }
}
