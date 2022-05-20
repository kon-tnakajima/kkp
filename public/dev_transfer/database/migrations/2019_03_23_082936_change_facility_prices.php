<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFacilityPrices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('facility_prices');

        Schema::create('facility_prices', function (Blueprint $table) {
            //
            $table->increments('id')->comment('納入価ID');
            $table->integer('facility_medicine_id')->nullable()->comment('施設薬品ID');
            $table->integer('trader_id')->nullable()->comment('業者ID');
            $table->decimal('purchase_price', 11, 2)->nullable()->comment('仕入単価');
            $table->integer('sales_price')->comment('納入単価');

            $table->date('start_date')->comment('適用開始日');
            $table->date('end_date')->comment('適用終了日');

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
