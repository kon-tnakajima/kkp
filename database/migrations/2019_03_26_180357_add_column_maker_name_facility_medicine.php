<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMakerNameFacilityMedicine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facility_medicines', function (Blueprint $table) {
            //
            $table->string('maker_name', 256)->nullable()->comment('メーカー名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facility_medicines', function (Blueprint $table) {
            //
            $table->dropColumn('maker_name');
        });
    }
}
