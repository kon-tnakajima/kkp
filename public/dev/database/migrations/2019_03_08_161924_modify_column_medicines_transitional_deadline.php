<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnMedicinesTransitionalDeadline extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 一度落としてからでないと型変更できない
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('transitional_deadline');
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->date('transitional_deadline')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('transitional_deadline');
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->integer('transitional_deadline')->comment('経過措置期限');
        });
    }
}
