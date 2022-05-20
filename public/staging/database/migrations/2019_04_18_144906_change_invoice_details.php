<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInvoiceDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoice_details');

        //テーブル名を変更
        Schema::create('transact_details', function (Blueprint $table) {
            $table->increments('id')->comment('取引明細ID');
            $table->date('claim_date')->nullable()->comment('取引年月');
            $table->integer('facility_id')->nullable()->comment('施設参照ID');
            $table->integer('trader_id')->nullable()->comment('業者参照ID');
            $table->integer('supply_division')->nullable()->comment('供給区分ID');
            $table->integer('transact_header_id')->comment('取引ヘッダ参照ID');
            $table->integer('transact_confirmation_id')->comment('請求支払確認参照ID');
            $table->string('bunkaren_trading_history_code', 32)->comment('文化連取引履歴ID');
            $table->string('bunkaren_billing_code', 32)->comment('文化連請求先CD');
            $table->string('bunkaren_payment_code', 32)->comment('文化連支払先CD');
            $table->integer('claim_invoice_id')->comment('請求書参照ID');
            $table->integer('claim_payment_id')->comment('支払確認書参照ID');
            $table->string('bunkaren_item_code', 32)->comment('文化連商品CD');
            $table->string('facility_item_code', 32)->comment('施設商品ID');
            $table->string('trader_item_code', 32)->comment('業者商品ID');
            $table->integer('medicine_id')->comment('標準薬品参照ID');
            $table->string('gs1_sscc', 18)->comment('GS1_SSCC');
            $table->string('maker_name', 80)->comment('メーカー名');
            $table->string('item_name')->comment('商品名');
            $table->string('standard')->comment('規格');
            $table->string('item_code')->comment('製品番号');
            $table->string('jan_code')->comment('JANコード');
            $table->string('gtin_code')->comment('GTINコード');
            $table->decimal('refund_price', 18, 2)->nullable()->comment('償還価格');
            $table->integer('quantity')->comment('数量');
            $table->string('unit_name')->comment('単位名');
            $table->integer('is_stock_or_sale')->comment('売仕区分');
            $table->integer('tax_division')->comment('消費税率区分');
            $table->decimal('tax_rate', 6, 4)->nullable()->comment('消費税率');
            $table->decimal('sales_unit_price', 18, 2)->nullable()->comment('売上単価');
            $table->decimal('sales_price', 18, 2)->nullable()->comment('売上金額');
            $table->text('sales_comment')->nullable()->comment('売上備考');
            $table->string('sales_slip_number', 256)->comment('売上伝票番号');
            $table->decimal('buy_unit_price', 18, 2)->nullable()->comment('仕入単価');
            $table->decimal('buy_price', 18, 2)->nullable()->comment('仕入金額');
            $table->text('buy_comment')->nullable()->comment('仕入備考');
            $table->string('buy_slip_number', 256)->comment('仕入伝票番号');
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
        Schema::dropIfExists('transact_details');
    }
}
