<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactConfirmations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //テーブル名を変更
        Schema::create('transact_confirmations', function (Blueprint $table) {
            $table->increments('id')->comment('請求支払確認ID');
            $table->date('claim_month')->nullable()->comment('取引年月');
            $table->integer('facility_id')->nullable()->comment('施設参照ID');
            $table->integer('trader_id')->nullable()->comment('業者参照ID');
            $table->integer('supply_division')->nullable()->comment('供給区分ID');
            $table->integer('transact_file_storage_id')->comment('取引ファイル管理参照ID');
            $table->integer('facility_confirmation_status')->nullable()->comment('施設確認状況');
            $table->date('facility_confirmation_date')->nullable()->comment('施設確認日');
            $table->integer('facility_confirmation_account')->nullable()->comment('施設確認アカウント');
            $table->integer('trader_confirmation_status')->nullable()->comment('業者確認状況');
            $table->date('trader_confirmation_date')->nullable()->comment('業者確認日');
            $table->integer('trader_confirmation_account')->nullable()->comment('業者確認アカウント');
            $table->decimal('medicine_price_total', 18, 2)->nullable()->comment('薬価金額合計');
            $table->decimal('sales_price_total', 18, 2)->nullable()->comment('売上金額合計');
            $table->decimal('purchase_price_total', 18, 2)->nullable()->comment('仕入金額合計');
            $table->integer('detail_count')->comment('取引レコード数');
            $table->integer('claim_invoice_id')->nullable()->comment('請求書参照ID');
            $table->integer('claim_payment_id')->nullable()->comment('支払確認書参照ID');
            $table->integer('facility_comment_id')->nullable()->comment('施設コメント');
            $table->integer('trader_comment_id')->nullable()->comment('業者コメント');
            $table->integer('bunkaren_comment_id')->nullable()->comment('文化連コメント');
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
        Schema::dropIfExists('transact_confirmations');
    }
}
