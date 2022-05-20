<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RolePrivilegeRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    	Schema::create('role_privilege_relations', function (Blueprint $table) {
    		$table->increments('id');
    		$table->string('role_key_code')->comment('ロールコード');
    		$table->string('privelege_key_code')->comment('権限コード');
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
    	Schema::dropIfExists('role_privilege_relations');
    }
}
