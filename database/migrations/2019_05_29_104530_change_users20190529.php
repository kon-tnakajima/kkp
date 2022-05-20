<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUsers20190529 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sub_id')->nullable()->comment('サブID');
        });
        
        //シーケンス追加
        DB::statement('create sequence users_id_seq start 50;');
        //usersテーブルのIDとシーケンスを紐付ける
        DB::statement("alter table users alter id set default nextval('users_id_seq');");
        //IDが削除されたらシーケンスも削除されるようにする
        DB::statement("alter sequence users_id_seq owned by users.id");

        Schema::table('password_resets', function (Blueprint $table) {
            $table->string('sub_id')->nullable()->comment('サブID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sub_id');
        });
    }
}
