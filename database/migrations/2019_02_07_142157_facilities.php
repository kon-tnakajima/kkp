<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Facilities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->increments('id')->comment('施設ID');
            $table->string('code', 20)->comment('施設コード');
            $table->string('name', 100)->comment('施設名');
            $table->string('formal_name', 100)->nullable()->comment('正式名');
            $table->integer('actor_id')->comment('アクターID');
            $table->string('zip', 100)->nullable()->comment('郵便番号');
            $table->string('address', 100)->nullable()->comment('住所');
            $table->string('tel', 100)->nullable()->comment('電話番号');
            $table->string('fax', 100)->nullable()->comment('FAX');
            $table->boolean('is_online')->default(true)->comment('業者EDI使用・未使用');
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
        Schema::dropIfExists('facilities');
    }
}
