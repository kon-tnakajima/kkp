<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPriceAdoption0412 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_adoptions', function (Blueprint $table) {
            $table->string('sales_packaging_code', 256)->nullable()->comment('販売包装単位コード');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('price_adoptions', function (Blueprint $table) {
            //
            $table->dropColumn('sales_packaging_code');
        });
    }
}
