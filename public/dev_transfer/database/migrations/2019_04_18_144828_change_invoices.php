<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invoices');

        //テーブル名を変更
        Schema::create('transact_file_storages', function (Blueprint $table) {
            $table->increments('id')->comment('取引ファイル管理ID');

            $table->date('claim_month')->nullable()->comment('取引年月');
            $table->integer('facility_id')->nullable()->comment('施設参照ID');
            $table->integer('trader_id')->nullable()->comment('業者参照ID');
            $table->integer('supply_division')->nullable()->comment('供給区分ID');
            $table->integer('is_stock_or_sale')->nullable()->comment('売仕区分');
    
            $table->decimal('medicine_price_total', 18, 2)->nullable()->comment('薬価金額合計');
            $table->decimal('sales_price_total', 18, 2)->nullable()->comment('売上金額合計');
            $table->decimal('purchase_price_total', 18, 2)->nullable()->comment('仕入金額合計');
            $table->integer('claim_task_status')->nullable()->comment('請求タスク状態');
            $table->binary('attachment')->nullable()->comment('添付ファイル');
            $table->string('file_name', 512)->nullable()->comment('添付ファイル名');
            $table->date('receipt_date')->nullable()->comment('受取日');

            $table->text('comment')->nullable()->nullable()->comment('備考');
            $table->integer('target_actor_id')->default(1)->comment('バリエーション対象アクタ');

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
        Schema::dropIfExists('transact_file_storages');
    }
}
