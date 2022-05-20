<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FacilityRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_relations', function (Blueprint $table) {
            $table->increments('id')->comment('施設関連ID');
            $table->integer('facility_id')->comment('施設ID');
            $table->integer('parent_facility_id')->comment('相手施設ID');
            $table->integer('deleter')->nullable()->comment('削除者ユーザID');
            $table->integer('creater')->comment('作成者ユーザID');
            $table->integer('updater')->comment('更新者ユーザID');
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
        Schema::dropIfExists('facility_relations');
    }
}
