<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    	Schema::create('privileges', function (Blueprint $table) {
    		$table->increments('id');
    		$table->string('key_code')->comment('権限コード');
    		$table->string('name')->comment('権限名');
    		$table->integer('privilege_type')->comment('権限');
    		$table->integer('disp_order')->comment('並び順');
    		$table->text('description')->default('')->comment('権限説明');
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
        //
    	Schema::dropIfExists('privileges');
    }
}
