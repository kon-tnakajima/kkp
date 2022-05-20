<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaimInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //テーブル名を変更
        Schema::create('claim_invoices', function (Blueprint $table) {
            $table->increments('id')->comment('請求書ID');
            $table->date('claim_month')->nullable()->comment('取引年月');
            $table->integer('facility_id')->nullable()->comment('施設参照ID');
            $table->integer('trader_id')->nullable()->comment('業者参照ID');
            $table->string('bunkaren_billing_code', 32)->comment('文化連請求先CD');
            $table->string('invoice_name', 256)->comment('請求先名');
            $table->binary('attachment')->nullable()->comment('添付ファイル');
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
        Schema::dropIfExists('claim_invoices');
    }
}
