<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPackUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pack_units', function (Blueprint $table) {
            $table->integer('price')->default(0)->after('total_pack_unit')->comment('包装薬価');
            $table->integer('coefficient')->default(0)->after('price')->comment('包装薬価係数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pack_units', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('coefficient');
        });
    }
}
