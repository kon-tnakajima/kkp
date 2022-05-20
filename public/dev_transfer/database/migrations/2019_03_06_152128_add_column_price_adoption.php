<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPriceAdoption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_adoptions', function (Blueprint $table) {
            $table->string('jan_code', 16)->default('')->comment('janコード');
            $table->date('start_date')->nullable()->comment('適用開始日');
            $table->date('end_date')->nullable()->comment('適用終了日');
            $table->string('name', 256)->nullable()->comment('商品名');
            $table->string('phonetic', 256)->nullable()->comment('商品名読み');
            $table->string('standard_unit', 32)->nullable()->comment('規格単位');
            $table->string('unit', 32)->nullable()->comment('単位');
            $table->string('pack_unit', 100)->nullable()->comment('包装単位');
            $table->integer('maker_id')->nullable()->comment('メーカーID');
            $table->integer('medicine_price')->nullable()->comment('薬価');
            $table->string('product_relation_id', 256)->nullable()->comment('商品連携ID');
            $table->integer('medicine_id')->nullable()->change();
            $table->integer('sales_price')->nullable()->change();
            $table->integer('pack_unit_price')->nullable()->comment('包装薬価');
            $table->integer('coefficient')->nullable()->comment('包装薬価係数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_adoptions', function (Blueprint $table) {
            $table->dropColumn('jan_code');
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('name');
            $table->dropColumn('phonetic');
            $table->dropColumn('standard_unit');
            $table->dropColumn('unit');
            $table->dropColumn('pack_unit');
            $table->dropColumn('maker_id');
            $table->dropColumn('product_relation_id');
            $table->dropColumn('medicine_price');
            $table->dropColumn('pack_unit_price');
            $table->dropColumn('coefficient');
            $table->integer('medicine_id')->nullable(false)->change();
            $table->integer('sales_price')->nullable(false)->change();
        });

    }
}
