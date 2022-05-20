<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AccountRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('account_requests', function (Blueprint $table) {
            $table->increments('id')->comment('アカウント申請ID');
            $table->string('email', 100)->comment('メールアドレスID');
            $table->string('organization_id', 10)->comment('組織ID');
            $table->string('name', 255)->comment('ユーザー名');
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
        Schema::dropIfExists('account_requests');
    }
}
