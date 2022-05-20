<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInvoiceDetailComments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoice_detail_comments');

        Schema::create('claim_history_comments', function (Blueprint $table) {
            $table->increments('id')->comment('取引履歴コメントID');
            $table->integer('trading_history_id')->comment('取引履歴ID');
            $table->integer('described_user_id')->comment('ユーザ参照ID');
            $table->boolean('read_flg')->default(false)->comment('既読フラグ');
            $table->text('comment')->comment('コメント');
            $table->integer('transact_confirmation_id')->comment('請求支払確認参照ID');
            $table->integer('deleter')->nullable()->comment('削除者ユーザID');
            $table->integer('creater')->default(0)->comment('作成者ユーザID');
            $table->integer('updater')->default(0)->comment('更新者ユーザID');
            $table->timestamps();
            $table->softDeletes();
        });
        
        //シーケンス追加
        DB::statement('create sequence trading_history_id_seq;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claim_history_comments');
    }
}
