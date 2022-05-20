<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnFacilitiyMedicine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facility_medicines', function (Blueprint $table) {
            $table->integer('medicine_id')->nullable()->change();
            $table->integer('price_adoption_id')->nullable();
            $table->integer('user_id')->default(0);
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
            $table->dropColumn('price_adoption_id');
            $table->dropColumn('user_id');
        });

    }
}
