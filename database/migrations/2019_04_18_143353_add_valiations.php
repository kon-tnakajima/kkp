<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddValiations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('valiations', function (Blueprint $table) {
            $table->integer('claim_id')->nullable()->comment('請求ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('valiations', function (Blueprint $table) {
            $table->dropColumn('claim_id');
        });
    }
}
