<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Invoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id')->comment('施設薬品ID');
            $table->integer('facility_id')->comment('施設ID');
            $table->date('invoice_date')->comment('請求日');
            $table->integer('trader_id')->comment('業者ID');
            $table->integer('status')->comment('業者ID');
            $table->integer('medicine_price_total')->comment('薬価金額合計');
            $table->integer('purchase_price_total')->comment('購入金額合計');
            $table->string('file_name', 256)->comment('添付ファイル');
            $table->integer('detail_count')->comment('明細件数');
            $table->integer('file_upload_user_id')->comment('請求ファイル登録ユーザID');
            $table->integer('deal_regist_user_id')->comment('売買登録ユーザID');
            $table->integer('auth_user_id')->comment('確認者ユーザID');
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
        Schema::dropIfExists('invoices');
    }
}
