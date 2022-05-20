<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFacilityGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facility_groups', function (Blueprint $table) {
            $table->text('search')->default(0)->after('facility_group_id')->comment('全文検索');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facility_groups', function (Blueprint $table) {
            $table->dropColumn('search');
        });
    }
}
