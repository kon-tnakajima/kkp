<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PriceAdoptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_adoptions', function (Blueprint $table) {
            $table->increments('id')->comment('採用申請ID');
            $table->integer('facility_id')->comment('施設ID');
            $table->integer('medicine_id')->comment('標準薬品ID');
            $table->integer('user_id')->comment('ユーザID');
            $table->timestamp('application_date')->comment('申請日');
            $table->integer('status')->comment('申請状況');
            $table->integer('approval_user_id')->nullable()->comment('承認者');
            $table->integer('purchase_price')->comment('仕入単価');
            $table->integer('sales_price')->comment('売上単価');
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
        Schema::dropIfExists('price_adoptions');
    }
}
