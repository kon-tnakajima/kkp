<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PackUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pack_units', function (Blueprint $table) {
            $table->increments('id')->comment('包装単位ID');
            $table->integer('medicine_id')->comment('標準薬品ID');
            $table->string('hot_code', 16)->comment('hotコード');
            $table->string('jan_code', 16)->comment('janコード');
            $table->string('display_pack_unit', 100)->comment('表示用包装単位');
            $table->integer('pack_count')->comment('包装数量');
            $table->string('pack_unit', 100)->comment('包装単位');
            $table->integer('total_pack_count')->comment('総包装数量');
            $table->string('total_pack_unit', 100)->comment('総包装単位');
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
        Schema::dropIfExists('pack_units');
    }
}
