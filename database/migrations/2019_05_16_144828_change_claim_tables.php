<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeClaimTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transact_headers', function (Blueprint $table) {
            $table->dropColumn('trader_id');
            $table->dropColumn('receive_date');
            $table->integer('supply_division')->nullable()->comment('供給区分ID');
        });
        Schema::table('transact_confirmations', function (Blueprint $table) {
            $table->integer('transact_header_id')->nullable()->comment('取引ヘッダ参照ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
