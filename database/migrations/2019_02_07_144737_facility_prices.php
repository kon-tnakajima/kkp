<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FacilityPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('facility_prices', function (Blueprint $table) {
            $table->increments('id')->comment('納入価ID');
            $table->integer('facility_medicine_id')->comment('施設薬品ID');
            $table->integer('trade_code')->comment('業者コード');
            $table->integer('delivery_price1')->comment('納入価1');
            $table->integer('delivery_price2')->comment('納入価2');
            $table->integer('consumption_price1')->comment('消費価1');
            $table->integer('consumption_price2')->comment('消費価2');
            $table->double('discount_rate1')->comment('値引率1');
            $table->double('discount_rate2')->comment('値引率2');
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
        Schema::dropIfExists('facility_prices');
    }
}
