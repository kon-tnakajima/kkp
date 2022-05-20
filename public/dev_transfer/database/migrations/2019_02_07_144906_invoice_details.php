<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoiceDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->increments('id')->comment('請求明細ID');
            $table->integer('invoice_id')->comment('請求ID');
            $table->integer('medicine_id')->comment('標準薬品ID');
            $table->integer('division')->comment('売仕区分');
            $table->integer('invoice_count')->comment('数量');
            $table->double('tax_rate')->comment('消費税率');
            $table->integer('sales_price')->comment('売上単価');
            $table->integer('sales_price_total')->comment('売上金額');
            $table->integer('sales_tax')->comment('売上消費税');
            $table->integer('purchase_price')->comment('仕入単価');
            $table->integer('purchase_price_total')->comment('仕入金額');
            $table->integer('purchase_tax')->comment('仕入消費税');
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
        Schema::dropIfExists('invoice_details');
    }
}
