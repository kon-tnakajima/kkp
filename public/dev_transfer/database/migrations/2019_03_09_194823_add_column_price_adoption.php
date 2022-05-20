<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPriceAdoption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('price_adoptions', function (Blueprint $table) {
            $table->string('maker_name', 256)->nullable()->comment('メーカー名');
            $table->integer('owner_classification')->nullable()->comment('オーナー区分');
            //
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
            $table->dropColumn('maker_name');
            $table->dropColumn('owner_classification');
        });
    }
}
