<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FacilityMedicines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facility_medicines', function (Blueprint $table) {
            $table->increments('id')->comment('施設薬品ID');
            $table->integer('facility_id')->comment('施設ID');
            $table->integer('medicine_id')->comment('標準薬品ID');
            $table->date('adoption_date')->comment('採用日');
            $table->date('adoption_stop_date')->nullable()->comment('採用停止日');
            $table->string('internal_name', 256)->nullable()->comment('院内薬品名称');
            $table->string('internal_name_before', 256)->nullable()->comment('院内薬品名称前');
            $table->string('internal_name_after', 256)->nullable()->comment('院内薬品名称後');
            $table->string('internal_name_kana', 256)->nullable()->comment('院内薬品名称かな');
            $table->string('internal_nickname', 256)->nullable()->comment('院内薬品名称略');
            $table->string('unit', 256)->nullable()->comment('規格単位');
            $table->string('code1', 512)->default('')->comment('予備コード1');
            $table->string('code2', 512)->default('')->comment('予備コード2');
            $table->string('code3', 512)->default('')->comment('予備コード3');
            $table->integer('division1')->default(0)->comment('予備区分1');
            $table->integer('division2')->default(0)->comment('予備区分2');
            $table->integer('division3')->default(0)->comment('予備区分3');
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
        Schema::dropIfExists('facility_medicines');
    }
}
