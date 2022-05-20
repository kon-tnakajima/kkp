<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMedicinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medicines', function (Blueprint $table) {
			$table->string('old_code',12)->nullable()->comment('旧12桁コード');
			$table->string('unity_medicine_code',9)->nullable()->comment('統一薬品コード');
			$table->string('drug_price_listing_name',256)->nullable()->comment('薬価収載名称');
			$table->string('receipt_medicine_code',256)->nullable()->comment('レセ用医薬品名');
			$table->string('name_half',256)->nullable()->comment('半角商品名');
			$table->string('name_kanpo',256)->nullable()->comment('商品名漢方');
			$table->string('standard_unit_half',256)->nullable()->comment('半角規格容量');
			$table->string('standard_unit_name',256)->nullable()->comment('規格単位名称');
			$table->string('standard_unit_symbol',256)->nullable()->comment('規格単位記号');
			$table->string('jpn_standard_classification_number',256)->nullable()->comment('日本標準商品分類番号');
			$table->string('dosage_type_code',4)->nullable()->comment('剤型コード');
			$table->string('dosage_type_symbol',256)->nullable()->comment('剤型記号');
			$table->string('dosage_type_name',256)->nullable()->comment('剤型名称');
			$table->string('stimulant_flag',4)->nullable()->comment('覚醒剤');
			$table->string('stimulant_material_flag',4)->nullable()->comment('覚醒剤原料');
			$table->string('addictive_drug_flag',1)->nullable()->comment('習慣性医薬品');
			$table->string('blood_product_flag',1)->nullable()->comment('血液製剤');
			$table->string('jpn_pharmacopoeia',1)->nullable()->comment('日本薬局方');
			$table->integer('life_time')->nullable()->comment('有効期間');
			$table->integer('expiration_date')->nullable()->comment('使用期限');
			$table->string('medicine_price_listing_kbn',1)->nullable()->comment('薬価基準収載区分');
			$table->string('bookkeeping_division',1)->nullable()->comment('記帳義務区分');
			$table->string('medicine_price_revision_date',8)->nullable()->comment('薬価改定年月日');
			$table->string('medicine_price_listing_date',8)->nullable()->comment('薬価収載年月日');
			$table->string('sales_start_date',8)->nullable()->comment('販売開始日');
			$table->string('discontinuing_division',1)->nullable()->comment('中止理由区分');
			$table->string('maintenance_division',1)->nullable()->comment('メンテナンス区分');
			$table->string('advance_division_generic',1)->nullable()->comment('後発品のある先発区分');
			$table->string('authorized_generic',1)->nullable()->comment('オーソライズドジェネリック');
			$table->string('biosimilar',1)->nullable()->comment('バイオシミラー');
			$table->string('high_risk_medicine',1)->nullable()->comment('ハイリスク薬');
			$table->string('room_temperature',4)->nullable()->comment('室温');
			$table->string('cold_place',4)->nullable()->comment('冷所');
			$table->string('refrigeration',4)->nullable()->comment('冷蔵');
			$table->string('forzen',4)->nullable()->comment('冷凍');
			$table->string('dark_place',4)->nullable()->comment('暗所');
			$table->string('shade',4)->nullable()->comment('遮光');
			$table->string('airtight_container',4)->nullable()->comment('気密容器');
			$table->string('sealed_container',4)->nullable()->comment('密封容器');
			$table->string('hazaedous_material',4)->nullable()->comment('危険物');
			$table->string('temperature_upper_limit',3)->nullable()->comment('温度上限');
			$table->string('temperature_lower_limit',3)->nullable()->comment('温度下限');
			$table->string('notes_on_other_storage',128)->nullable()->comment('その他保管上の注意');
			$table->string('update_classification',1)->nullable()->comment('更新区分');
			$table->string('data_registration_date',8)->nullable()->comment('登録年月日');
			$table->string('data_update_date',8)->nullable()->comment('更新年月日');
			$table->string('dispensing_packaging_code',14)->nullable()->comment('調剤包装単位コード');
			$table->string('sales_packaging_code',14)->nullable()->comment('販売包装単位コード');
			$table->string('popular_name_kana',320)->nullable()->comment('一般名カナ');
			$table->decimal('min_drug_price_equivalent',5,2)->nullable()->comment('最小薬価換算係数');
			$table->string('old_dispensing_packaging_code',14)->nullable()->comment('旧調剤包装単位コード');
			$table->string('old_sales_packaging_code',14)->nullable()->comment('旧販売包装単位コード');
			$table->string('medicine_code_regist_date',8)->nullable()->comment('YJコード収載年月日');
			$table->string('release_stop_date',8)->nullable()->comment('発売中止日');
			$table->string('original_drug_division',1)->nullable()->comment('先発医薬品区分');
			$table->string('generic_product_detail_devision',1)->nullable()->comment('後発品詳細区分');
			$table->string('generic_product_detail_name',512)->nullable()->comment('後発品詳細名');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medicines', function (Blueprint $table) {
			$table->dropColumn('old_code');
			$table->dropColumn('unity_medicine_code');
			$table->dropColumn('drug_price_listing_name');
			$table->dropColumn('receipt_medicine_code');
			$table->dropColumn('name_half');
			$table->dropColumn('name_kanpo');
			$table->dropColumn('standard_unit_half');
			$table->dropColumn('standard_unit_name');
			$table->dropColumn('standard_unit_symbol');
			$table->dropColumn('jpn_standard_classification_number');
			$table->dropColumn('dosage_type_code');
			$table->dropColumn('dosage_type_symbol');
			$table->dropColumn('dosage_type_name');
			$table->dropColumn('stimulant_flag');
			$table->dropColumn('stimulant_material_flag');
			$table->dropColumn('addictive_drug_flag');
			$table->dropColumn('blood_product_flag');
			$table->dropColumn('jpn_pharmacopoeia');
			$table->dropColumn('life_time');
			$table->dropColumn('expiration_date');
			$table->dropColumn('medicine_price_listing_kbn');
			$table->dropColumn('bookkeeping_division');
			$table->dropColumn('medicine_price_revision_date');
			$table->dropColumn('medicine_price_listing_date');
			$table->dropColumn('sales_start_date');
			$table->dropColumn('discontinuing_division');
			$table->dropColumn('maintenance_division');
			$table->dropColumn('advance_division_generic');
			$table->dropColumn('authorized_generic');
			$table->dropColumn('biosimilar');
			$table->dropColumn('high_risk_medicine');
			$table->dropColumn('room_temperature');
			$table->dropColumn('cold_place');
			$table->dropColumn('refrigeration');
			$table->dropColumn('forzen');
			$table->dropColumn('dark_place');
			$table->dropColumn('shade');
			$table->dropColumn('airtight_container');
			$table->dropColumn('sealed_container');
			$table->dropColumn('hazaedous_material');
			$table->dropColumn('temperature_upper_limit');
			$table->dropColumn('temperature_lower_limit');
			$table->dropColumn('notes_on_other_storage');
			$table->dropColumn('update_classification');
			$table->dropColumn('data_registration_date');
			$table->dropColumn('data_update_date');
			$table->dropColumn('dispensing_packaging_code');
			$table->dropColumn('sales_packaging_code');
			$table->dropColumn('popular_name_kana');
			$table->dropColumn('min_drug_price_equivalent');
			$table->dropColumn('old_dispensing_packaging_code');
			$table->dropColumn('old_sales_packaging_code');
			$table->dropColumn('medicine_code_regist_date');
			$table->dropColumn('release_stop_date');
			$table->dropColumn('original_drug_division');
			$table->dropColumn('generic_product_detail_devision');
			$table->dropColumn('generic_product_detail_name');
        	//
        });
    }
}
