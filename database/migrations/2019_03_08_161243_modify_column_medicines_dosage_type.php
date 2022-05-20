<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyColumnMedicinesDosageType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('danger_poison_category1')->nullable()->change();
            $table->string('danger_poison_category2')->nullable()->change();
            $table->string('danger_poison_category3')->nullable()->change();
            $table->string('danger_poison_category4')->nullable()->change();
            $table->string('danger_poison_category5')->nullable()->change();
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
        // 戻すロジックを書くとエラーが起きそうなのであえてかかない
    }
}
