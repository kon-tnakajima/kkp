<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailQuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_ques', function (Blueprint $table) {
            $table->increments('id')->comment('メールキューID');
            $table->date('send_date')->comment('メール送信日');
            $table->integer('send_que_id')->nullable()->comment('送信ID');
            $table->string('mail_to', 256)->comment('メール送信先');
            $table->string('subject', 256)->comment('メール件名');
            $table->string('facility', 256)->comment('施設名');
            $table->string('jan_code', 256)->nullable()->comment('JANコード');
            $table->string('name', 256)->nullable()->comment('商品名');
            $table->string('maker_name', 256)->nullable()->comment('メーカー名');
            $table->string('mail_url', 256)->nullable()->comment('参照URL');
            $table->string('mail_str', 256)->comment('ステータス');
            $table->integer('is_send')->default(0)->comment('送信フラグ');
            $table->integer('is_target')->default(0)->comment('担当者フラグ');
            //$table->string('result_code', 256)->nullable()->comment('送信結果コード');
            //$table->text('result_message', 256)->nullable()->comment('送信結果メッセージ');
            $table->integer('deleter')->nullable()->comment('削除者ユーザID');
            $table->integer('creater')->default(0)->comment('作成者ユーザID');
            $table->integer('updater')->default(0)->comment('更新者ユーザID');
            $table->timestamps();
            $table->softDeletes();
        });
        
        //シーケンス追加
        DB::statement('create sequence send_que_id_seq;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_ques');
    }
}
