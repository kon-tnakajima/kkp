<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Medicines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->increments('id')->comment('薬品ID');
            $table->string('kon_medicine_id', 256)->comment('クオン薬品ID');
            $table->integer('bunkaren_code')->comment('文化連コード');
            $table->string('code', 12)->comment('12桁コード');
            $table->integer('medicine_code')->comment('個別医薬品コード');
            $table->integer('receipt_computerized_processing1')->comment('レセプト電算処理コード1');
            $table->integer('receipt_computerized_processing2')->comment('レセプト電算処理コード2');
            $table->string('popular_name', 256)->comment('一般名');
            $table->string('name', 256)->comment('商品名');
            $table->string('phonetic', 256)->comment('商品名読み');
            $table->string('standard_unit', 32)->comment('規格単位');
            $table->string('package_presentation', 32)->comment('包装形態');
            $table->string('unit', 32)->comment('単位');
            $table->integer('drug_price_equivalent')->comment('薬価換算数');
            $table->integer('maker_id')->comment('メーカーID');
            $table->integer('selling_agency_code')->comment('販売元コード');
            $table->integer('medicine_effet_id')->comment('薬効ID');
            $table->integer('dosage_type_division')->comment('剤型区分');
            $table->integer('transitional_deadline')->comment('経過措置期限');
            $table->integer('danger_poison_category1')->comment('劇毒区分1');
            $table->integer('danger_poison_category2')->comment('劇毒区分2');
            $table->integer('danger_poison_category3')->comment('劇毒区分3');
            $table->integer('danger_poison_category4')->comment('劇毒区分4');
            $table->integer('danger_poison_category5')->comment('劇毒区分5');
            $table->integer('prescription_drug_category')->comment('処方箋医薬品区分');
            $table->integer('biological_product_classification')->comment('生物由来品区分');
            $table->boolean('generic_product_flag')->comment('後発品フラグ');
            $table->date('production_stop_date')->comment('製造中止日');
            $table->date('discontinuation_date')->comment('販売中止日');
            $table->integer('hospital_code')->comment('病院コード');
            $table->integer('owner_classification')->comment('オーナー区分（試薬区分）');
            $table->text('search')->default('')->comment('全文検索用');
            $table->integer('deleter')->nullable()->comment('削除者ユーザID');
            $table->integer('creater')->default(0)->comment('作成者ユーザID');
            $table->integer('updater')->default(0)->comment('更新者ユーザID');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medicines');
    }
}
