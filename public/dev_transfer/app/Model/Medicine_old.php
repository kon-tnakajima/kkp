<?php

// namespace App\Model;

// use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Pagination\LengthAwarePaginator;
// use App\Model\Facility;
// use App\Model\FacilityMedicine;
// use App\Model\MedicinePrice;
// use Illuminate\Http\Request;
// use App\Model\Concerns\Calc as CalcTrait;

// class Medicine extends Model
// {
//     use SoftDeletes;
//     use CalcTrait;

//     protected $guraded = ['id'];
//     protected $fillable = [
//         'kon_medicine_id',
//         'bunkaren_code',
//         'code',
//         'medicine_code',
//         'receipt_computerized_processing1',
//         'receipt_computerized_processing2',
//         'popular_name',
//         'name',
//         'phonetic',
//         'standard_unit',
//         'package_presentation',
//         'unit',
//         'drug_price_equivalent',
//         'maker_id',
//         'selling_agency_code',
//         'medicine_effet_id',
//         'dosage_type_division',
//         'transitional_deadline',
//         'danger_poison_category1',
//         'danger_poison_category2',
//         'danger_poison_category3',
//         'danger_poison_category4',
//         'danger_poison_category5',
//         'prescription_drug_category',
//         'biological_product_classification',
//         'generic_product_flag',
//         'production_stop_date',
//         'discontinuation_date',
//         'hospital_code',
//         'owner_classification',
//         'deleter',
//         'creater',
//         'updater'
//     ];

//     const DOSAGE_TYPE_INTERNAL = 1;
//     const DOSAGE_TYPE_EXTERNAL = 2;
//     const DOSAGE_TYPE_INJECTION = 3;
//     const DOSAGE_TYPE_DENTISTRY = 4;
//     const DOSAGE_TYPE_OTHER = 5;

//     const OWNER_CLASSIFICATION_BUNKAREN = 1;
//     const OWNER_CLASSIFICATION_HQ = 2;
//     const OWNER_CLASSIFICATION_HOSPITAL = 3;

//     const OWNER_CLASSIFICATION_STR = [
//         self::OWNER_CLASSIFICATION_BUNKAREN => '文化連',
//         self::OWNER_CLASSIFICATION_HQ => '本部',
//         self::OWNER_CLASSIFICATION_HOSPITAL => '病院',
//         ];

//     const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT1 = " ";
//     const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT2 = "生";
//     const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT3 = "特生";

//     const GENERIC_PRODUCT_DETAIL_DEVISION_NOT_APPLICABLE = 0;
//     const GENERIC_PRODUCT_DETAIL_DEVISION_PATENT = 1;
//     const GENERIC_PRODUCT_DETAIL_DEVISION_LONG_TERM = 2;
//     const GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC = 3;
//     const GENERIC_PRODUCT_DETAIL_DEVISION_ORIGINAL = 4;
//     const GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC2 = 5;

//     const GENERIC_PRODUCT_DETAIL_DEVISION_STR = [
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_NOT_APPLICABLE => '',
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_PATENT => '特許品',
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_LONG_TERM => '長期品',
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC => '後発品',
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_ORIGINAL => '☆先発',
//         self::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC2 => '★後発',
//         ];

//     const DISCONTINUING_DIVISION_NONE = 0;
//     const DISCONTINUING_DIVISION_STOP = 1;
//     const DISCONTINUING_DIVISION_MODIFER = 2;

    /*
     * 承認申請一覧(削除対象)
     */
//     public function listWithApply_old(Request $request, Facility $facility, $count)
//     {
//         // サブクエリ
//         $pa = new PriceAdoption();
//         $p_query = $pa->select(\DB::raw('medicine_id as medicine_id
//                                ,\'\' as code
//                                ,price_adoptions.sales_packaging_code
//                                ,\'\' as generic_product_detail_devision
//                                ,\'\' as popular_name
//                                ,jan_code
//                                ,price_adoptions.name as medicine_name
//                                ,standard_unit
//                                ,maker_name
//                                ,medicine_price as price
//                                ,comment
//                                ,status
//                                ,facilities.id as facility_id
//                                ,facilities.name as facility_name
//                                ,users.name as user_name
//                                ,pack_unit
//                                ,medicine_price as pack_unit_price
//                                ,coefficient
//                                ,application_date
//                                ,trader.name as trader_name
//                                ,0 as fm_parent
//                                ,0 as fm_own
//                                ,sales_price
//                                ,0 as fp_id
//                                ,price_adoptions.id as price_adoption_id'))
//                     ->leftJoin('makers', 'price_adoptions.maker_id', '=', 'makers.id')
//                     ->leftJoin('facilities', 'facilities.id', '=', 'price_adoptions.facility_id')
//                     ->leftJoin('facilities as trader', 'trader.id', '=', 'price_adoptions.trader_id')
//                     ->leftJoin('users', 'users.id', '=', 'price_adoptions.user_id')
//                     ->getFacility($facility, 'price_adoptions.facility_id');

//         // 状態
//         if (!empty($request->status)) {
//             $p_query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
//         }
//         // 施設
//         if (!empty($request->facility)) {
//             $p_query->whereIn('facilities.id', $request->facility);
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $p_query->where('status', -1);
//         }
//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $p_query->where("coefficient", \DB::raw($request->coefficient));
//         }
//         $search_words = [];
//         // 全文検索
//         if (!empty($request->search)) {
//             $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//             foreach($search_words as $word) {
//                 $p_query->where('price_adoptions.search', 'like', '%'.$word.'%');
//             }
//         }

//         // 先発後発品区分
//         if (!empty($request->generic_product_detail_devision)) {
//         	$p_query->where('status', -1);
//         }

//         // 剤形区分
//         if (!empty($request->dosage_type_division)) {
//         	$p_query->where('status', -1);
//         }

//         // オーナー区分
//         if (!empty($request->owner_classification)) {
//             $p_query->where('price_adoptions.owner_classification', $request->owner_classification);
//         }
//         $p_query->whereNull("price_adoptions.medicine_id");


// //-----------------------------------
//         // サブクエリ(本部の施設薬品)
//         $fm_parent = \DB::table('facility_medicines')->select('id','facility_id', 'medicine_id', 'maker_name')->where('facility_id', $facility->id)->where('deleted_at', null);
//         $fm_own = \DB::table('facility_medicines')->select('id','facility_id', 'medicine_id', 'maker_name')->where('facility_id', $facility->id)->where('deleted_at', null);
//         if($facility->isHospital()){
//             $fm_parent = \DB::table('facility_medicines')->select('id','facility_id', 'medicine_id', 'maker_name')->where('facility_id', $facility->parent()->id)->where('deleted_at', null);
//             $fm_own = \DB::table('facility_medicines')->select('id','facility_id', 'medicine_id', 'maker_name')->where('facility_id', $facility->id)->where('deleted_at', null);
//         }

//         $medicineOnly = \DB::table('price_adoptions')->where('facility_id', $facility->id)->where('deleted_at', null);
//         $m_query = $this->select(\DB::raw('medicines.id as medicine_id
//                             ,medicines.code
//                             ,medicines.sales_packaging_code
//                             ,medicines.generic_product_detail_devision
//                             ,medicines.popular_name
//                             ,pack_units.jan_code as jan_code
//                             ,medicines.name as medicine_name
//                             ,medicines.standard_unit as standard_unit
//                             ,( CASE WHEN pa.maker_name is null
//                                     THEN
//                                         CASE
//                                             WHEN fm_parent.maker_name is null THEN makers.name
//                                             ELSE fm_parent.maker_name
//                                         END
//                                     ELSE pa.maker_name END
//                             ) as maker_name
//                             ,medicine_prices.price as price
//                             ,pa.comment as comment
//                             ,(
//                                 CASE WHEN fm_own.medicine_id is null
//                                     THEN
//                                         CASE
//                                             WHEN fm_parent.medicine_id is not null THEN ' . Task::STATUS_ADOPTABLE .
//                                             'ELSE pa.status
//                                         END
//                                     ELSE ' . Task::STATUS_DONE . 'END
//                             ) as status
//                             ,(CASE WHEN fm_own is not null THEN fm_own.facility_id ELSE fm_parent.facility_id END) as facility_id
//                             ,(CASE WHEN own_facility.name is null THEN \'\' ELSE own_facility.name END) as facility_name
//                             ,\'\' as user_name
//                             ,pack_units.total_pack_unit
//                             ,pack_units.price as pack_unit_price
//                             ,pack_units.coefficient
//                             ,pa.application_date as application_date
//                             ,(CASE WHEN trader.name is not null THEN trader.name ELSE \'\' END) as trader_name
//                             ,fm_parent.medicine_id as fm_parent
//                             ,fm_own.medicine_id as fm_own
//                             ,(CASE WHEN pa.sales_price is not null THEN pa.sales_price ELSE facility_prices.sales_price END) as sales_price
//                             ,facility_prices.id as fp_id
//                             ,pa.id as price_adoption_id'
//                             ))
//                             ->join('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
//                             ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
//                             ->leftJoin('medicine_prices', function ($query) {
//                                 $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
//                                 ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
//                             })
//                             ->leftJoinSub($medicineOnly, 'pa', function ($join) {
//                                 $join->on('medicines.id', '=', 'pa.medicine_id');
//                             })
//                             ->leftJoinSub($fm_parent, 'fm_parent', function ($join) {
//                                 $join->on('medicines.id', '=', 'fm_parent.medicine_id');
//                             })
//                             ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'fm_parent.id')
//                             ->leftJoin('facilities as trader', 'trader.id', '=', 'facility_prices.trader_id')
//                             ->leftJoinSub($fm_own, 'fm_own', function ($join) {
//                                 $join->on('medicines.id', '=', 'fm_own.medicine_id');
//                             })
//                             ->leftJoin('facilities as own_facility', 'own_facility.id', '=', 'fm_own.facility_id');

//             // 検索条件
//             // 状態
//             if (!empty($request->status)) {
//                 if (in_array(Task::STATUS_ADOPTABLE, $request->status)){
//                     $m_query->whereIn(\DB::raw('coalesce(
//                         (
//                             CASE WHEN fm_own.medicine_id is null
//                                 THEN
//                                     CASE
//                                         WHEN fm_parent.medicine_id is not null THEN ' .  Task::STATUS_ADOPTABLE .
//                                         'ELSE pa.status
//                                     END
//                                 ELSE ' . Task::STATUS_DONE .' END
//                         ),1)'), $request->status);
//                 } else {
//                      $m_query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
//                 }
//             }
//             // 医薬品CD
//             if (!empty($request->code)) {
//                 $m_query->where('medicines.code', 'like', '%'.$request->code.'%');
//             }
//             // 一般名
//             if (!empty($request->popular_name)) {
//                 $m_query->where('medicines.popular_name', 'like', '%'.mb_convert_kana($request->popular_name, 'KVAS').'%');
//             }
//             // 施設
//             if (!empty($request->facility)) {
//                 $m_query->whereIn('fm_own.facility_id', $request->facility);
//             }
//             // 販売中止日
//             if (!empty($request->discontinuation_date)) {
//                 $m_query->where('medicines.discontinuation_date', '>', date('Y-m-d H:i:s'));
//             }
//             // 包装薬価係数
//             if (!empty($request->coefficient)) {
//                 $m_query->where("pack_units.coefficient", \DB::raw($request->coefficient));
//             }
//             // 全文検索
//             if (!empty($request->search)) {
//                 foreach($search_words as $word) {
//                     $m_query->where('medicines.search', 'like', '%'.$word.'%');
//                 }
//             }
//             // 剤型区分
//             if (!empty($request->dosage_type_division)) {
//             	$m_query->where('dosage_type_division', $request->dosage_type_division);
//             }

//             // 先発後発品区分
//             if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//             	$m_query->where('generic_product_detail_devision', $request->generic_product_detail_devision);
//             }

//             $m_query->whereNull("pa.status");
// //-----------------------------------



//         $query = $this->select(\DB::raw('medicines.id as medicine_id
//                               ,medicines.code
//                               ,medicines.sales_packaging_code
//                               ,medicines.generic_product_detail_devision
//                               ,medicines.popular_name
//                               ,pack_units.jan_code as jan_code
//                               ,medicines.name as medicine_name
//                               ,medicines.standard_unit as standard_unit
//                               ,( CASE WHEN pa.maker_name is null THEN makers.name ELSE pa.maker_name END ) as maker_name
//                               ,medicine_prices.price as price
//                               ,pa.comment as comment
//                               ,pa.status as status
//                               ,facilities.id as facility_id
//                               ,facilities.name as facility_name
//                               ,users.name as user_name
//                               ,pack_units.total_pack_unit
//                               ,pack_units.price as pack_unit_price
//                               ,pack_units.coefficient
//                               ,pa.application_date as application_date
//                               ,trader.name as trader_name
//                               ,fm_parent.medicine_id as fm_parent
//                               ,fm_own.medicine_id as fm_own
//                               ,pa.sales_price
//                               ,0 as fp_id
//                               ,pa.id as price_adoption_id'
//                              ))
//                 ->joinSub($pa->getFacility($facility,'facility_id',true), 'pa', function($join){
//                     $join->on('medicines.id', '=', 'pa.medicine_id');
//                 });
// //                if(!$facility->isBunkaren()){
//                     $query->unionAll($m_query);
// //                }
//                 $query->unionAll($p_query)
//                 ->join('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
//                 ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
//                 ->leftJoin('medicine_prices', function ($query) {
//                     $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
//                     ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
//                 })
//                 ->leftJoin('facilities', 'facilities.id', '=', 'pa.facility_id')
//                 ->leftJoin('facilities as trader', 'trader.id', '=', 'pa.trader_id')
//                 ->leftJoinSub($fm_parent, 'fm_parent', function ($join) {
//                     $join->on('medicines.id', '=', 'fm_parent.medicine_id');
//                 })
//                 ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'fm_parent.id')
//                 ->leftJoinSub($fm_own, 'fm_own', function ($join) {
//                     $join->on('medicines.id', '=', 'fm_own.medicine_id');
//                 })
//                 ->leftJoin('users', 'users.id', '=', 'pa.user_id');

//         // 検索条件
//         // 状態
//         if (!empty($request->status)) {
//             $query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
//         }
//         // 施設
//         if (!empty($request->facility)) {
//             $query->whereIn('facilities.id', $request->facility);
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $query->where('medicines.code', 'like', '%'.$request->code.'%');
//         }
//         // 一般名
//         if (!empty($request->popular_name)) {
//             $query->where('popular_name', 'like', '%'.mb_convert_kana($request->popular_name, 'KVAS').'%');
//         }
//         // 販売中止日
//         if (!empty($request->discontinuation_date)) {
//             $query->where('medicines.discontinuation_date', '>', date('Y-m-d H:i:s'));
//         }
//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $query->where("pack_units.coefficient", \DB::raw($request->coefficient));
//         }
//         // 全文検索
//         if (!empty($request->search)) {
//             foreach($search_words as $word) {
//                 $query->where('medicines.search', 'like', '%'.$word.'%');
//             }
//         }
//         // 剤型区分
//         if (!empty($request->dosage_type_division)) {
//         	$query->where('dosage_type_division', $request->dosage_type_division);
//         }
//         // 先発後発品区分
//         if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//                 $query->where('generic_product_detail_devision', $request->generic_product_detail_devision);
//         }

// //echo $query->toSql();
//         $query->orderBy('maker_name', 'asc')->orderBy('medicine_name', 'asc')->orderBy('standard_unit', 'asc');
//         return $query->paginate($count);
//     }



    /**
     * 採用一覧取得SQL
     *
     * @param Request $request リクエスト情報
     * @param Facility $facility 施設情報
     * @param int $count 表示件数
     * @param $user ユーザ情報
     * @return
     */
//     public function listWithApplySql_old_saisin(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	$userGroup = new UserGroup();
//     	$user_group = $userGroup->getUserGroup($user->primary_user_group_id);
//     	$user_group_type = $user_group[0]->group_type;

//     	$sql ="  select ";
//     	$sql .=" base.medicine_id ";
//     	$sql .=" , base.code      ";                           // --医薬品ＣＤ
//     	$sql .=", base.sales_packaging_code   ";               //--ＧＳ1販売
//     	$sql .=", base.generic_product_detail_devision ";
//     	$sql .=", base.popular_name  ";                         //--一般名
//     	$sql .=", pu.jan_code ";                               //--ＪＡＮ
//     	$sql .=", COALESCE(base.pa_maker_name, mk_hanbai.name) as hanbai_maker "; // --販売メーカー 福永 メーカー名整理
//     	$sql .=", mk_seizo.name seizo_maker  ";                //--製造メーカー 福永 メーカー名整理
//     	$sql .=", base.medicine_name   ";                      //--商品名
//     	//     , COALESCE(base.item_name, base.name) AS medicine_name --商品名 福永 新規申請時はmedicineが作成されるので、商品名はmedicineのみを参照でよい
//     	$sql .=", base.standard_unit                   ";      //--規格
//     	//     , COALESCE(base.maker_name, mk.name) as maker_name --メーカー名 福永 メーカー名整理
//     	//     , mp.price --単位薬価 重複のため削除福永
//     	$sql .=", base.comment ";
//     	$sql .=", CASE ";
//     	$sql .="when base.fm_id is not null ";
//     	$sql .="then case ";
//     	$sql .="when base.pa_id is not null ";
//     	$sql .="then base.status ";
//     	$sql .="when base.fp_id is not null ";
//     	$sql .="then 100                       ";//  --採用済み
//     	$sql .="when fp_honbu.id is not null ";
//     	$sql .="then 91                         ";// --採用可--福永
//     	$sql .="when base.is_approval = true ";
//     	$sql .="then 110                        ";// --期限切れ
//     	$sql .="else 1 ";
//     	$sql .="end ";
//     	$sql .="else case ";
//     	//$sql .="when pa_honbu.id is not null ";
//     	//$sql .="then pa_honbu.status ";
//     	$sql .="when fp_honbu.id is not null ";
//     	$sql .="then 91                         ";// --採用可
//     	//$sql .="when fm_honbu.id is not null  ";
//     	//$sql .="and fm_honbu.is_approval = true ";
//     	//$sql .="then 120                         "; // --本部期限切れ
//     	$sql .="else 1 ";
//     	$sql .="END ";
//     	$sql .="end as status ";
//     	$sql .=", base.facility_id ";
//     	$sql .=", base.facility_name  ";                      // --施設名
//     	$sql .=", base.purchase_user_group_id  "; // --福永追加
//     	$sql .=", CASE ";
//     	$sql .="WHEN base.trader_name IS NOT NULL ";
//     	$sql .="THEN base.trader_name ";
//     	$sql .="ELSE fshow.trader_group_name ";
//     	$sql .="END AS trader_name              ";//         --業者名
//     	$sql .=", pu.total_pack_unit              ";//           --総包装単位
//     	//--     , pu.price as pack_unit_price --包装薬価 medicine_priceと連動しているのか？ 薬価はmedicine_priceから取得するべき
//     	$sql .=", pu.coefficient        ";                     //--包装薬価係数
//     	$sql .=", base.application_date ";
//     	$sql .=", f_show_purchase_price2( ";
//     	$sql .=" base.purchase_requested_price ";
//     	$sql .=" ,coalesce(vpry.show_purchase_price, 0)) as purchase_requested_price "; // --要望仕入単価 福永追加
//     	$sql .=" , f_show_purchase_price2( ";
//     	$sql .=" base.purchase_estimated_price ";
//     	$sql .=" ,coalesce(vpry.show_purchase_price, 0)) as purchase_estimated_price ";// --見積仕入単価 福永追加
//     	$sql .=" , f_show_purchase_price2( ";
//     	$sql .=" base.purchase_price ";
//     	$sql .=" ,coalesce(vpry.show_purchase_price, 0)) as show_purchase_price ";// --仕入単価 福永追加
//     	$sql .=" , f_show_sales_price2( ";
//     	$sql .=" CASE ";
//     	$sql .=" when base.fm_id is not null ";
//     	$sql .=" then case ";
//     	$sql .=" when base.pa_id is not null ";
//     	$sql .=" then base.status ";
//     	$sql .=" when base.fp_id is not null ";
//     	$sql .="  then 100 ";
//     	$sql .=" when fp_honbu.id is not null ";
//     	$sql .="  then 91    ";                 // --採用可--福永
//     	$sql .=" when base.is_approval = true ";
//     	$sql .="  then 110 ";
//     	$sql .="  else 1 ";
//     	$sql .=" end ";
//     	$sql .=" else case ";
//     	$sql .=" when pa_honbu.id is not null ";
//     	$sql .=" then pa_honbu.status ";
//     	$sql .=" when fp_honbu.id is not null ";
//     	$sql .=" then 91 ";
//     	//$sql .=" when fm_honbu.id is not null ";
//     	//$sql .=" and fm_honbu.is_approval = true ";
//     	//$sql .=" then 120 ";
//     	$sql .=" else 1 ";
//     	$sql .=" END ";
//     	$sql .=" end ";
//     	$sql .=", coalesce(base.sales_price, fp_honbu.sales_price) ";
//     	$sql .=", coalesce(vpry.show_sales_price_before80, 0) ";
//     	$sql .=", coalesce(vpry.show_sales_price_over80, 0) ";
//     	$sql .=") as sales_price ";
//     	$sql .=" ,base.sales_price ";
//     	$sql .=" ,vpry.show_sales_price_before80 ";
//     	$sql .=" ,vpry.show_sales_price_over80 ";
//     	$sql .=" ,coalesce(base.fp_id, fp_honbu.id) AS fp_id ";
//     	$sql .=" ,base.pa_id AS price_adoption_id ";
//     	$sql .=" ,base.fm_id AS fm_id ";
//     	//								--     , mk.name AS production_maker_name --福永 mk_seizoに変更
//     	$sql .=" , base.dispensing_packaging_code ";
//     	$sql .=" , pu.hot_code                       ";      //   --GS1調剤
//     	$sql .=" , base.dosage_type_division            ";     // --剤型区分
//     	$sql .=" , mp.price AS medicine_price              ";   //--単位薬価
//     	$sql .=" , mp.price * pu.coefficient AS unit_medicine_price "; //--包装薬価
//     	$sql .=" , mp.start_date AS medicine_price_start_date  "; //--薬価有効開始日
//     	$sql .=" , base.facility_price_start_date AS facility_price_start_date ";// --単価有効開始日
//     	$sql .=" , base.created_at ";
//     	$sql .=" , base.updated_at ";
//     	$sql .=" , base.adoption_date ";
//     	$sql .=" , base.adoption_stop_date ";
//     	$sql .=" , base.production_stop_date  ";//                --製造中止日
//     	$sql .=" , base.transitional_deadline   ";//              --経過措置
//     	$sql .=" , base.discontinuation_date     ";//             --販売中止日
//     	$sql .=" , base.discontinuing_division   ";//          --中止理由区分
//     	$sql .=" , vprpab.status_forward ";
//     	$sql .=" , vprpab.action_name_foward ";
//     	$sql .=" , vprpab.status_reject ";
//     	$sql .=" , vprpab.action_name_reject ";
//     	$sql .=" , vprpab.status_remand ";
//     	$sql .=" , vprpab.status_withdraw ";
//     	$sql .=" , base.optional_key1 ";
//     	$sql .=" , base.optional_key2 ";
//     	$sql .=" , base.optional_key3 ";
//     	$sql .=" , base.optional_key4 ";
//     	$sql .=" , base.optional_key5 ";
//     	$sql .=" , base.optional_key6 ";
//     	$sql .=" , base.optional_key7 ";
//     	$sql .=" , base.optional_key8 ";
//     	$sql .=" , fm_honbu.optional_key1 as hq_optional_key1 ";
//     	$sql .=" , fm_honbu.optional_key2 as hq_optional_key2 ";
//     	$sql .=" , fm_honbu.optional_key3 as hq_optional_key3 ";
//     	$sql .=" , fm_honbu.optional_key4 as hq_optional_key4 ";
//     	$sql .=" , fm_honbu.optional_key5 as hq_optional_key5 ";
//     	$sql .=" , fm_honbu.optional_key6 as hq_optional_key6 ";
//     	$sql .=" , fm_honbu.optional_key7 as hq_optional_key7 ";
//     	$sql .=" , fm_honbu.optional_key8 as hq_optional_key8 ";
//     	$sql .=" , fm_honbu.medicine_id as fm_honbu_medicine_id ";
//     	$sql .=" , fp_honbu.medicine_id as fp_honbu_medicine_id ";
//     	$sql .=" , pa_honbu.medicine_id as pa_honbu_medicine_id ";
//     	//								-- ,vprpab.* --検証用
//     	//								-- ,vpry.* --検証用
//     	$sql .=" from ";
//     	$sql .=" ( ";
//     	$sql .=" select ";
//     	$sql .=" base1.* ";
//     	$sql .=" from ";
//     	$sql .=" ( ";
//     	$sql .=" select ";
//     	$sql .=" grand.id as medicine_id ";
//     	$sql .=" , grand.name as medicine_name ";
//     	$sql .=" , grand.standard_unit ";//       --福永 standard_unit_name→standard_unitに変更
//     	$sql .=" , grand.search ";
//     	$sql .=" , grand.sales_packaging_code ";
//     	$sql .=" , grand.popular_name ";
//     	$sql .=" , grand.generic_product_detail_devision ";
//     	$sql .=" , grand.code ";
//     	$sql .=" , grand.maker_id ";
//     	$sql .=" , grand.selling_agency_code ";
//     	$sql .=" , pa.maker_name as pa_maker_name ";
//     	//												--             , grand.name --10行上と重複？
//     	//												--             , (
//     	//														--                 CASE
//     	//														--                     WHEN pa.maker_name IS NOT NULL
//     	//														--                         THEN pa.maker_name
//     	//														--                     ELSE fm.maker_name
//     	//													--                     END
//     	//													--             ) AS maker_name --福永 メーカー名整理
//     	//											--                     , (
//     	//													--                         case
//     	//													--                             when fm.sales_user_group_id IS NOT NULL
//     	//													--                                 THEN fm.sales_user_group_id
//     	//													--                             else pa.sales_user_group_id
//     	//													--                             END
//     	//													--                     ) AS privilege_sales_user_group_id --福永 fm.sales_user_group_idだけで問題ない？
//     	$sql .=" , gr.partner_user_group_id "; //-- as privilege_partner_user_group_id 福永 別名をやめる
//     	$sql .=" , fm.id as fm_id ";
//     	$sql .=" , fm.sales_user_group_id ";
//     	//										--             , case
//     	//												--                 WHEN pa.id IS null
//     	//												--                     THEN fp.purchase_user_group_id
//     	//												--                 else pa.purchase_user_group_id
//     	//												--                 END AS purchase_user_group_id --福永 下に置き換え
//     	$sql .=" , case ";
//     	$sql .=" when fp.id is not null ";
//     	$sql .=" then fp.purchase_user_group_id ";
//     	$sql .=" when pa.id IS not null ";
//     	$sql .=" then pa.purchase_user_group_id ";
//     	$sql .=" else 0 ";
//     	$sql .=" END AS purchase_user_group_id "; // --福永 pa.purchase_user_group_idの初期値を0にしておき、nullは返さないようにする
//     	$sql .=" , fp.start_date as facility_price_start_date ";
//     	$sql .=" , fp.end_date ";
//     	//												--                     , fp.sales_price --福永 重複するので削除
//     	$sql .=" , pa.id as pa_id ";
//     	$sql .=" , pa.deleted_at as pa_del ";
//     	$sql .=" , pa.comment as comment ";
//     	$sql .=" , pa.status ";
//     	$sql .=" , pa.application_date AS application_date ";
//     	$sql .=" , pa.created_at AS created_at ";
//     	$sql .=" , pa.updated_at AS updated_at ";
//     	$sql .=" , fp.id as fp_id ";
//     	$sql .=" , fp.deleted_at as fp_del ";
//     	//												--             , case
//     	//												--                 when pa.medicine_id is null
//     	//												--                     then pa.name
//     	//												--                 else grand.name
//     	//											--                 end as item_name 福永新規申請時にmedicineが作成されるのでpa.medicine_id is nullとなることはない
//     	$sql .=" , pa.purchase_requested_price ";
//     	$sql .=" , purchase_estimated_price ";
//     	$sql .=" , case ";
//     	$sql .=" when pa.id is null ";
//     	$sql .=" then fp.purchase_price ";
//     	$sql .=" else pa.purchase_price ";
//     	$sql .=" end as purchase_price ";
//     	$sql .=" , case ";
//     	$sql .=" when pa.id is null ";
//     	$sql .=" then fp.sales_price ";
//     	$sql .=" else pa.sales_price ";
//     	$sql .=" end as sales_price ";
//     	$sql .=" , gr.partner_user_group_id as honbu_id ";
//     	$sql .=" , case ";
//     	$sql .=" WHEN pa.id IS null ";
//     	$sql .=" THEN fp.trader_group_name ";
//     	$sql .=" else pa.trader_group_name ";
//     	$sql .=" END AS trader_name ";
//     	//										--             , facilities.id as facility_id
//     	//										--             , facilities.name as facility_name --福永 1行に下に変更 施設名はf_show_recordsから取得する
//     	$sql .=" , fm_fsr.user_group_id as facility_id ";
//         $sql .=" , fm_fsr.user_group_name as facility_name ";
//     	$sql .=" , grand.dispensing_packaging_code ";
//     	$sql .=" , grand.dosage_type_division ";
//     	$sql .=" , grand.production_stop_date ";
//     	$sql .=" , grand.transitional_deadline ";
//     	$sql .=" , grand.discontinuation_date ";
//     	$sql .=" , grand.discontinuing_division ";
//     	$sql .=" , fm.adoption_date AS adoption_date ";
//     	$sql .=" , fm.adoption_stop_date AS adoption_stop_date ";
//     	$sql .=" , fm.optional_key1 AS optional_key1 ";
//     	$sql .=" , fm.optional_key2 AS optional_key2 ";
//     	$sql .=" , fm.optional_key3 AS optional_key3 ";
//     	$sql .=" , fm.optional_key4 AS optional_key4 ";
//     	$sql .=" , fm.optional_key5 AS optional_key5 ";
//     	$sql .=" , fm.optional_key6 AS optional_key6 ";
//     	$sql .=" , fm.optional_key7 AS optional_key7 ";
//     	$sql .=" , fm.optional_key8 AS optional_key8 ";
//     	$sql .=" , fm.is_approval ";
//     	$sql .=" from ";
//     	$sql .=" facility_medicines as fm ";
//     	$sql .=" inner join f_show_records_facility(". $user->id . ") as fm_fsr ";
//     	$sql .=" on fm.sales_user_group_id = fm_fsr.user_group_id ";
//     	$sql .=" and fm.deleted_at is null  ";//--福永
//     	$sql .=" left join ( ";
//     	$sql .=" select ";
//     	$sql .=" fp2.* ";
//     	$sql .=" , fsr2.user_group_id ";
//     	$sql .=" , fsr2.user_group_name ";
//     	$sql .=" , fsr2.trader_user_group_id ";
//     	$sql .=" , fsr2.trader_group_name ";
//     	$sql .=" from ";
//     	$sql .=" facility_prices as fp2 ";
//     	$sql .=" inner join f_show_records(". $user->id . ") as fsr2 ";
//     	$sql .=" on fp2.sales_user_group_id = fsr2.user_group_id ";
//     	$sql .=" and fp2.purchase_user_group_id = fsr2.trader_user_group_id ";
//     	$sql .=" and fp2.deleted_at is null ";
//     	$sql .=" ) as fp ";
//     	$sql .=" on fm.medicine_id = fp.medicine_id ";
//     	$sql .=" and fm.sales_user_group_id = fp.sales_user_group_id ";
//     	$sql .=" and fp.start_date <= now() ";
//     	$sql .=" and fp.end_date >= now() ";
//     	$sql .=" left join ( ";
//     	$sql .=" select ";
//     	$sql .=" pa2.* ";
//     	$sql .=" , fsr_pa.trader_group_name ";
//     	$sql .=" from ";
//     	$sql .=" price_adoptions as pa2 ";
//     	$sql .=" inner join f_show_records(". $user->id . ") as fsr_pa ";
//     	$sql .=" on pa2.sales_user_group_id = fsr_pa.user_group_id ";
//     	$sql .=" and pa2.purchase_user_group_id = fsr_pa.trader_user_group_id ";
//     	$sql .=" and pa2.status <> 100 ";
//     	$sql .=" and pa2.deleted_at is null  ";//--福永
//     	$sql .=" ) as pa ";
//     	$sql .=" on fm.medicine_id = pa.medicine_id ";
//     	$sql .=" and fm.sales_user_group_id = pa.sales_user_group_id ";
//     	$sql .=" left join medicines as grand ";
//     	$sql .=" on fm.medicine_id = grand.id ";
//     	$sql .=" and grand.deleted_at is null  ";//--福永
//     	$sql .=" left join group_relations as gr ";
//     	$sql .=" on fm.sales_user_group_id = gr.user_group_id ";
//     	$sql .=" and gr.deleted_at is null ";// --福永
//     	//												--             left join user_groups as facilities
//     	//												--                 on fm.sales_user_group_id = facilities.id --福永 施設名はf_show_recordsから取得する
//     	$sql .=" ) as base1 ";
//     	$sql .=" inner join f_show_records(". $user->id . ") as fsr3 ";
//     	$sql .=" on base1.sales_user_group_id = fsr3.user_group_id ";
//     	$sql .=" and base1.purchase_user_group_id = fsr3.trader_user_group_id ";// --福永 業者が所属施設・所属業者のレコードのみ表示するように
//     	$sql .=" union all ";
//     	$sql .=" select ";
//     	$sql .=" grand_p.id as medicine_id ";
//     	$sql .=" , grand_p.name as medicine_naem ";
//     	$sql .=" , grand_p.standard_unit ";
//     	$sql .=" , grand_p.search ";
//     	$sql .=" , grand_p.sales_packaging_code ";
//     	$sql .=" , grand_p.popular_name ";
//     	$sql .=" , grand_p.generic_product_detail_devision ";
//     	$sql .=" , grand_p.code ";
//     	$sql .=" , grand_p.maker_id ";
//     	$sql .=" , grand_p.selling_agency_code  ";//      --福永追加
//     	$sql .=" , null as pa_maker_name          ";//    --福永
//     	//										--             , grand_p.name --福永10行上と重複？
//     	//										--             , null as maker_name
//     	//										--             , gr_p.user_group_id as privilege_sales_user_group_id
//     	$sql .=" , gr_p.partner_user_group_id ";
//     	$sql .=" , null as fm_id ";
//     	$sql .=" , null as sales_user_group_id ";
//     	$sql .=" , null as purchase_user_group_id ";
//     	$sql .=" , null as facility_price_start_date ";
//     	$sql .=" , null as end_date ";
//     	//										--             , null as sales_price
//     	$sql .=" , null as pa_id ";
//     	$sql .=" , null as pa_del ";
//     	$sql .=" , null as comment ";
//     	$sql .=" , null as status ";
//     	$sql .=" , null AS application_date ";
//     	$sql .=" , null AS created_at ";
//     	$sql .=" , null AS updated_at ";
//     	$sql .=" , null as fp_id ";
//     	$sql .=" , null as fp_del ";
//     	//										--             , null as item_name
//     	$sql .=" , null as purchase_requested_price "; //--福永追加
//     	$sql .=" , null as purchase_estimated_price "; //--福永追加
//     	$sql .=" , null as purchase_price ";
//     	$sql .=" , null as sales_price ";
//     	$sql .=" , null as honbu_id ";
//     	$sql .=" , null as trader_name ";
//     	$sql .=" , null as facility_id ";
//     	$sql .=" , null as facility_name ";
//     	$sql .=" , grand_p.dispensing_packaging_code ";
//     	$sql .=" , grand_p.dosage_type_division ";
//     	$sql .=" , grand_p.production_stop_date ";
//     	$sql .=" , grand_p.transitional_deadline ";
//     	$sql .=" , grand_p.discontinuation_date ";
//     	$sql .=" , grand_p.discontinuing_division ";
//     	$sql .=" , null AS adoption_date ";
//     	$sql .=" , null AS adoption_stop_date ";
//     	$sql .=" , null AS optional_key1 ";
//     	$sql .=" , null AS optional_key2 ";
//     	$sql .=" , null AS optional_key3 ";
//     	$sql .=" , null AS optional_key4 ";
//     	$sql .=" , null AS optional_key5 ";
//     	$sql .=" , null AS optional_key6 ";
//     	$sql .=" , null AS optional_key7 ";
//     	$sql .=" , null AS optional_key8 ";
//     	$sql .=" , null as is_approval ";
//     	$sql .=" from ";
//     	$sql .=" medicines as grand_p ";
//     	$sql .=" left join users as usr_p ";
//     	$sql .=" on usr_p.id =". $user->id;
//     	$sql .=" left join group_relations as gr_p ";
//     	$sql .=" on gr_p.user_group_id = usr_p.primary_user_group_id ";
//     	$sql .=" and gr_p.deleted_at is null "; //--福永
//     	$sql .=" left outer join facility_medicines as fm_p ";
//     	$sql .=" on grand_p.id = fm_p.medicine_id ";
//     	$sql .=" and fm_p.sales_user_group_id = gr_p.user_group_id ";
//     	$sql .=" and fm_p.deleted_at is null ";
//     	$sql .=" where ";
//     	$sql .=" grand_p.deleted_at is null ";
//     	$sql .=" and fm_p.medicine_id is null ";
//     	$sql .=" ) as base ";
//     	$sql .=" left join facility_medicines as fm_honbu ";
//     	$sql .=" on base.medicine_id = fm_honbu.medicine_id ";
//     	$sql .=" and base.partner_user_group_id = fm_honbu.sales_user_group_id ";
//     	$sql .=" and fm_honbu.deleted_at is null ";// --福永
//     	$sql .=" left join facility_prices as fp_honbu ";
//     	$sql .=" on base.medicine_id = fp_honbu.medicine_id ";
//     	$sql .=" and base.partner_user_group_id = fp_honbu.sales_user_group_id ";
//     	$sql .=" and fp_honbu.start_date <= now() ";
//     	$sql .=" and fp_honbu.end_date >= now() ";
//     	$sql .=" and fp_honbu.deleted_at is null ";// --福永
//     	$sql .=" left join f_show_records(". $user->id . ") as fshow ";
//     	$sql .=" on fp_honbu.sales_user_group_id = fshow.user_group_id ";
//     	$sql .=" and fp_honbu.purchase_user_group_id = fshow.trader_user_group_id ";
//     	$sql .=" left join price_adoptions as pa_honbu ";
//     	$sql .=" on base.medicine_id = pa_honbu.medicine_id ";
//     	$sql .=" and base.partner_user_group_id = pa_honbu.sales_user_group_id ";
//     	$sql .=" and pa_honbu.status <> 100 ";
//     	$sql .=" and pa_honbu.deleted_at is null ";// --福永
//     	$sql .=" left join pack_units as pu ";
//     	$sql .=" on base.medicine_id = pu.medicine_id ";
//     	$sql .=" LEFT JOIN medicine_prices AS mp ";
//     	$sql .=" ON base.medicine_id = mp.medicine_id ";
//     	$sql .=" and mp.end_date >= NOW() ";
//     	$sql .=" and mp.start_date <= NOW() ";
//     	$sql .=" and mp.deleted_at is NULL ";
//     	$sql .=" left join makers as mk_seizo   ";//              --福永 メーカー名の整理
//     	$sql .=" on base.maker_id = mk_seizo.id ";
//     	$sql .=" and mk_seizo.deleted_at is null "; //--福永
//     	$sql .=" left join makers as mk_hanbai "; //               --福永 メーカー名の整理
//     	$sql .=" on base.selling_agency_code = mk_hanbai.id ";
//     	$sql .=" and mk_hanbai.deleted_at is null  "; //--福永
//     	$sql .=" LEFT JOIN users AS usr ";
//     	$sql .=" ON usr.id = ". $user->id ;
//     	$sql .=" left join v_privileges_row_price_adoptions_bystate2 as vprpab ";
//     	//										on --vprpab.user_group_id = usr.primary_user_group_id --福永 結合を下記に修正
//     	if($user_group_type == '業者') {
//     		$sql .=" on vprpab.user_group_id = usr.primary_user_group_id ";
//     	} else {
//     	    $sql .=" on vprpab.user_group_id = coalesce(base.sales_user_group_id,usr.primary_user_group_id) ";
//     	}
//     	$sql .=" and vprpab.user_id = ". $user->id;
//     	$sql .=" and vprpab.status = ( ";
//     	$sql .=" CASE ";
//     	$sql .=" when base.fm_id is not null ";
//     	$sql .=" then case ";
//     	$sql .=" when base.pa_id is not null ";
//     	$sql .=" then base.status ";
//     	$sql .=" when base.fp_id is not null ";
//     	$sql .=" then 100 ";
//     	$sql .=" when fp_honbu.id is not null ";
//     	$sql .=" then 91    ";                      //--採用可--福永
//     	$sql .=" when base.is_approval = true ";
//     	$sql .=" then 110 ";
//     	$sql .=" else 1 ";
//     	$sql .=" end ";
//     	$sql .=" else case ";
//     	//$sql .=" when pa_honbu.id is not null ";
//     	//$sql .=" then pa_honbu.status ";
//     	$sql .=" when fp_honbu.id is not null ";
//     	$sql .=" then 91 ";
//     	//$sql .=" when fm_honbu.id is not null ";
//     	//$sql .=" and fm_honbu.is_approval = true ";
//     	//$sql .=" then 120 ";
//     	$sql .=" else 1 ";
//     	$sql .=" END ";
//     	$sql .=" end ";
//     	$sql .=" ) ";
//     	$sql .=" left join v_privileges_row_yakuhinshinsei2 as vpry ";
//     	//												on --vpry.user_group_id = usr.primary_user_group_id --福永 結合を下記に修正
//     	if($user_group_type == '業者') {
//     		$sql .=" on vpry.user_group_id = usr.primary_user_group_id ";
//     	} else {
//     	    $sql .=" on vpry.user_group_id = coalesce(base.sales_user_group_id,usr.primary_user_group_id) ";
//     	}
//     	$sql .=" and vpry.user_id =". $user->id ;
//     	$sql .=" where ";
//     	$sql .=" 1 = 1 ";

//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(100, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 110;
//     			$req_status[] = 120;
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $req_status).")";

//     		} else {
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $request->status).")";
//     			$req_status = $request->status;

//     		}

//     		$sql .= "and ";
//     		$sql .="           ( CASE ";
//     		$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     		$sql .="                 case ";
//     		$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     		$sql .="                 when base.fp_id is not null then 100 ";         //--採用済み
//     		$sql .="                 when fp_honbu.id is not null ";
//     		$sql .="                 then 91    ";                 // --採用可--福永
//     		$sql .="                 when base.is_approval = true ";
//     		$sql .="                 then 110 ";
//     		$sql .="                 else 1 ";
//     		$sql .="                 end ";
//     		$sql .="               else  ";                                   //--hospital行がない
//     		$sql .="                 case ";
//     		$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     		$sql .="                 when fp_honbu.id is not null then 91  ";       //--採用可
//     		$sql .="                 else 1      ";                                 //--未採用
//     		$sql .="               END ";
//     		$sql .="            end ) in (".implode(',', $req_status).")";

//     	}

//     	//採用中止日 TODO
//     	//if (!empty($request->stop_date)) {
//     	//	 $sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//     	//}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and coalesce(base.facility_id, base.sales_user_group_id) in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and base.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and base.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pu.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and base.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and base.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	// TODO
// //     	 //項目1
// //     	 if (!empty($request->optional_key1)) {
// //     	 $sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

// //     	 }

// //     	 //項目2
// //     	 if (!empty($request->optional_key2)) {
// //     	 $sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

// //     	 }

// //     	 //項目3
// //     	 if (!empty($request->optional_key3)) {
// //     	 $sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

// //     	 }

// //     	 //項目4
// //     	 if (!empty($request->optional_key4)) {
// //     	 $sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

// //     	 }

// //     	 //項目5
// //     	 if (!empty($request->optional_key5)) {
// //     	 $sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

// //     	 }

// //     	 //項目6
// //     	 if (!empty($request->optional_key6)) {
// //     	 $sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

// //     	 }

// //     	 //項目7
// //     	 if (!empty($request->optional_key7)) {
// //     	 $sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

// //     	 }

// //     	 //項目8
// //     	 if (!empty($request->optional_key8)) {
// //     	 $sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

// //     	 }


//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//     	}

//     	return $sql;

//     }

    /**
     * 採用一覧取得SQL
     *
     * @param Request $request リクエスト情報
     * @param Facility $facility 施設情報
     * @param int $count 表示件数
     * @param $user ユーザ情報
     * @return
     */
//     public function listWithApplySql_old(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	$sql =" select ";
//     	$sql .="        base.medicine_id ";
//     	$sql .="       ,base.code ";
//     	$sql .="       ,base.sales_packaging_code ";
//     	$sql .="       ,base.generic_product_detail_devision ";
//     	$sql .="       ,base.popular_name ";
//     	$sql .="       ,pu.jan_code ";
//     	$sql .="       ,COALESCE(base.item_name, base.name) AS medicine_name ";
//     	$sql .="       ,base.standard_unit ";
//     	//$sql .="       --,base.maker_id ";
//     	$sql .="       ,COALESCE(base.maker_name, mk.name) as maker_name ";
//     	$sql .="       ,mp.price ";
//     	$sql .="       ,base.comment ";
//     	$sql .="       , CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="       case ";
//     	$sql .="       when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 100  ";        //--採用済み
//     	$sql .="       when base.is_approval = true then 110  ";                                  //  --期限切れ
//     	$sql .="       else 1  ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                 // --hospital行がない
//     	$sql .="       case ";
//     	$sql .="       when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 91  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 120  ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="       END ";
//     	$sql .="       end as status  ";
//     	$sql .="       ,base.facility_id ";
//     	$sql .="       ,base.facility_name ";

//     	$sql .="       ,pu.total_pack_unit ";
//     	$sql .="       ,pu.price as pack_unit_price ";
//     	$sql .="       ,pu.coefficient ";
//     	$sql .="       ,base.application_date ";
//     	//$sql .="       ,base.trader_name ";
//     	$sql .="       ,CASE WHEN base.trader_name IS NOT NULL THEN base.trader_name ";
//     	$sql .="        ELSE fshow.trader_group_name ";
//     	$sql .="        END AS trader_name ";
//     	// TODO ここの権限部分を修正
//     	$sql .="       ,f_show_sales_price2( ";
//     	$sql .="        CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="        case ";
//     	$sql .="        when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 100 ";         //--採用済み
//     	$sql .="       when base.is_approval = true then 110 ";        //--期限切れ
//     	$sql .="       else 1 ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                  //--hospital行がない
//     	$sql .="       case ";
//     	$sql .="        when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 91  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 120  ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="        END ";
//     	$sql .="        end  ";
//     	$sql .="       ,coalesce(base.sales_price,fp_honbu.sales_price) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_before80,0) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_over80,0) ";
//     	$sql .="       ) as sales_price ";
//     	//$sql .="       ,base.fp_id ";
//     	$sql .="       ,coalesce(base.fp_id,fp_honbu.id) AS fp_id ";
//     	$sql .="       ,base.pa_id AS price_adoption_id ";

//     	// 20200818詳細画面の影響で追加したので他の箇所にも追加 TODO
//     	$sql .="       ,base.fm_id AS fm_id ";

//     	//$sql .="       --csv ";
//     	$sql .="       ,mk.name AS production_maker_name ";
//     	$sql .="       ,base.dispensing_packaging_code ";
//     	$sql .="       ,pu.hot_code ";
//     	$sql .="       ,base.dosage_type_division ";
//     	$sql .="       ,mp.price AS medicine_price ";
//     	$sql .="       ,mp.price * pu.coefficient AS unit_medicine_price ";
//     	$sql .="       ,mp.start_date AS medicine_price_start_date ";
//     	$sql .="       ,base.facility_price_start_date AS facility_price_start_date ";
//     	$sql .="       ,base.created_at ";
//     	$sql .="       ,base.updated_at ";
//     	$sql .="       ,base.adoption_date ";
//     	$sql .="       ,base.adoption_stop_date ";
//     	$sql .="       ,base.production_stop_date ";
//     	$sql .="       ,base.transitional_deadline ";
//     	$sql .="       ,base.discontinuation_date ";
//     	$sql .="       ,base.discontinuing_division ";
//     	$sql .="       ,vprpab.status_forward ";
//     	//$sql .="       ,vprpab.status_back ";
//     	//$sql .="       ,vprpab.status_back_text ";
//     	$sql .="       ,vprpab.action_name_foward ";
//     	$sql .="       ,vprpab.status_reject ";
//     	$sql .="       ,vprpab.action_name_reject ";
//     	$sql .="       ,vprpab.status_remand ";
//     	$sql .="       ,vprpab.status_withdraw ";

//     	//--追加
//     	$sql .="       ,base.optional_key1 ";
//     	$sql .="       ,base.optional_key2 ";
//     	$sql .="       ,base.optional_key3 ";
//     	$sql .="       ,base.optional_key4 ";
//     	$sql .="       ,base.optional_key5 ";
//     	$sql .="       ,base.optional_key6 ";
//     	$sql .="       ,base.optional_key7 ";
//     	$sql .="       ,base.optional_key8 ";
//     	$sql .="       ,fm_honbu.optional_key1 as hq_optional_key1 ";
//     	$sql .="       ,fm_honbu.optional_key2 as hq_optional_key2 ";
//     	$sql .="       ,fm_honbu.optional_key3 as hq_optional_key3 ";
//     	$sql .="       ,fm_honbu.optional_key4 as hq_optional_key4 ";
//     	$sql .="       ,fm_honbu.optional_key5 as hq_optional_key5 ";
//     	$sql .="       ,fm_honbu.optional_key6 as hq_optional_key6 ";
//     	$sql .="       ,fm_honbu.optional_key7 as hq_optional_key7 ";
//     	$sql .="       ,fm_honbu.optional_key8 as hq_optional_key8 ";
//     	$sql .="       , fm_honbu.medicine_id as fm_honbu_medicine_id ";
//     	$sql .="       , fp_honbu.medicine_id as fp_honbu_medicine_id ";
//     	$sql .="       , pa_honbu.medicine_id as pa_honbu_medicine_id  ";

//     	$sql .="        from ";
//     	$sql .="        ( ";
//     	//--基本部分(base)は【medicine】→【採用マスター】→【価格マスター】
//     	//--　　　　　　　　　　　　　　　　　　　　　　　　→【有効な申請データ】
//     	//--の繋ぎこみまで。採用マスターのないものは権限判定用ユーザーグループ列に実行ユーザーのprimary_user_group_id、primary_partner_user_group_idを返す
//     	//--商品情報、採用施設情報に対するWhere句はこのブロックに記述
//     	//--本部の繋ぎこみ、権限判定、状態判定は外側で行う(必然的に本部列に対する検索条件は外側に書かれる)
//     	$sql .="             select "; // --所属組織に制限された採用マスター
//     	$sql .="             grand.id as medicine_id ";
//     	$sql .="             , grand.name as medicine_name ";
//     	$sql .="             , grand.standard_unit_name as standard_unit ";
//     	$sql .="             , grand.search ";
//     	$sql .="             , grand.sales_packaging_code ";
//     	$sql .="             , grand.popular_name ";
//     	$sql .="             , grand.generic_product_detail_devision ";
//     	$sql .="             , grand.code ";
//     	$sql .="             , grand.maker_id ";
//     	$sql .="             , grand.name ";

//     	$sql .="             ,(CASE ";
//     	$sql .="                 WHEN pa.maker_name IS NOT NULL THEN pa.maker_name ";
//     	$sql .="                 ELSE fm.maker_name ";
//     	$sql .="                 END) AS maker_name ";
//     	//$sql .="             , fm.maker_name as maker_name ";
//     	//--, fm.sales_user_group_id as privilege_sales_user_group_id
//     	$sql .="             ,(case ";
//     	$sql .="                 when fm.sales_user_group_id IS NOT NULL THEN fm.sales_user_group_id ";
//     	$sql .="                 else pa.sales_user_group_id ";
//     	$sql .="              END) AS  privilege_sales_user_group_id ";
//     	$sql .="            , gr.partner_user_group_id as privilege_partner_user_group_id ";
//     	$sql .="            , fm.id as fm_id ";
//     	$sql .="            , fm.sales_user_group_id ";
//     	//--, fp.user_group_id
//     	//--, fp.user_group_name
//     	//--, fp.trader_user_group_id
//     	//--, fp.trader_group_name
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.purchase_user_group_id ";
//     	$sql .="            else pa.purchase_user_group_id ";
//     	$sql .="            END AS purchase_user_group_id ";
//     	$sql .="            , fp.start_date as facility_price_start_date ";
//     	$sql .="            , fp.end_date ";
//     	$sql .="            , fp.sales_price  ";                   //--商品情報
//     	$sql .="            , pa.id as pa_id ";
//     	$sql .="            , pa.deleted_at as pa_del ";
//     	$sql .="            , pa.comment as comment ";
//     	$sql .="            , pa.status ";
//     	$sql .="            , pa.application_date AS application_date ";
//     	$sql .="            , pa.created_at AS created_at ";
//     	$sql .="            , pa.updated_at AS updated_at ";
//     	$sql .="            , fp.id as fp_id ";
//     	$sql .="            , fp.deleted_at as fp_del ";
//     	$sql .="            , case ";
//     	$sql .="            when pa.medicine_id is null ";
//     	$sql .="            then pa.name ";
//     	$sql .="            else grand.name ";
//     	$sql .="            end as item_name "; // --商品名
//     	//--規格
//     	//--JAN
//     	//--単価情報
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.purchase_price ";
//     	$sql .="            else pa.purchase_price ";
//     	$sql .="            end as pur_price  "; //--仕入価格
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.sales_price ";
//     	$sql .="            else pa.sales_price ";
//     	$sql .="            end as sa_price  "; //--売上価格
//     	//--本部情報
//     	$sql .="            , gr.partner_user_group_id as honbu_id ";
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.trader_group_name ";
//     	$sql .="            else pa.trader_group_name ";
//     	$sql .="            END AS trader_name ";

//     	$sql .="            , facilities.id as facility_id ";
//     	$sql .="            , facilities.name as facility_name ";
//     	$sql .="            , grand.dispensing_packaging_code ";
//     	$sql .="            , grand.dosage_type_division ";
//     	$sql .="            , grand.production_stop_date ";
//     	$sql .="            , grand.transitional_deadline ";
//     	$sql .="            , grand.discontinuation_date ";
//     	$sql .="            , grand.discontinuing_division ";
//     	$sql .="            , fm.adoption_date AS adoption_date ";
//     	$sql .="            , fm.adoption_stop_date AS adoption_stop_date ";
//     	$sql .="            , fm.optional_key1 AS optional_key1 ";
//     	$sql .="            , fm.optional_key2 AS optional_key2 ";
//     	$sql .="            , fm.optional_key3 AS optional_key3 ";
//     	$sql .="            , fm.optional_key4 AS optional_key4 ";
//     	$sql .="            , fm.optional_key5 AS optional_key5 ";
//     	$sql .="            , fm.optional_key6 AS optional_key6 ";
//     	$sql .="            , fm.optional_key7 AS optional_key7 ";
//     	$sql .="            , fm.optional_key8 AS optional_key8 ";
//     	$sql .="            , fm.is_approval ";
//     	$sql .="            from ";
//     	$sql .="            facility_medicines as fm ";
//     	$sql .="            inner join f_show_records_facility(". $user->id . ") as fm_fsr ";
//     	$sql .="            on fm.sales_user_group_id = fm_fsr.user_group_id  ";
//     	//--所属組織に制限された価格マスター
//     	$sql .="            left join ( ";
//     	$sql .="                select ";
//     	$sql .="                fp2.* ";
//     	$sql .="                , fsr2.user_group_id ";
//     	$sql .="                , fsr2.user_group_name ";
//     	$sql .="                , fsr2.trader_user_group_id ";
//     	$sql .="                , fsr2.trader_group_name ";
//     	$sql .="                from ";
//     	$sql .="                facility_prices as fp2 ";
//     	$sql .="                inner join f_show_records(". $user->id . ") as fsr2 ";
//     	$sql .="                on fp2.sales_user_group_id = fsr2.user_group_id ";
//     	$sql .="                and fp2.purchase_user_group_id = fsr2.trader_user_group_id ";
//     	$sql .="            ) as fp ";
//     	$sql .="            on fm.medicine_id = fp.medicine_id ";
//     	$sql .="            and fm.sales_user_group_id = fp.sales_user_group_id ";
//     	$sql .="            and fp.start_date <= now() ";
//     	$sql .="            and fp.end_date >= now()  ";
//     	//--所属組織に制限された有効な申請データ
//     	$sql .="            left join ( ";
//     	$sql .="                 select ";
//     	$sql .="                 pa2.* ";
//     	$sql .="                 , fsr_pa.trader_group_name ";
//     	$sql .="                 from ";
//     	$sql .="                 price_adoptions as pa2 ";
//     	$sql .="                 inner join f_show_records(". $user->id . ") as fsr_pa ";
//     	$sql .="                 on pa2.sales_user_group_id = fsr_pa.user_group_id ";
//     	$sql .="                 and pa2.purchase_user_group_id = fsr_pa.trader_user_group_id ";
//     	$sql .="                 and pa2.status <> 100 ";
//     	$sql .="             ) as pa ";
//     	$sql .="             on fm.medicine_id = pa.medicine_id ";
//     	$sql .="             and fm.sales_user_group_id = pa.sales_user_group_id ";
//     	$sql .="             left join medicines as grand ";
//     	$sql .="             on fm.medicine_id = grand.id ";
//     	$sql .="             left join group_relations as gr ";
//     	$sql .="             on fm.sales_user_group_id = gr.user_group_id  ";

//     	$sql .="             left join user_groups as facilities ";
//     	$sql .="             on fm.sales_user_group_id=facilities.id ";
//     	$sql .="             union all ";
//     	$sql .="             select ";
//     	$sql .="             grand_p.id as medicine_id ";
//     	$sql .="             , grand_p.name as medicine_naem ";
//     	$sql .="             , grand_p.standard_unit_name as standard_unit ";
//     	$sql .="             , grand_p.search ";
//     	$sql .="             , grand_p.sales_packaging_code ";
//     	$sql .="             , grand_p.popular_name ";
//     	$sql .="             , grand_p.generic_product_detail_devision ";
//     	$sql .="             , grand_p.code ";
//     	$sql .="             , grand_p.maker_id ";
//     	$sql .="             , grand_p.name ";
//     	$sql .="             , null as maker_name ";
//     	$sql .="             , gr_p.user_group_id as privilege_sales_user_group_id ";
//     	$sql .="             , gr_p.partner_user_group_id as privilege_partner_user_group_id ";
//     	//$sql .="             , 3 as privilege_sales_user_group_id ";
//     	//$sql .="             , 2 as privilege_partner_user_group_id ";
//     	$sql .="             , null as fm_id ";
//     	$sql .="             , null as sales_user_group_id ";

//     	$sql .="             , null as purchase_user_group_id ";
//     	$sql .="             , null as facility_price_start_date ";
//     	$sql .="             , null as end_date ";
//     	$sql .="             , null as sales_price  ";              //--商品情報
//     	$sql .="             , null as pa_id ";
//     	$sql .="             , null as pa_del ";
//     	$sql .="             , null as comment ";
//     	$sql .="             , null as status ";
//     	$sql .="             , null AS application_date ";
//     	$sql .="             , null AS created_at ";
//     	$sql .="             , null AS updated_at ";
//     	$sql .="             , null as fp_id ";
//     	$sql .="             , null as fp_del ";
//     	$sql .="             , null as item_name "; //-- 商品名
//     	$sql .="             , null as pur_price "; //-- 仕入価格
//     	$sql .="             , null as sa_price ";  //-- 売上価格
//     	$sql .="             , null as honbu_id ";
//     	$sql .="             , null as trader_name ";
//     	$sql .="             , null as facility_id ";
//     	$sql .="             , null as facility_name ";
//     	$sql .="             , grand_p.dispensing_packaging_code ";
//     	$sql .="             , grand_p.dosage_type_division ";
//     	$sql .="             , grand_p.production_stop_date ";
//     	$sql .="             , grand_p.transitional_deadline ";
//     	$sql .="             , grand_p.discontinuation_date ";
//     	$sql .="             , grand_p.discontinuing_division ";
//     	$sql .="             , null AS adoption_date ";
//     	$sql .="             , null AS adoption_stop_date ";
//     	$sql .="             , null AS optional_key1 ";
//     	$sql .="             , null AS optional_key2 ";
//     	$sql .="             , null AS optional_key3 ";
//     	$sql .="             , null AS optional_key4 ";
//     	$sql .="             , null AS optional_key5 ";
//     	$sql .="             , null AS optional_key6 ";
//     	$sql .="             , null AS optional_key7 ";
//     	$sql .="             , null AS optional_key8 ";
//     	$sql .="             , null as is_approval ";
//     	$sql .="             from ";
//     	$sql .="             medicines as grand_p ";
//     	$sql .="             left join users as usr_p";
//     	$sql .="              on usr_p.id = ". $user->id;
//     	$sql .="             left join group_relations as gr_p";
//     	$sql .="              on gr_p.user_group_id = usr_p.primary_user_group_id";
//     	$sql .="             left outer join facility_medicines as fm_p";
//     	$sql .="              on grand_p.id = fm_p.medicine_id";
//     	$sql .="              and fm_p.sales_user_group_id = gr_p.user_group_id";
//     	//$sql .="             left outer join facility_medicines as fm_p";
//     	//$sql .="              on grand_p.id = fm_p.medicine_id";
//     	//$sql .="              and fm_p.sales_user_group_id = 3";
//     	$sql .="             where";
//     	$sql .="               grand_p.deleted_at is null";
//     	$sql .="               and fm_p.medicine_id is null";
//     	$sql .="        ) as base ";
//     	//--本部：採用マスター
//     	$sql .="        left join facility_medicines as fm_honbu ";
//     	$sql .="          on base.medicine_id = fm_honbu.medicine_id ";
//     	$sql .="          and base.privilege_partner_user_group_id = fm_honbu.sales_user_group_id ";
//     	//--本部：価格マスター
//     	$sql .="        left join facility_prices as fp_honbu ";
//     	$sql .="         on base.medicine_id = fp_honbu.medicine_id ";
//     	$sql .="         and base.privilege_partner_user_group_id = fp_honbu.sales_user_group_id ";
//     	$sql .="         and fp_honbu.start_date <= now() ";
//     	$sql .="        and fp_honbu.end_date >= now() ";
//     	$sql .="        left join f_show_records(". $user->id . ") as fshow";
//     	$sql .="        on fp_honbu.sales_user_group_id = fshow.user_group_id";
//     	$sql .="        and fp_honbu.purchase_user_group_id = fshow.trader_user_group_id";
//     	//--本部：有効な申請データ
//     	$sql .="        left join price_adoptions as pa_honbu ";
//     	$sql .="        on base.medicine_id = pa_honbu.medicine_id ";
//     	$sql .="        and base.privilege_partner_user_group_id = pa_honbu.sales_user_group_id ";
//     	$sql .="        and pa_honbu.status <> 100 ";

//     	//-- add2
//     	$sql .="        left join pack_units as pu ";
//     	$sql .="          on base.medicine_id =pu.medicine_id ";
//     	$sql .="        LEFT JOIN medicine_prices AS mp ";
//     	$sql .="          ON base.medicine_id = mp.medicine_id ";
//     	$sql .="          and mp.end_date >= NOW() ";
//     	$sql .="          and mp.start_date <= NOW() ";
//     	$sql .="          and mp.deleted_at is NULL ";
//     	$sql .="        left join makers as mk ";
//     	$sql .="          on base.maker_id =  mk.id ";
//     	$sql .="        LEFT JOIN users AS usr ";
//     	$sql .="          ON usr.id = ". $user->id;
//     	$sql .="        left join v_privileges_row_price_adoptions_bystate2 as vprpab ";
//     	$sql .="          on vprpab.user_group_id = usr.primary_user_group_id ";
//     	$sql .="          and vprpab.user_id = ". $user->id;
//     	$sql .="          and vprpab.status = ";
//     	$sql .="           ( CASE ";
//     	$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     	$sql .="                 case ";
//     	$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="                 when base.fp_id is not null then 100 ";         //--採用済み
//     	$sql .="                 when base.is_approval = true then 110  ";                                   // --期限切れ
//     	$sql .="                 else 1  ";
//     	$sql .="                 end ";
//     	$sql .="               else  ";                                   //--hospital行がない
//     	$sql .="                 case ";
//     	$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="                 when fp_honbu.id is not null then 91  ";       //--採用可 12
//     	$sql .="                 when fm_honbu.id is not null and fm_honbu.is_approval = true then 120  ";       //--本部期限切れ 13
//     	$sql .="                 else 1      ";                                 //--未採用
//     	$sql .="               END ";
//     	$sql .="            end ) ";
//     	$sql .=" left join v_privileges_row_yakuhinshinsei2 as vpry ";
//     	$sql .=" on vpry.user_group_id = usr.primary_user_group_id ";
//     	$sql .=" and vpry.user_id = ". $user->id;
//     	$sql .=" where 1 = 1 ";


//     	//			--WHERE

//     	//			--base.search like '%14987114336009%'
//     	//					--base.purchase_user_group_id = 36

//     	//					--limit 100
//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(100, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 110;
//     			$req_status[] = 120;
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $req_status).")";

//     		} else {
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $request->status).")";
//     			$req_status = $request->status;

//     		}
//     		$sql .= "and ";
//     		$sql .="           ( CASE ";
//     		$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     		$sql .="                 case ";
//     		$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     		$sql .="                 when base.fp_id is not null then 100 ";         //--採用済み
//     		$sql .="                 else 110  ";                                   // --期限切れ
//     		$sql .="                 end ";
//     		$sql .="               else  ";                                   //--hospital行がない
//     		$sql .="                 case ";
//     		$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     		$sql .="                 when fp_honbu.id is not null then 91  ";       //--採用可
//     		$sql .="                 when fm_honbu.id is not null then 120  ";       //--本部期限切れ
//     		$sql .="                 else 1      ";                                 //--未採用
//     		$sql .="               END ";
//     		$sql .="            end ) in (".implode(',', $req_status).")";

//     	}

//     	//採用中止日 TODO
//     	//if (!empty($request->stop_date)) {
//     	//	 $sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//     	//}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and coalesce(base.facility_id, base.sales_user_group_id) in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and base.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and base.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pu.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and base.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and base.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	// TODO
// //     	 //項目1
// //     	 if (!empty($request->optional_key1)) {
// //     	 $sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

// //     	 }

// //     	 //項目2
// //     	 if (!empty($request->optional_key2)) {
// //     	 $sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

// //     	 }

// //     	 //項目3
// //     	 if (!empty($request->optional_key3)) {
// //     	 $sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

// //     	 }

// //     	 //項目4
// //     	 if (!empty($request->optional_key4)) {
// //     	 $sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

// //     	 }

// //     	 //項目5
// //     	 if (!empty($request->optional_key5)) {
// //     	 $sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

// //     	 }

// //     	 //項目6
// //     	 if (!empty($request->optional_key6)) {
// //     	 $sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

// //     	 }

// //     	 //項目7
// //     	 if (!empty($request->optional_key7)) {
// //     	 $sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

// //     	 }

// //     	 //項目8
// //     	 if (!empty($request->optional_key8)) {
// //     	 $sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

// //     	 }


//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//     	}

//     	return $sql;

//     }

    /**
    * 採用一覧取得SQL
    *
    * @param Request $request リクエスト情報
    * @param Facility $facility 施設情報
    * @param int $count 表示件数
    * @param $user ユーザ情報
    * @return
     */
//     public function listWithApplySql_kengentorikomimae(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	$sql =" select ";
//     	$sql .="       	base.medicine_id ";
//     	$sql .="       ,base.code ";
//     	$sql .="       ,base.sales_packaging_code ";
//     	$sql .="       ,base.generic_product_detail_devision ";
//     	$sql .="       ,base.popular_name ";
//     	$sql .="       ,pu.jan_code ";
//     	$sql .="       ,COALESCE(base.item_name, base.name) AS medicine_name ";
//     	$sql .="        ,base.standard_unit ";
//     	//$sql .="       --,base.maker_id ";
//     	$sql .="       ,COALESCE(base.maker_name, mk.name) as maker_name ";
//     	$sql .="       ,mp.price ";
//     	$sql .="       ,base.comment ";
//     	$sql .="       , CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="       case ";
//     	$sql .="       when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 10  ";        //--採用済み
//     	$sql .="       when base.is_approval = true then 11  ";                                  //  --期限切れ
//     	$sql .="       else 1  ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                 // --hospital行がない
//     	$sql .="       case ";
//     	$sql .="       when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 11  ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="       END ";
//     	$sql .="       end as status  ";
//     	$sql .="       ,base.facility_id ";
//     	$sql .="       ,base.facility_name ";

//     	$sql .="       ,pu.total_pack_unit ";
//     	$sql .="       ,pu.price as pack_unit_price ";
//     	$sql .="       ,pu.coefficient ";
//     	$sql .="       ,base.application_date ";
//     	//$sql .="       ,base.trader_name ";
//     	$sql .="       ,CASE WHEN base.trader_name IS NOT NULL THEN base.trader_name ";
//     	$sql .="        ELSE fshow.trader_group_name ";
//     	$sql .="        END AS trader_name ";
//     	$sql .="       ,f_show_sales_price( ";
//     	$sql .="        CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="        case ";
//     	$sql .="        when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 10 ";         //--採用済み
//     	$sql .="       when base.is_approval = true then 11 ";        //--期限切れ
//     	$sql .="       else 1 ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                  //--hospital行がない
//     	$sql .="       case ";
//     	$sql .="        when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 11 ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="        END ";
//     	$sql .="        end  ";
//     	$sql .="       ,coalesce(base.sales_price,fp_honbu.sales_price) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_before6,0) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_over6,0) ";
//     	$sql .="       ) as sales_price ";
//     	//$sql .="       ,base.fp_id ";
//     	$sql .="       ,coalesce(base.fp_id,fp_honbu.id) AS fp_id ";
//     	$sql .="       ,base.pa_id AS price_adoption_id ";

//     	// 20200818詳細画面の影響で追加したので他の箇所にも追加 TODO
//     	$sql .="       ,base.fm_id AS fm_id ";

//     	//$sql .="       --csv ";
//     	$sql .="       ,mk.name AS production_maker_name ";
//     	$sql .="       ,base.dispensing_packaging_code ";
//     	$sql .="       ,pu.hot_code ";
//     	$sql .="       ,base.dosage_type_division ";
//     	$sql .="       ,mp.price AS medicine_price ";
//     	$sql .="       ,mp.price * pu.coefficient AS unit_medicine_price ";
//     	$sql .="       ,mp.start_date AS medicine_price_start_date ";
//     	$sql .="       ,base.facility_price_start_date AS facility_price_start_date ";
//     	$sql .="       ,base.created_at ";
//     	$sql .="       ,base.updated_at ";
//     	$sql .="       ,base.adoption_date ";
//     	$sql .="       ,base.adoption_stop_date ";
//     	$sql .="       ,base.production_stop_date ";
//     	$sql .="       ,base.transitional_deadline ";
//     	$sql .="       ,base.discontinuation_date ";
//     	$sql .="       ,base.discontinuing_division ";
//     	$sql .="       ,vprpab.status_forward ";
//     	$sql .="       ,vprpab.status_back ";
//     	$sql .="       ,vprpab.status_back_text ";
//     	//--追加
//     	$sql .="       ,base.optional_key1 ";
//     	$sql .="       ,base.optional_key2 ";
//     	$sql .="       ,base.optional_key3 ";
//     	$sql .="       ,base.optional_key4 ";
//     	$sql .="       ,base.optional_key5 ";
//     	$sql .="       ,base.optional_key6 ";
//     	$sql .="       ,base.optional_key7 ";
//     	$sql .="       ,base.optional_key8 ";
//     	$sql .="       ,fm_honbu.optional_key1 as hq_optional_key1 ";
//     	$sql .="       ,fm_honbu.optional_key2 as hq_optional_key2 ";
//     	$sql .="       ,fm_honbu.optional_key3 as hq_optional_key3 ";
//     	$sql .="       ,fm_honbu.optional_key4 as hq_optional_key4 ";
//     	$sql .="       ,fm_honbu.optional_key5 as hq_optional_key5 ";
//     	$sql .="       ,fm_honbu.optional_key6 as hq_optional_key6 ";
//     	$sql .="       ,fm_honbu.optional_key7 as hq_optional_key7 ";
//     	$sql .="       ,fm_honbu.optional_key8 as hq_optional_key8 ";
//     	$sql .="       , fm_honbu.medicine_id as fm_honbu_medicine_id ";
//     	$sql .="       , fp_honbu.medicine_id as fp_honbu_medicine_id ";
//     	$sql .="       , pa_honbu.medicine_id as pa_honbu_medicine_id  ";

//     	$sql .="        from ";
//     	$sql .="        ( ";
//     	//--基本部分(base)は【medicine】→【採用マスター】→【価格マスター】
//     	//--　　　　　　　　　　　　　　　　　　　　　　　　→【有効な申請データ】
//     	//--の繋ぎこみまで。採用マスターのないものは権限判定用ユーザーグループ列に実行ユーザーのprimary_user_group_id、primary_partner_user_group_idを返す
//     	//--商品情報、採用施設情報に対するWhere句はこのブロックに記述
//     	//--本部の繋ぎこみ、権限判定、状態判定は外側で行う(必然的に本部列に対する検索条件は外側に書かれる)
//     	$sql .="             select "; // --所属組織に制限された採用マスター
//     	$sql .="             grand.id as medicine_id ";
//     	$sql .="             , grand.name as medicine_name ";
//     	$sql .="             , grand.standard_unit_name as standard_unit ";
//     	$sql .="             , grand.search ";
//     	$sql .="             , grand.sales_packaging_code ";
//     	$sql .="             , grand.popular_name ";
//     	$sql .="             , grand.generic_product_detail_devision ";
//     	$sql .="             , grand.code ";
//     	$sql .="             , grand.maker_id ";
//     	$sql .="             , grand.name ";

//     	$sql .="             ,(CASE ";
//     	$sql .="                 WHEN pa.maker_name IS NOT NULL THEN pa.maker_name ";
//     	$sql .="                 ELSE fm.maker_name ";
//     	$sql .="                 END) AS maker_name ";
//     	//$sql .="             , fm.maker_name as maker_name ";
//     	//--, fm.sales_user_group_id as privilege_sales_user_group_id
//     	$sql .="             ,(case ";
//     	$sql .="                 when fm.sales_user_group_id IS NOT NULL THEN fm.sales_user_group_id ";
//     	$sql .="                 else pa.sales_user_group_id ";
//     	$sql .="              END) AS  privilege_sales_user_group_id ";
//     	$sql .="            , gr.partner_user_group_id as privilege_partner_user_group_id ";
//     	$sql .="            , fm.id as fm_id ";
//     	$sql .="            , fm.sales_user_group_id ";
//     	//--, fp.user_group_id
//     	//--, fp.user_group_name
//     	//--, fp.trader_user_group_id
//     	//--, fp.trader_group_name
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.purchase_user_group_id ";
//     	$sql .="            else pa.purchase_user_group_id ";
//     	$sql .="            END AS purchase_user_group_id ";
//     	$sql .="            , fp.start_date as facility_price_start_date ";
//     	$sql .="            , fp.end_date ";
//     	$sql .="            , fp.sales_price  ";                   //--商品情報
//     	$sql .="            , pa.id as pa_id ";
//     	$sql .="            , pa.deleted_at as pa_del ";
//     	$sql .="            , pa.comment as comment ";
//     	$sql .="            , pa.status ";
//     	$sql .="            , pa.application_date AS application_date ";
//     	$sql .="            , pa.created_at AS created_at ";
//     	$sql .="            , pa.updated_at AS updated_at ";
//     	$sql .="            , fp.id as fp_id ";
//     	$sql .="            , fp.deleted_at as fp_del ";
//     	$sql .="            , case ";
//     	$sql .="            when pa.medicine_id is null ";
//     	$sql .="            then pa.name ";
//     	$sql .="            else grand.name ";
//     	$sql .="            end as item_name "; // --商品名
//     	//--規格
//     	//--JAN
//     	//--単価情報
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.purchase_price ";
//     	$sql .="            else pa.purchase_price ";
//     	$sql .="            end as pur_price  "; //--仕入価格
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.sales_price ";
//     	$sql .="            else pa.sales_price ";
//     	$sql .="            end as sa_price  "; //--売上価格
//     	//--本部情報
//     	$sql .="            , gr.partner_user_group_id as honbu_id ";
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.trader_group_name ";
//     	$sql .="            else pa.trader_group_name ";
//     	$sql .="            END AS trader_name ";

//     	$sql .="            , facilities.id as facility_id ";
//     	$sql .="            , facilities.name as facility_name ";
//     	$sql .="            , grand.dispensing_packaging_code ";
//     	$sql .="            , grand.dosage_type_division ";
//     	$sql .="            , grand.production_stop_date ";
//     	$sql .="            , grand.transitional_deadline ";
//     	$sql .="            , grand.discontinuation_date ";
//     	$sql .="            , grand.discontinuing_division ";
//     	$sql .="            , fm.adoption_date AS adoption_date ";
//     	$sql .="            , fm.adoption_stop_date AS adoption_stop_date ";
//     	$sql .="            , fm.optional_key1 AS optional_key1 ";
//     	$sql .="            , fm.optional_key2 AS optional_key2 ";
//     	$sql .="            , fm.optional_key3 AS optional_key3 ";
//     	$sql .="            , fm.optional_key4 AS optional_key4 ";
//     	$sql .="            , fm.optional_key5 AS optional_key5 ";
//     	$sql .="            , fm.optional_key6 AS optional_key6 ";
//     	$sql .="            , fm.optional_key7 AS optional_key7 ";
//     	$sql .="            , fm.optional_key8 AS optional_key8 ";
//     	$sql .="            , fm.is_approval ";
//     	$sql .="            from ";
//     	$sql .="            facility_medicines as fm ";
//     	$sql .="            inner join f_show_records_facility(". $user->id . ") as fm_fsr ";
//     	$sql .="            on fm.sales_user_group_id = fm_fsr.user_group_id  ";
//     	//--所属組織に制限された価格マスター
//     	$sql .="            left join ( ";
//     	$sql .="                select ";
//     	$sql .="                fp2.* ";
//     	$sql .="                , fsr2.user_group_id ";
//     	$sql .="                , fsr2.user_group_name ";
//     	$sql .="                , fsr2.trader_user_group_id ";
//     	$sql .="                , fsr2.trader_group_name ";
//     	$sql .="                from ";
//     	$sql .="                facility_prices as fp2 ";
//     	$sql .="                inner join f_show_records(". $user->id . ") as fsr2 ";
//     	$sql .="                on fp2.sales_user_group_id = fsr2.user_group_id ";
//     	$sql .="                and fp2.purchase_user_group_id = fsr2.trader_user_group_id ";
//     	$sql .="            ) as fp ";
//     	$sql .="            on fm.medicine_id = fp.medicine_id ";
//     	$sql .="            and fm.sales_user_group_id = fp.sales_user_group_id ";
//     	$sql .="            and fp.start_date <= now() ";
//     	$sql .="            and fp.end_date >= now()  ";
//     	//--所属組織に制限された有効な申請データ
//     	$sql .="            left join ( ";
//     	$sql .="                 select ";
//     	$sql .="                 pa2.* ";
//     	$sql .="                 , fsr_pa.trader_group_name ";
//     	$sql .="                 from ";
//     	$sql .="                 price_adoptions as pa2 ";
//     	$sql .="                 inner join f_show_records(". $user->id . ") as fsr_pa ";
//     	$sql .="                 on pa2.sales_user_group_id = fsr_pa.user_group_id ";
//     	$sql .="                 and pa2.purchase_user_group_id = fsr_pa.trader_user_group_id ";
//     	$sql .="                 and pa2.status <> 10 ";
//     	$sql .="             ) as pa ";
//     	$sql .="             on fm.medicine_id = pa.medicine_id ";
//     	$sql .="             and fm.sales_user_group_id = pa.sales_user_group_id ";
//     	$sql .="             left join medicines as grand ";
//     	$sql .="             on fm.medicine_id = grand.id ";
//     	$sql .="             left join group_relations as gr ";
//     	$sql .="             on fm.sales_user_group_id = gr.user_group_id  ";

//     	$sql .="             left join user_groups as facilities ";
//     	$sql .="             on fm.sales_user_group_id=facilities.id ";
//     	$sql .="             union all ";
//     	$sql .="             select ";
//     	$sql .="             grand_p.id as medicine_id ";
//     	$sql .="             , grand_p.name as medicine_naem ";
//     	$sql .="             , grand_p.standard_unit_name as standard_unit ";
//     	$sql .="             , grand_p.search ";
//     	$sql .="             , grand_p.sales_packaging_code ";
//     	$sql .="             , grand_p.popular_name ";
//     	$sql .="             , grand_p.generic_product_detail_devision ";
//     	$sql .="             , grand_p.code ";
//     	$sql .="             , grand_p.maker_id ";
//     	$sql .="             , grand_p.name ";
//     	$sql .="             , null as maker_name ";
//     	$sql .="             , gr_p.user_group_id as privilege_sales_user_group_id ";
//     	$sql .="             , gr_p.partner_user_group_id as privilege_partner_user_group_id ";
//     	//$sql .="             , 3 as privilege_sales_user_group_id ";
//     	//$sql .="             , 2 as privilege_partner_user_group_id ";
//     	$sql .="             , null as fm_id ";
//     	$sql .="             , null as sales_user_group_id ";

//     	$sql .="             , null as purchase_user_group_id ";
//     	$sql .="             , null as facility_price_start_date ";
//     	$sql .="             , null as end_date ";
//     	$sql .="             , null as sales_price  ";              //--商品情報
//     	$sql .="             , null as pa_id ";
//     	$sql .="             , null as pa_del ";
//     	$sql .="             , null as comment ";
//     	$sql .="             , null as status ";
//     	$sql .="             , null AS application_date ";
//     	$sql .="             , null AS created_at ";
//     	$sql .="             , null AS updated_at ";
//     	$sql .="             , null as fp_id ";
//     	$sql .="             , null as fp_del ";
//     	$sql .="             , null as item_name "; //-- 商品名
//     	$sql .="             , null as pur_price "; //-- 仕入価格
//     	$sql .="             , null as sa_price ";  //-- 売上価格
//     	$sql .="             , null as honbu_id ";
//     	$sql .="             , null as trader_name ";
//     	$sql .="             , null as facility_id ";
//     	$sql .="             , null as facility_name ";
//     	$sql .="             , grand_p.dispensing_packaging_code ";
//     	$sql .="             , grand_p.dosage_type_division ";
//     	$sql .="             , grand_p.production_stop_date ";
//     	$sql .="             , grand_p.transitional_deadline ";
//     	$sql .="             , grand_p.discontinuation_date ";
//     	$sql .="             , grand_p.discontinuing_division ";
//     	$sql .="             , null AS adoption_date ";
//     	$sql .="             , null AS adoption_stop_date ";
//     	$sql .="             , null AS optional_key1 ";
//     	$sql .="             , null AS optional_key2 ";
//     	$sql .="             , null AS optional_key3 ";
//     	$sql .="             , null AS optional_key4 ";
//     	$sql .="             , null AS optional_key5 ";
//     	$sql .="             , null AS optional_key6 ";
//     	$sql .="             , null AS optional_key7 ";
//     	$sql .="             , null AS optional_key8 ";
//     	$sql .="             , null as is_approval ";
//     	$sql .="             from ";
//     	$sql .="             medicines as grand_p ";
//     	$sql .="             left join users as usr_p";
//     	$sql .="              on usr_p.id = ". $user->id;
//     	$sql .="             left join group_relations as gr_p";
//     	$sql .="              on gr_p.user_group_id = usr_p.primary_user_group_id";
//     	$sql .="             left outer join facility_medicines as fm_p";
//     	$sql .="              on grand_p.id = fm_p.medicine_id";
//     	$sql .="              and fm_p.sales_user_group_id = gr_p.user_group_id";
//     	//$sql .="             left outer join facility_medicines as fm_p";
//     	//$sql .="              on grand_p.id = fm_p.medicine_id";
//     	//$sql .="              and fm_p.sales_user_group_id = 3";
//     	$sql .="             where";
//     	$sql .="               grand_p.deleted_at is null";
//     	$sql .="               and fm_p.medicine_id is null";
//     	$sql .="        ) as base ";
//     	//--本部：採用マスター
//     	$sql .="        left join facility_medicines as fm_honbu ";
//     	$sql .="          on base.medicine_id = fm_honbu.medicine_id ";
//     	$sql .="          and base.privilege_partner_user_group_id = fm_honbu.sales_user_group_id ";
//     	//--本部：価格マスター
//     	$sql .="        left join facility_prices as fp_honbu ";
//     	$sql .="         on base.medicine_id = fp_honbu.medicine_id ";
//     	$sql .="         and base.privilege_partner_user_group_id = fp_honbu.sales_user_group_id ";
//     	$sql .="         and fp_honbu.start_date <= now() ";
//     	$sql .="        and fp_honbu.end_date >= now() ";
//     	$sql .="        left join f_show_records(". $user->id . ") as fshow";
//     	$sql .="        on fp_honbu.sales_user_group_id = fshow.user_group_id";
//     	$sql .="        and fp_honbu.purchase_user_group_id = fshow.trader_user_group_id";
//     	//--本部：有効な申請データ
//     	$sql .="        left join price_adoptions as pa_honbu ";
//     	$sql .="        on base.medicine_id = pa_honbu.medicine_id ";
//     	$sql .="        and base.privilege_partner_user_group_id = pa_honbu.sales_user_group_id ";
//     	$sql .="        and pa_honbu.status <> 10 ";

//     	//-- add2
//     	$sql .="        left join pack_units as pu ";
//     	$sql .="          on base.medicine_id =pu.medicine_id ";
//     	$sql .="        LEFT JOIN medicine_prices AS mp ";
//     	$sql .="          ON base.medicine_id = mp.medicine_id ";
//     	$sql .="          and mp.end_date >= NOW() ";
//     	$sql .="          and mp.start_date <= NOW() ";
//     	$sql .="          and mp.deleted_at is NULL ";
//     	$sql .="        left join makers as mk ";
//     	$sql .="          on base.maker_id =  mk.id ";
//     	$sql .="        LEFT JOIN users AS usr ";
//     	$sql .="          ON usr.id = ". $user->id;
//     	$sql .="        left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .="          on vprpab.user_group_id = COALESCE(base.sales_user_group_id, usr.primary_user_group_id) ";
//     	$sql .="          and vprpab.user_id = ". $user->id;
//     	$sql .="          and vprpab.status = ";
//     	$sql .="           ( CASE ";
//     	$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     	$sql .="                 case ";
//     	$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="                 when base.fp_id is not null then 10 ";         //--採用済み
//     	$sql .="                 when base.is_approval = true then 11  ";                                   // --期限切れ
//     	$sql .="                 else 1  ";
//     	$sql .="                 end ";
//     	$sql .="               else  ";                                   //--hospital行がない
//     	$sql .="                 case ";
//     	$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="                 when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="                 when fm_honbu.id is not null and fm_honbu.is_approval = true then 11  ";       //--本部期限切れ 13
//     	$sql .="                 else 1      ";                                 //--未採用
//     	$sql .="               END ";
//     	$sql .="            end ) ";
//     	$sql .=" left join v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .=" on vpry.user_group_id = COALESCE(base.sales_user_group_id, usr.primary_user_group_id) ";
//     	$sql .=" and vpry.user_id = ". $user->id;
//     	$sql .=" where 1 = 1 ";


//     	//			--WHERE

//     	//			--base.search like '%14987114336009%'
//     	//					--base.purchase_user_group_id = 36

//     	//					--limit 100
//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(10, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 11;
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $req_status).")";

//     		} else {
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $request->status).")";
//     			$req_status = $request->status;

//     		}
//     		$sql .= "and ";
//     		$sql .="           ( CASE ";
//     		$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     		$sql .="                 case ";
//     		$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     		$sql .="                 when base.fp_id is not null then 10 ";         //--採用済み
//     		$sql .="                 else 11  ";                                   // --期限切れ
//     		$sql .="                 end ";
//     		$sql .="               else  ";                                   //--hospital行がない
//     		$sql .="                 case ";
//     		$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     		$sql .="                 when fp_honbu.id is not null then 8  ";       //--採用可
//     		$sql .="                 when fm_honbu.id is not null then 11  ";       //--本部期限切れ
//     		$sql .="                 else 1      ";                                 //--未採用
//     		$sql .="               END ";
//     		$sql .="            end ) in (".implode(',', $req_status).")";

//     	}

//     	//採用中止日 TODO
//     	//if (!empty($request->stop_date)) {
//     	//	 $sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//     	//}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and coalesce(base.facility_id, base.sales_user_group_id) in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and base.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and base.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pu.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and base.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and base.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	// TODO
// //     	 //項目1
// //     	 //項目1
// //     	 if (!empty($request->optional_key1)) {
// //     	 $sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

// //     	 }

// //     	 //項目2
// //     	 if (!empty($request->optional_key2)) {
// //     	 $sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

// //     	 }

// //     	 //項目3
// //     	 if (!empty($request->optional_key3)) {
// //     	 $sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

// //     	 }

// //     	 //項目4
// //     	 if (!empty($request->optional_key4)) {
// //     	 $sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

// //     	 }

// //     	 //項目5
// //     	 if (!empty($request->optional_key5)) {
// //     	 $sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

// //     	 }

// //     	 //項目6
// //     	 if (!empty($request->optional_key6)) {
// //     	 $sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

// //     	 }

// //     	 //項目7
// //     	 if (!empty($request->optional_key7)) {
// //     	 $sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

// //     	 }

// //     	 //項目8
// //     	 if (!empty($request->optional_key8)) {
// //     	 $sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

// //     	 }


//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//     	}

//     	return $sql;

//     }

    /**
     * 採用一覧取得
     *
     * @param Request $request リクエスト情報
     * @param Facility $facility 施設情報
     * @param int $count 表示件数
     * @param $user ユーザ情報
     * @return
     */
//     public function listWithApplyTest_bunkatsumae(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	$sql =" select ";
//     	$sql .="       	base.medicine_id ";
//     	$sql .="       ,base.code ";
//     	$sql .="       ,base.sales_packaging_code ";
//     	$sql .="       ,base.generic_product_detail_devision ";
//     	$sql .="       ,base.popular_name ";
//     	$sql .="       ,pu.jan_code ";
//     	$sql .="       ,COALESCE(base.item_name, base.name) AS medicine_name ";
//     	$sql .="        ,base.standard_unit ";
//     	//$sql .="       --,base.maker_id ";
//     	$sql .="       ,COALESCE(base.maker_name, mk.name) as maker_name ";
//     	$sql .="       ,mp.price ";
//     	$sql .="       ,base.comment ";
//     	$sql .="       , CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="       case ";
//     	$sql .="       when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 10  ";        //--採用済み
//     	$sql .="       when base.is_approval = true then 11  ";                                  //  --期限切れ
//     	$sql .="       else 1  ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                 // --hospital行がない
//     	$sql .="       case ";
//     	$sql .="       when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 11  ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="       END ";
//     	$sql .="       end as status  ";
//     	$sql .="       ,base.facility_id ";
//     	$sql .="       ,base.facility_name ";

//     	$sql .="       ,pu.total_pack_unit ";
//     	$sql .="       ,pu.price as pack_unit_price ";
//     	$sql .="       ,pu.coefficient ";
//     	$sql .="       ,base.application_date ";
//     	//$sql .="       ,base.trader_name ";
//     	$sql .="       ,CASE WHEN base.trader_name IS NOT NULL THEN base.trader_name ";
//     	$sql .="        ELSE fshow.trader_group_name ";
//     	$sql .="        END AS trader_name ";
//     	$sql .="       ,f_show_sales_price( ";
//     	$sql .="        CASE ";
//     	$sql .="       when base.fm_id is not null then ";        //--hospital行があるので
//     	$sql .="        case ";
//     	$sql .="        when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="       when base.fp_id is not null then 10 ";         //--採用済み
//     	$sql .="       when base.is_approval = true then 11 ";        //--期限切れ
//     	$sql .="       else 1 ";
//     	$sql .="       end ";
//     	$sql .="       else   ";                                  //--hospital行がない
//     	$sql .="       case ";
//     	$sql .="        when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="       when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="       when fm_honbu.id is not null and fm_honbu.is_approval = true then 11  ";       //--本部期限切れ 13
//     	$sql .="       else 1  ";                                     //--未採用
//     	$sql .="        END ";
//     	$sql .="        end  ";
//     	$sql .="       ,coalesce(base.sales_price,fp_honbu.sales_price) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_before6,0) ";
//     	$sql .="       ,coalesce(vpry.show_sales_price_over6,0) ";
//     	$sql .="       ) as sales_price ";
//     	//$sql .="       ,base.fp_id ";
//     	$sql .="       ,coalesce(base.fp_id,fp_honbu.id) AS fp_id ";
//     	$sql .="       ,base.pa_id AS price_adoption_id ";

//     	//$sql .="       --csv ";
//     	$sql .="       ,mk.name AS production_maker_name ";
//     	$sql .="       ,base.dispensing_packaging_code ";
//     	$sql .="       ,pu.hot_code ";
//     	$sql .="       ,base.dosage_type_division ";
//     	$sql .="       ,mp.price AS medicine_price ";
//     	$sql .="       ,mp.price * pu.coefficient AS unit_medicine_price ";
//     	$sql .="       ,mp.start_date AS medicine_price_start_date ";
//     	$sql .="       ,base.facility_price_start_date AS facility_price_start_date ";
//     	$sql .="       ,base.created_at ";
//     	$sql .="       ,base.updated_at ";
//     	$sql .="       ,base.adoption_date ";
//     	$sql .="       ,base.adoption_stop_date ";
//     	$sql .="       ,base.production_stop_date ";
//     	$sql .="       ,base.transitional_deadline ";
//     	$sql .="       ,base.discontinuation_date ";
//     	$sql .="       ,base.discontinuing_division ";
//     	$sql .="       ,vprpab.status_forward ";
//     	$sql .="       ,vprpab.status_back ";
//     	$sql .="       ,vprpab.status_back_text ";
//     	//--追加
//     	$sql .="       ,base.optional_key1 ";
//     	$sql .="       ,base.optional_key2 ";
//     	$sql .="       ,base.optional_key3 ";
//     	$sql .="       ,base.optional_key4 ";
//     	$sql .="       ,base.optional_key5 ";
//     	$sql .="       ,base.optional_key6 ";
//     	$sql .="       ,base.optional_key7 ";
//     	$sql .="       ,base.optional_key8 ";
//     	$sql .="       ,fm_honbu.optional_key1 as hq_optional_key1 ";
//     	$sql .="       ,fm_honbu.optional_key2 as hq_optional_key2 ";
//     	$sql .="       ,fm_honbu.optional_key3 as hq_optional_key3 ";
//     	$sql .="       ,fm_honbu.optional_key4 as hq_optional_key4 ";
//     	$sql .="       ,fm_honbu.optional_key5 as hq_optional_key5 ";
//     	$sql .="       ,fm_honbu.optional_key6 as hq_optional_key6 ";
//     	$sql .="       ,fm_honbu.optional_key7 as hq_optional_key7 ";
//     	$sql .="       ,fm_honbu.optional_key8 as hq_optional_key8 ";
//     	$sql .="       , fm_honbu.medicine_id as fm_honbu_medicine_id ";
//     	$sql .="       , fp_honbu.medicine_id as fp_honbu_medicine_id ";
//     	$sql .="       , pa_honbu.medicine_id as pa_honbu_medicine_id  ";

//     	$sql .="        from ";
//     	$sql .="        ( ";
//     	//--基本部分(base)は【medicine】→【採用マスター】→【価格マスター】
//     	//--　　　　　　　　　　　　　　　　　　　　　　　　→【有効な申請データ】
//     	//--の繋ぎこみまで。採用マスターのないものは権限判定用ユーザーグループ列に実行ユーザーのprimary_user_group_id、primary_partner_user_group_idを返す
//     	//--商品情報、採用施設情報に対するWhere句はこのブロックに記述
//     	//--本部の繋ぎこみ、権限判定、状態判定は外側で行う(必然的に本部列に対する検索条件は外側に書かれる)
//     	$sql .="             select "; // --所属組織に制限された採用マスター
//     	$sql .="             grand.id as medicine_id ";
//     	$sql .="             , grand.name as medicine_name ";
//     	$sql .="             , grand.standard_unit_name as standard_unit ";
//     	$sql .="             , grand.search ";
//     	$sql .="             , grand.sales_packaging_code ";
//     	$sql .="             , grand.popular_name ";
//     	$sql .="             , grand.generic_product_detail_devision ";
//     	$sql .="             , grand.code ";
//     	$sql .="             , grand.maker_id ";
//     	$sql .="             , grand.name ";

//     	$sql .="             ,(CASE ";
//     	$sql .="                 WHEN pa.maker_name IS NOT NULL THEN pa.maker_name ";
//     	$sql .="                 ELSE fm.maker_name ";
//     	$sql .="                 END) AS maker_name ";
//     	//$sql .="             , fm.maker_name as maker_name ";
//     	//--, fm.sales_user_group_id as privilege_sales_user_group_id
//     	$sql .="             ,(case ";
//     	$sql .="                 when fm.sales_user_group_id IS NOT NULL THEN fm.sales_user_group_id ";
//     	$sql .="                 else pa.sales_user_group_id ";
//     	$sql .="              END) AS  privilege_sales_user_group_id ";
//     	$sql .="            , gr.partner_user_group_id as privilege_partner_user_group_id ";
//     	$sql .="            , fm.id as fm_id ";
//     	$sql .="            , fm.sales_user_group_id ";
//     	//--, fp.user_group_id
//     	//--, fp.user_group_name
//     	//--, fp.trader_user_group_id
//     	//--, fp.trader_group_name
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.purchase_user_group_id ";
//     	$sql .="            else pa.purchase_user_group_id ";
//     	$sql .="            END AS purchase_user_group_id ";
//     	$sql .="            , fp.start_date as facility_price_start_date ";
//     	$sql .="            , fp.end_date ";
//     	$sql .="            , fp.sales_price  ";                   //--商品情報
//     	$sql .="            , pa.id as pa_id ";
//     	$sql .="            , pa.deleted_at as pa_del ";
//     	$sql .="            , pa.comment as comment ";
//     	$sql .="            , pa.status ";
//     	$sql .="            , pa.application_date AS application_date ";
//     	$sql .="            , pa.created_at AS created_at ";
//     	$sql .="            , pa.updated_at AS updated_at ";
//     	$sql .="            , fp.id as fp_id ";
//     	$sql .="            , fp.deleted_at as fp_del ";
//     	$sql .="            , case ";
//     	$sql .="            when pa.medicine_id is null ";
//     	$sql .="            then pa.name ";
//     	$sql .="            else grand.name ";
//     	$sql .="            end as item_name "; // --商品名
//     	//--規格
//     	//--JAN
//     	//--単価情報
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.purchase_price ";
//     	$sql .="            else pa.purchase_price ";
//     	$sql .="            end as pur_price  "; //--仕入価格
//     	$sql .="            , case ";
//     	$sql .="            when pa.id is null ";
//     	$sql .="            then fp.sales_price ";
//     	$sql .="            else pa.sales_price ";
//     	$sql .="            end as sa_price  "; //--売上価格
//     	//--本部情報
//     	$sql .="            , gr.partner_user_group_id as honbu_id ";
//     	$sql .="            , case ";
//     	$sql .="            WHEN pa.id IS null ";
//     	$sql .="            THEN fp.trader_group_name ";
//     	$sql .="            else pa.trader_group_name ";
//     	$sql .="            END AS trader_name ";

//     	$sql .="            , facilities.id as facility_id ";
//     	$sql .="            , facilities.name as facility_name ";
//     	$sql .="            , grand.dispensing_packaging_code ";
//     	$sql .="            , grand.dosage_type_division ";
//     	$sql .="            , grand.production_stop_date ";
//     	$sql .="            , grand.transitional_deadline ";
//     	$sql .="            , grand.discontinuation_date ";
//     	$sql .="            , grand.discontinuing_division ";
//     	$sql .="            , fm.adoption_date AS adoption_date ";
//     	$sql .="            , fm.adoption_stop_date AS adoption_stop_date ";
//     	$sql .="            , fm.optional_key1 AS optional_key1 ";
//     	$sql .="            , fm.optional_key2 AS optional_key2 ";
//     	$sql .="            , fm.optional_key3 AS optional_key3 ";
//     	$sql .="            , fm.optional_key4 AS optional_key4 ";
//     	$sql .="            , fm.optional_key5 AS optional_key5 ";
//     	$sql .="            , fm.optional_key6 AS optional_key6 ";
//     	$sql .="            , fm.optional_key7 AS optional_key7 ";
//     	$sql .="            , fm.optional_key8 AS optional_key8 ";
//     	$sql .="            , fm.is_approval ";
//     	$sql .="            from ";
//     	$sql .="            facility_medicines as fm ";
//     	$sql .="            inner join f_show_records_facility(". $user->id . ") as fm_fsr ";
//     	$sql .="            on fm.sales_user_group_id = fm_fsr.user_group_id  ";
//     	//--所属組織に制限された価格マスター
//     	$sql .="            left join ( ";
//     	$sql .="                select ";
//     	$sql .="                fp2.* ";
//     	$sql .="                , fsr2.user_group_id ";
//     	$sql .="                , fsr2.user_group_name ";
//     	$sql .="                , fsr2.trader_user_group_id ";
//     	$sql .="                , fsr2.trader_group_name ";
//     	$sql .="                from ";
//     	$sql .="                facility_prices as fp2 ";
//     	$sql .="                inner join f_show_records(". $user->id . ") as fsr2 ";
//     	$sql .="                on fp2.sales_user_group_id = fsr2.user_group_id ";
//     	$sql .="                and fp2.purchase_user_group_id = fsr2.trader_user_group_id ";
//     	$sql .="            ) as fp ";
//     	$sql .="            on fm.medicine_id = fp.medicine_id ";
//     	$sql .="            and fm.sales_user_group_id = fp.sales_user_group_id ";
//     	$sql .="            and fp.start_date <= now() ";
//     	$sql .="            and fp.end_date >= now()  ";
//     	//--所属組織に制限された有効な申請データ
//     	$sql .="            left join ( ";
//     	$sql .="                 select ";
//     	$sql .="                 pa2.* ";
//     	$sql .="                 , fsr_pa.trader_group_name ";
//     	$sql .="                 from ";
//     	$sql .="                 price_adoptions as pa2 ";
//     	$sql .="                 inner join f_show_records(". $user->id . ") as fsr_pa ";
//     	$sql .="                 on pa2.sales_user_group_id = fsr_pa.user_group_id ";
//     	$sql .="                 and pa2.purchase_user_group_id = fsr_pa.trader_user_group_id ";
//     	$sql .="                 and pa2.status <> 10 ";
//     	$sql .="             ) as pa ";
//     	$sql .="             on fm.medicine_id = pa.medicine_id ";
//     	$sql .="             and fm.sales_user_group_id = pa.sales_user_group_id ";
//     	$sql .="             left join medicines as grand ";
//     	$sql .="             on fm.medicine_id = grand.id ";
//     	$sql .="             left join group_relations as gr ";
//     	$sql .="             on fm.sales_user_group_id = gr.user_group_id  ";

//     	$sql .="             left join user_groups as facilities ";
//     	$sql .="             on fm.sales_user_group_id=facilities.id ";
//     	$sql .="             union all ";
//     	$sql .="             select ";
//     	$sql .="             grand_p.id as medicine_id ";
//     	$sql .="             , grand_p.name as medicine_naem ";
//     	$sql .="             , grand_p.standard_unit_name as standard_unit ";
//     	$sql .="             , grand_p.search ";
//     	$sql .="             , grand_p.sales_packaging_code ";
//     	$sql .="             , grand_p.popular_name ";
//     	$sql .="             , grand_p.generic_product_detail_devision ";
//     	$sql .="             , grand_p.code ";
//     	$sql .="             , grand_p.maker_id ";
//     	$sql .="             , grand_p.name ";
//     	$sql .="             , null as maker_name ";
//     	$sql .="             , gr_p.user_group_id as privilege_sales_user_group_id ";
//     	$sql .="             , gr_p.partner_user_group_id as privilege_partner_user_group_id ";
//     	//$sql .="             , 3 as privilege_sales_user_group_id ";
//     	//$sql .="             , 2 as privilege_partner_user_group_id ";
//     	$sql .="             , null as fm_id ";
//     	$sql .="             , null as sales_user_group_id ";

//     	$sql .="             , null as purchase_user_group_id ";
//     	$sql .="             , null as facility_price_start_date ";
//     	$sql .="             , null as end_date ";
//     	$sql .="             , null as sales_price  ";              //--商品情報
//     	$sql .="             , null as pa_id ";
//     	$sql .="             , null as pa_del ";
//     	$sql .="             , null as comment ";
//     	$sql .="             , null as status ";
//     	$sql .="             , null AS application_date ";
//     	$sql .="             , null AS created_at ";
//     	$sql .="             , null AS updated_at ";
//     	$sql .="             , null as fp_id ";
//     	$sql .="             , null as fp_del ";
//     	$sql .="             , null as item_name "; //-- 商品名
//     	$sql .="             , null as pur_price "; //-- 仕入価格
//     	$sql .="             , null as sa_price ";  //-- 売上価格
//     	$sql .="             , null as honbu_id ";
//     	$sql .="             , null as trader_name ";
//     	$sql .="             , null as facility_id ";
//     	$sql .="             , null as facility_name ";
//     	$sql .="             , grand_p.dispensing_packaging_code ";
//     	$sql .="             , grand_p.dosage_type_division ";
//     	$sql .="             , grand_p.production_stop_date ";
//     	$sql .="             , grand_p.transitional_deadline ";
//     	$sql .="             , grand_p.discontinuation_date ";
//     	$sql .="             , grand_p.discontinuing_division ";
//     	$sql .="             , null AS adoption_date ";
//     	$sql .="             , null AS adoption_stop_date ";
//     	$sql .="             , null AS optional_key1 ";
//     	$sql .="             , null AS optional_key2 ";
//     	$sql .="             , null AS optional_key3 ";
//     	$sql .="             , null AS optional_key4 ";
//     	$sql .="             , null AS optional_key5 ";
//     	$sql .="             , null AS optional_key6 ";
//     	$sql .="             , null AS optional_key7 ";
//     	$sql .="             , null AS optional_key8 ";
//     	$sql .="             , null as is_approval ";
//     	$sql .="             from ";
//     	$sql .="             medicines as grand_p ";
//     	$sql .="             left join users as usr_p";
//     	$sql .="              on usr_p.id = ". $user->id;
//     	$sql .="             left join group_relations as gr_p";
//     	$sql .="              on gr_p.user_group_id = usr_p.primary_user_group_id";
//     	$sql .="             left outer join facility_medicines as fm_p";
//     	$sql .="              on grand_p.id = fm_p.medicine_id";
//     	$sql .="              and fm_p.sales_user_group_id = gr_p.user_group_id";
//     	//$sql .="             left outer join facility_medicines as fm_p";
//     	//$sql .="              on grand_p.id = fm_p.medicine_id";
//     	//$sql .="              and fm_p.sales_user_group_id = 3";
//     	$sql .="             where";
//     	$sql .="               grand_p.deleted_at is null";
//     	$sql .="               and fm_p.medicine_id is null";
//     	$sql .="        ) as base ";
//     	//--本部：採用マスター
//     	$sql .="        left join facility_medicines as fm_honbu ";
//     	$sql .="          on base.medicine_id = fm_honbu.medicine_id ";
//     	$sql .="          and base.privilege_partner_user_group_id = fm_honbu.sales_user_group_id ";
//     	//--本部：価格マスター
//     	$sql .="        left join facility_prices as fp_honbu ";
//     	$sql .="         on base.medicine_id = fp_honbu.medicine_id ";
//     	$sql .="         and base.privilege_partner_user_group_id = fp_honbu.sales_user_group_id ";
//     	$sql .="         and fp_honbu.start_date <= now() ";
//     	$sql .="        and fp_honbu.end_date >= now() ";
//     	$sql .="        left join f_show_records(". $user->id . ") as fshow";
//     	$sql .="        on fp_honbu.sales_user_group_id = fshow.user_group_id";
//     	$sql .="        and fp_honbu.purchase_user_group_id = fshow.trader_user_group_id";
//     	//--本部：有効な申請データ
//     	$sql .="        left join price_adoptions as pa_honbu ";
//     	$sql .="        on base.medicine_id = pa_honbu.medicine_id ";
//     	$sql .="        and base.privilege_partner_user_group_id = pa_honbu.sales_user_group_id ";
//     	$sql .="        and pa_honbu.status <> 10 ";

//     	//-- add2
//     	$sql .="        left join pack_units as pu ";
//     	$sql .="          on base.medicine_id =pu.medicine_id ";
//     	$sql .="        LEFT JOIN medicine_prices AS mp ";
//     	$sql .="          ON base.medicine_id = mp.medicine_id ";
//     	$sql .="          and mp.end_date >= NOW() ";
//     	$sql .="          and mp.start_date <= NOW() ";
//     	$sql .="          and mp.deleted_at is NULL ";
//     	$sql .="        left join makers as mk ";
//     	$sql .="          on base.maker_id =  mk.id ";
//     	$sql .="        LEFT JOIN users AS usr ";
//     	$sql .="          ON usr.id = ". $user->id;
//     	$sql .="        left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .="          on vprpab.user_group_id = COALESCE(base.sales_user_group_id, usr.primary_user_group_id) ";
//     	$sql .="          and vprpab.user_id = ". $user->id;
//     	$sql .="          and vprpab.status = ";
//     	$sql .="           ( CASE ";
//     	$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     	$sql .="                 case ";
//     	$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     	$sql .="                 when base.fp_id is not null then 10 ";         //--採用済み
//     	$sql .="                 when base.is_approval = true then 11  ";                                   // --期限切れ
//     	$sql .="                 else 1  ";
//     	$sql .="                 end ";
//     	$sql .="               else  ";                                   //--hospital行がない
//     	$sql .="                 case ";
//     	$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     	$sql .="                 when fp_honbu.id is not null then 8  ";       //--採用可 12
//     	$sql .="                 when fm_honbu.id is not null and fm_honbu.is_approval = true then 11  ";       //--本部期限切れ 13
//     	$sql .="                 else 1      ";                                 //--未採用
//     	$sql .="               END ";
//     	$sql .="            end ) ";
//     	$sql .=" left join v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .=" on vpry.user_group_id = COALESCE(base.sales_user_group_id, usr.primary_user_group_id) ";
//     	$sql .=" and vpry.user_id = ". $user->id;
//     	$sql .=" where 1 = 1 ";


//     	//			--WHERE

//     	//			--base.search like '%14987114336009%'
//     	//					--base.purchase_user_group_id = 36

//     	//					--limit 100
//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(10, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 11;
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $req_status).")";

//     		} else {
//     			//$sql .= " and coalesce(item.status,1) in (".implode(',', $request->status).")";
//     			$req_status = $request->status;

//     		}
//     		$sql .= "and ";
//     		$sql .="           ( CASE ";
//     		$sql .="               when base.fm_id is not null then  ";       //--hospital行があるので
//     		$sql .="                 case ";
//     		$sql .="                 when base.pa_id is not null then base.status "; //--申請行があるのでステータス表示
//     		$sql .="                 when base.fp_id is not null then 10 ";         //--採用済み
//     		$sql .="                 else 11  ";                                   // --期限切れ
//     		$sql .="                 end ";
//     		$sql .="               else  ";                                   //--hospital行がない
//     		$sql .="                 case ";
//     		$sql .="                 when pa_honbu.id is not null then pa_honbu.status ";
//     		$sql .="                 when fp_honbu.id is not null then 8  ";       //--採用可
//     		$sql .="                 when fm_honbu.id is not null then 11  ";       //--本部期限切れ
//     		$sql .="                 else 1      ";                                 //--未採用
//     		$sql .="               END ";
//     		$sql .="            end ) in (".implode(',', $req_status).")";

//     	}

//     	//採用中止日 TODO
//     	//if (!empty($request->stop_date)) {
//     	//	 $sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//     	//}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and coalesce(base.facility_id, base.sales_user_group_id) in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and base.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and base.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pu.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and base.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and base.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	// TODO
// //     	 //項目1
// //     	 //項目1
// //     	 if (!empty($request->optional_key1)) {
// //     	 $sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

// //     	 }

// //     	 //項目2
// //     	 if (!empty($request->optional_key2)) {
// //     	 $sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

// //     	 }

// //     	 //項目3
// //     	 if (!empty($request->optional_key3)) {
// //     	 $sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

// //     	 }

// //     	 //項目4
// //     	 if (!empty($request->optional_key4)) {
// //     	 $sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

// //     	 }

// //     	 //項目5
// //     	 if (!empty($request->optional_key5)) {
// //     	 $sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

// //     	 }

// //     	 //項目6
// //     	 if (!empty($request->optional_key6)) {
// //     	 $sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

// //     	 }

// //     	 //項目7
// //     	 if (!empty($request->optional_key7)) {
// //     	 $sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

// //     	 }

// //     	 //項目8
// //     	 if (!empty($request->optional_key8)) {
// //     	 $sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
// //     	 $sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

// //     	 }


//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//     	}



//     	$count_sql = " select count(*) from (".$sql.") as tmp ";
//     	$all_count = \DB::select($count_sql);

//     	$count2=$all_count[0]->count;


//     	$sql .=" order by ";
//     	$sql .="  maker_name asc ";
//     	$sql .=" ,medicine_name asc ";
//     	$sql .=" ,standard_unit asc ";
//     	$sql .=" ,jan_code asc ";
//     	$sql .=" ,facility_id asc ";

//     	if ($count == -1) {
//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     	} else {
//     		if (!isset($request->page_count)){
//     			$offset=0;
//     		} else {
//     			if ($request->page == 0) {
//     				$offset=0;
//     			} else {
//     				$offset=($request->page - 1) * $request->page_count;
//     			}
//     		}

//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     		$sql .=" limit ".$per_page." offset ".$offset;

//     	}

//     	$all_rec = \DB::select($sql);
//     	$result = Medicine::query()->hydrate($all_rec);


//     	// ページ番号が指定されていなかったら１ページ目
//     	$page_num = isset($request->page) ? $request->page : 1;
//     	// ページ番号に従い、表示するレコードを切り出す
//     	$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

//     	// ページャーオブジェクトを生成
//     	$pager= new \Illuminate\Pagination\LengthAwarePaginator(
//     			$result, // ページ番号で指定された表示するレコード配列
//     			$count2, // 検索結果の全レコード総数
//     			$per_page, // 1ページ当りの表示数
//     			$page_num, // 表示するページ
//     			['path' => $request->url()] // ページャーのリンク先のURLを指定
//     			);

//     	return $pager;

//     }

    /**
     * 採用一覧取得
     *
     * @param Request $request リクエスト情報
     * @param Facility $facility 施設情報
     * @param int $count 表示件数
     * @param $user ユーザ情報
     * @return
     */
//     public function listWithApplyTest_old_1(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	// SQL katamari start
// $sql  = " select";
// $sql .= "    grand.id AS medicine_id";
// $sql .= "    ,COALESCE(item.item_name, grand.name) AS medicine_name";
// $sql .= "    ,grand.standard_unit_name AS standard_unit";
// $sql .= "    ,grand.sales_packaging_code";
// $sql .= "    ,grand.popular_name";
// $sql .= "    ,grand.generic_product_detail_devision";
// $sql .= "    ,grand.code";
// $sql .= "    ,pu.jan_code";
// //$sql .= "    ,COALESCE(item.sales_user_group_id, usr.primary_user_group_id ) as sales_user_group_id";
// $sql .= "    ,COALESCE(item.maker_name, mk.name) as maker_name";
// $sql .= "    ,item.comment";
// $sql .= "    ,mp.price";
// $sql .= "    ,item.facility_id";
// $sql .= "    ,item.facility_name";
// $sql .= "    ,pu.total_pack_unit";
// $sql .= "    ,pu.price AS pack_unit_price";
// $sql .= "    ,pu.coefficient";
// $sql .= "    ,item.application_date";
// //$sql .= "    ,item.sa_price AS sales_price";
// $sql .= "     ,f_show_sales_price(coalesce(item.status,1),item.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// $sql .= "    ,item.fp_id";
// $sql .= "    ,item.pa_id AS price_adoption_id";
// ///$sql .= "    , (case ";
// //$sql .= "        when item.pa_id is not null then item.pa_status ";  //'paのステータス'
// //$sql .= "        when  item.fm_id is not null then 10"; //採用済
// //$sql .= "        else 1 "; //未採用
// //$sql .= "        end ) as status ";
// $sql .= "    ,COALESCE(item.status, 1) AS status";
// //CSV
// $sql .= "    ,mk.name as production_maker_name ";
// $sql .= "    ,grand.dispensing_packaging_code ";
// $sql .= "    ,pu.hot_code ";
// $sql .= "    ,grand.dosage_type_division ";
// $sql .= "    ,mp.price AS medicine_price ";
// $sql .= "    ,mp.start_date AS medicine_price_start_date ";
// $sql .= "    ,item.facility_price_start_date AS facility_price_start_date ";
// $sql .= "    ,item.created_at as created_at ";
// $sql .= "    ,item.updated_at as updated_at ";
// $sql .= "    ,item.adoption_date as adoption_date ";
// $sql .= "    ,item.adoption_stop_date as adotion_stop_date ";
// $sql .= "    ,grand.production_stop_date ";
// $sql .= "    ,grand.transitional_deadline ";
// $sql .= "    ,grand.discontinuation_date ";
// $sql .= "    ,grand.discontinuing_division ";
// $sql .= "    ,vprpab.status_forward ";
// $sql .= "    ,vprpab.status_back ";
// $sql .= "    ,vprpab.status_back_text ";
// $sql .= "    ,item.optional_key1 ";
// $sql .= "    ,item.optional_key2 ";
// $sql .= "    ,item.optional_key3 ";
// $sql .= "    ,item.optional_key4 ";
// $sql .= "    ,item.optional_key5 ";
// $sql .= "    ,item.optional_key6 ";
// $sql .= "    ,item.optional_key7 ";
// $sql .= "    ,item.optional_key8 ";
// $sql .= "    ,item.hq_optional_key1 ";
// $sql .= "    ,item.hq_optional_key2 ";
// $sql .= "    ,item.hq_optional_key3 ";
// $sql .= "    ,item.hq_optional_key4 ";
// $sql .= "    ,item.hq_optional_key5 ";
// $sql .= "    ,item.hq_optional_key6 ";
// $sql .= "    ,item.hq_optional_key7 ";
// $sql .= "    ,item.hq_optional_key8 ";

// $sql .= "    ,item.trader_name ";

// $sql .= "from";
// $sql .= "    medicines as grand ";
// $sql .= "    left join ( ";
// $sql .= "       select";
// $sql .= "            hospital.*";
// $sql .= "            ,fp_honbu.sales_price as honbu_sales_price";
// $sql .= "            ,fp_honbu.purchase_price as honbu_purchase_price";
// $sql .= "            ,pa_honbu.id as honbu_pa_id";
// $sql .= "            ,fm_honbu.id as fm_honbu_id ";
// $sql .= "            , fm_honbu.optional_key1 AS hq_optional_key1 ";
// $sql .= "            , fm_honbu.optional_key2 AS hq_optional_key2 ";
// $sql .= "            , fm_honbu.optional_key3 AS hq_optional_key3 ";
// $sql .= "            , fm_honbu.optional_key4 AS hq_optional_key4 ";
// $sql .= "            , fm_honbu.optional_key5 AS hq_optional_key5 ";
// $sql .= "            , fm_honbu.optional_key6 AS hq_optional_key6 ";
// $sql .= "            , fm_honbu.optional_key7 AS hq_optional_key7 ";
// $sql .= "            , fm_honbu.optional_key8 AS hq_optional_key8 ";
// $sql .= "        from";
// $sql .= "        (select ";
// $sql .= "            fm.medicine_id";
// $sql .= "            ,fm.id as fm_id";
// $sql .= "            ,(case ";
// $sql .= "            when fm.sales_user_group_id IS NOT NULL THEN fm.sales_user_group_id";
// $sql .= "              else pa.sales_user_group_id";
//  $sql .= "           END) AS sales_user_group_id";
// $sql .= "            ,fm.maker_name AS maker_name";
// $sql .= "            , fp.user_group_id";
// $sql .= "            , fp.user_group_name";
// $sql .= "            , fp.trader_user_group_id";
// $sql .= "            , fp.trader_group_name";
// $sql .= "            , fp.start_date as facility_price_start_date ";
// $sql .= "            , fp.end_date";
// $sql .= "            , fp.sales_price  ";                 // --商品情報
// $sql .= "            , pa.id as pa_id";
// $sql .= "            , pa.deleted_at as pa_del";
// $sql .= "            , pa.comment AS comment";
// $sql .= "            , pa.status AS pa_status";
// $sql .= "            , pa.application_date AS application_date";
// $sql .= "            , pa.created_at AS created_at";
// $sql .= "            , pa.updated_at AS updated_at";
// $sql .= "            , fp.id as fp_id";
// $sql .= "            , fp.deleted_at as fp_del";
// $sql .= "            , facilities.id AS facility_id";
// $sql .= "            , facilities.name AS facility_name";
// $sql .= "            , fm.adoption_date ";
// $sql .= "            , fm.adoption_stop_date ";
// $sql .= "            , fm.optional_key1 AS optional_key1 ";
// $sql .= "            , fm.optional_key2 AS optional_key2 ";
// $sql .= "            , fm.optional_key3 AS optional_key3 ";
// $sql .= "            , fm.optional_key4 AS optional_key4 ";
// $sql .= "            , fm.optional_key5 AS optional_key5 ";
// $sql .= "            , fm.optional_key6 AS optional_key6 ";
// $sql .= "            , fm.optional_key7 AS optional_key7 ";
// $sql .= "            , fm.optional_key8 AS optional_key8 ";
// $sql .= ", (case ";
// $sql .= "		when pa.id is not null then pa.status";
// $sql .= "		when fm.id is not null and fm.is_approval = true and fp.id is not null THEN 10";
// $sql .= "		when fm.id is not null and fm.is_approval = true and fp.id is null THEN 11";
// $sql .= "		ELSE 1";
// $sql .= "		end ) as status ";
// $sql .= "            , case ";
// $sql .= "                when pa.medicine_id is null ";
// $sql .= "                    then pa.name ";
// $sql .= "                else grand.name ";
// $sql .= "                end as item_name "; //--商品名
// //$sql .= "				--規格 ";
// //$sql .= "            --JAN ";
// //$sql .= "            --単価情報 ";
// $sql .= "            , case ";
// $sql .= "                when pa.id is null ";
//  $sql .= "                   then fp.purchase_price ";
// $sql .= "                else pa.purchase_price ";
// $sql .= "                end as pur_price "; //--仕入価格
// $sql .= "            , case ";
// $sql .= "                when pa.id is null ";
// $sql .= "                    then fp.sales_price ";
// $sql .= "                else pa.sales_price ";
// $sql .= "                end as sa_price "; //--売上価格
// //$sql .= "                --本部情報";
// $sql .= "                ,gr.partner_user_group_id as honbu_id";

// $sql .= "                , case";
// $sql .= "                WHEN pa.id IS null";
// $sql .= "                THEN fp.trader_group_name";
// $sql .= "                else pa.trader_group_name";
// $sql .= "                END AS trader_name";

// $sql .= "        from ";
// $sql .= "            facility_medicines as fm ";
// $sql .= "            inner join f_show_records_facility(". $user->id . ") as fm_fsr";
// $sql .= "                on fm.sales_user_group_id=fm_fsr.user_group_id";
// $sql .= "            left join (";
// $sql .= "                select fp2.*";
// $sql .= "                , fsr2.user_group_id";
// $sql .= "                , fsr2.user_group_name";
// $sql .= "                 , fsr2.trader_user_group_id";
// $sql .= "                 , fsr2.trader_group_name";
// $sql .= "                 from facility_prices as fp2";
// $sql .= "                inner join f_show_records(". $user->id . ") as fsr2";
// $sql .= "                on fp2.sales_user_group_id = fsr2.user_group_id ";
// $sql .= "                and fp2.purchase_user_group_id = fsr2.trader_user_group_id";
// $sql .= "            ) as fp ";
// $sql .= "                on fm.medicine_id = fp.medicine_id ";
// $sql .= "                and fm.sales_user_group_id = fp.sales_user_group_id ";
// $sql .= "               and fp.start_date <= now() ";
// $sql .= "                and fp.end_date >= now() ";
// $sql .= "            left join (";
// $sql .= "                    select pa2.*, fsr_pa.trader_group_name from price_adoptions  as pa2";
// $sql .= "                    inner join f_show_records(". $user->id . ") as fsr_pa";
// $sql .= "                    on pa2.sales_user_group_id = fsr_pa.user_group_id";
// $sql .= "                    and pa2.purchase_user_group_id = fsr_pa.trader_user_group_id ";
//  $sql .= "               ) as pa "; //--未設定はnullではなくゼロ) ";
//  $sql .= "               on fm.medicine_id = pa.medicine_id ";
// $sql .= "                and fm.sales_user_group_id = pa.sales_user_group_id ";
//  $sql .= "           left join medicines as grand ";
//  $sql .= "               on fm.medicine_id = grand.id ";
//  $sql .= "           left join group_relations as gr";
//  $sql .= "               on fm.sales_user_group_id=gr.user_group_id";
//   $sql .= "          left join user_groups as facilities";
//  $sql .= "   	          on fm.sales_user_group_id=facilities.id";
//  $sql .= "       ) as hospital ";
//  $sql .= "       left join facility_medicines as fm_honbu";
//  $sql .= "               on hospital.honbu_id=fm_honbu.sales_user_group_id";
//  $sql .= "               and hospital.medicine_id=fm_honbu.medicine_id";
//  $sql .= "       left join facility_prices as fp_honbu";
//  $sql .= "           on fm_honbu.medicine_id = fp_honbu.medicine_id ";
//  $sql .= "           and fm_honbu.sales_user_group_id = fp_honbu.sales_user_group_id ";
//  $sql .= "           and fp_honbu.start_date <= now() ";
//  $sql .= "           and fp_honbu.end_date >= now() ";
//  $sql .= "       left join price_adoptions as pa_honbu";
//  $sql .= "       on fm_honbu.sales_user_group_id=pa_honbu.sales_user_group_id";
//  $sql .= "       and fm_honbu.medicine_id = pa_honbu.medicine_id";
//  $sql .= "   ) as item ";
//  $sql .= "   on grand.id=item.medicine_id";
//   $sql .= "  left join pack_units as pu";
//   $sql .= "  on grand.id =pu.medicine_id";
//   $sql .= "  LEFT JOIN medicine_prices AS mp";
//  $sql .= "   ON grand.id = mp.medicine_id";
//  $sql .= "   and mp.end_date >= now() ";
//   $sql .= "  and mp.start_date <= now() ";
//   $sql .= "  and mp.deleted_at is null ";
//   $sql .= "  left join makers as mk ";
//   $sql .= "   on grand.maker_id =  mk.id ";
//   $sql .= "  left join users as usr ";
//   $sql .= "   on usr.id = " .$user->id  ;

//   $sql .= "  left join v_privileges_row_price_adoptions_bystate as vprpab";
//   $sql .= "  on vprpab.user_group_id =  COALESCE(item.sales_user_group_id, usr.primary_user_group_id )";
//   $sql .= "  and vprpab.user_id = ".$user->id;
//   $sql .= "  and vprpab.status = COALESCE(item.status, 1 )";
//   $sql .= "   left join v_privileges_row_yakuhinshinsei as vpry";
//    $sql .= "  on vpry.user_group_id = COALESCE(item.sales_user_group_id, usr.primary_user_group_id )";
//    $sql .= "  and vpry.user_id = ".$user->id;

//   //$sql .= "  where item.pa_status = 10 "; //仮
//    $sql .= " where grand.deleted_at is NULL ";


//    // 状態
//    if (!empty($request->status)) {
//    	if(in_array(10, $request->status)) {
//    		$req_status = $request->status;
//    		$req_status[] = 11;
//    		$sql .= " and coalesce(item.status,1) in (".implode(',', $req_status).")";
//    	} else {
//    		$sql .= " and coalesce(item.status,1) in (".implode(',', $request->status).")";
//    	}

//    }

//    //採用中止日 TODO
//    if (!empty($request->stop_date)) {
//    	 $sql .= " and item.adoption_stop_date >= '".$stop_date."'";
//    }

//    // 施設
//    if (!empty($request->user_group)) {
//    	$sql .= " and coalesce(item.facility_id, item.sales_user_group_id) in (".implode(',', $request->user_group).")";
//    }
//    // 医薬品CD
//    if (!empty($request->code)) {
//    	$sql .= " and grand.code like '%".$request->code."%'";
//    }
//    // 一般名
//    if (!empty($request->popular_name)) {
//    	$sql .= " and grand.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//    }

//    // 包装薬価係数
//    if (!empty($request->coefficient)) {
//    	$sql .= " and pu.coefficient = ".$request->coefficient;
//    }

//    // 販売中止日
//    if (!empty($request->discontinuation_date)) {
//    	$sql .= " and grand.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//    }

//    $search_words = [];

//    // 全文検索
//    if (!empty($request->search)) {
//    	$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//    	foreach($search_words as $word) {
//    		$sql .= " and grand.search like '%".$word."%'";
//    	}
//    }

//    // 剤型区分
//    if (!empty($request->dosage_type_division)) {
//    	$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//    }

//    // 先発後発品区分
//    if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//    	$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//    }

//    //2019.08.26 オプション項目追加 start
//    //項目1
//    //項目1
//    if (!empty($request->optional_key1)) {
//    	$sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
//    	$sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

//    }

//    //項目2
//    if (!empty($request->optional_key2)) {
//    	$sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
//    	$sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

//    }

//    //項目3
//    if (!empty($request->optional_key3)) {
//    	$sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
//    	$sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

//    }

//    //項目4
//    if (!empty($request->optional_key4)) {
//    	$sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
//    	$sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

//    }

//    //項目5
//    if (!empty($request->optional_key5)) {
//    	$sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
//    	$sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

//    }

//    //項目6
//    if (!empty($request->optional_key6)) {
//    	$sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
//    	$sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

//    }

//    //項目7
//    if (!empty($request->optional_key7)) {
//    	$sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
//    	$sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

//    }

//    //項目8
//    if (!empty($request->optional_key8)) {
//    	$sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
//    	$sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

//    }


//    //項目1
//    if (!empty($request->hq_optional_key1)) {
//    	$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//    }
//    if (!empty($request->hq_optional_key2)) {
//    	$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//    }
//    if (!empty($request->hq_optional_key3)) {
//    	$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//    }
//    if (!empty($request->hq_optional_key4)) {
//    	$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//    }
//    if (!empty($request->hq_optional_key5)) {
//    	$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//    }
//    if (!empty($request->hq_optional_key6)) {
//    	$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//    }
//    if (!empty($request->hq_optional_key7)) {
//    	$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//    }
//    if (!empty($request->hq_optional_key8)) {
//    	$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//    }


//     	// sql katamari end//

//     	$count_sql = " select count(*) from (".$sql.") as tmp ";
//     	$all_count = \DB::select($count_sql);

//     	$count2=$all_count[0]->count;


//     	$sql .=" order by ";
//     	$sql .="  maker_name asc ";
//     	$sql .=" ,medicine_name asc ";
//     	$sql .=" ,standard_unit asc ";
//     	$sql .=" ,jan_code asc ";
//     	$sql .=" ,facility_id asc ";

//     	if ($count == -1) {
//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     	} else {
//     		if (!isset($request->page_count)){
//     			$offset=0;
//     		} else {
//     			if ($request->page == 0) {
//     				$offset=0;
//     			} else {
//     				$offset=($request->page - 1) * $request->page_count;
//     			}
//     		}

//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     		$sql .=" limit ".$per_page." offset ".$offset;

//     	}

//     	$all_rec = \DB::select($sql);
//     	$result = Medicine::query()->hydrate($all_rec);


//     	// ページ番号が指定されていなかったら１ページ目
//     	$page_num = isset($request->page) ? $request->page : 1;
//     	// ページ番号に従い、表示するレコードを切り出す
//     	$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

//     	// ページャーオブジェクトを生成
//     	$pager= new \Illuminate\Pagination\LengthAwarePaginator(
//     			$result, // ページ番号で指定された表示するレコード配列
//     			$count2, // 検索結果の全レコード総数
//     			$per_page, // 1ページ当りの表示数
//     			$page_num, // 表示するページ
//     			['path' => $request->url()] // ページャーのリンク先のURLを指定
//     			);

//     	return $pager;
//     }



	/**
	 * 採用一覧取得
	 *
	 * @param Request $request リクエスト情報
	 * @param Facility $facility 施設情報
	 * @param int $count 表示件数
	 * @param $user ユーザ情報
	 * @return
	 */
//     public function listWithApplyTest_old(Request $request, int $count, $user)
//     {
//     	$today = date('Y/m/d');
//     	$stop_date =date('Y/m/d');

//     	$apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//     	//自施設の採用、自施設の申請、自本部の採用
//     	$sql  = " (select ";
//     	$sql .= "      base.medicine_id as medicine_id ";
//     	$sql .= "     ,base.code ";
//     	$sql .= "     ,base.sales_packaging_code ";
//     	$sql .= "     ,base.generic_product_detail_devision ";
//     	$sql .= "     ,base.popular_name ";
//     	$sql .= "     ,base.jan_code as jan_code ";
//     	$sql .= "     ,base.medicine_name ";
//     	$sql .= "     ,base.standard_unit ";
//     	$sql .= "     ,base.maker_name ";
//     	$sql .= "     ,base.price as price ";
//     	$sql .= "     ,base.comment as comment ";
//     	$sql .= "     ,base.status ";
//     	$sql .= "     ,base.facility_id ";
//     	$sql .= "     ,base.facility_name ";
//     	$sql .= "     ,base.user_name ";
//     	$sql .= "     ,base.total_pack_unit ";
//     	$sql .= "     ,base.pack_unit_price as pack_unit_price ";
//     	$sql .= "     ,base.coefficient ";
//     	$sql .= "     ,base.application_date as application_date ";
//     	$sql .= "     ,f_show_trader_name(base.status,base.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//     	$sql .= "     ,base.fm_parent ";
//     	$sql .= "     ,base.fm_own ";
//     	$sql .= "     ,f_show_sales_price(base.status,base.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
//     	$sql .= "     ,base.fp_id ";
//     	$sql .= "     ,base.price_adoption_id ";
//     	//CSVダウンロード追加項目 START
//     	$sql .= "     ,base.production_maker_name ";
//     	$sql .= "     ,base.dispensing_packaging_code ";
//     	$sql .= "     ,base.hot_code ";
//     	$sql .= "     ,base.dosage_type_division ";
//     	$sql .= "     ,base.medicine_price ";
//     	$sql .= "     ,base.unit_medicine_price ";
//     	$sql .= "     ,base.medicine_price_start_date ";
//     	$sql .= "     ,base.facility_price_start_date ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,f_show_trader_name(base.status,base.trader_code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     ,base.created_at ";
//     	$sql .= "     ,base.updated_at ";
//     	$sql .= "     ,base.adoption_date ";
//     	$sql .= "     ,base.adoption_stop_date ";
//     	$sql .= "     ,base.production_stop_date ";
//     	$sql .= "     ,base.transitional_deadline ";
//     	$sql .= "     ,base.discontinuation_date ";
//     	$sql .= "     ,base.discontinuing_division ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,base.facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	//CSVダウンロード追加項目 END
//     	//$sql .= "     ,f_yakuhinshinsei_task_next(base.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_reject(base.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_withdraw(base.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//     	$sql .= "     ,vprpab.status_forward  ";
//     	$sql .= "     ,vprpab.status_back  ";
//     	$sql .= "     ,vprpab.status_back_text  ";
//     	//追加項目 START
//     	$sql .= "     ,base.optional_key1 ";
//     	$sql .= "     ,base.optional_key2 ";
//     	$sql .= "     ,base.optional_key3 ";
//     	$sql .= "     ,base.optional_key4 ";
//     	$sql .= "     ,base.optional_key5 ";
//     	$sql .= "     ,base.optional_key6 ";
//     	$sql .= "     ,base.optional_key7 ";
//     	$sql .= "     ,base.optional_key8 ";
//     	$sql .= "     ,base.hq_optional_key1 ";
//     	$sql .= "     ,base.hq_optional_key2 ";
//     	$sql .= "     ,base.hq_optional_key3 ";
//     	$sql .= "     ,base.hq_optional_key4 ";
//     	$sql .= "     ,base.hq_optional_key5 ";
//     	$sql .= "     ,base.hq_optional_key6 ";
//     	$sql .= "     ,base.hq_optional_key7 ";
//     	$sql .= "     ,base.hq_optional_key8 ";
//     	$sql .= "     ,base.facility_price_end_date ";
//     	$sql .= " from ";
//     	$sql .= "     (select distinct ";
//     	$sql .= "          medicines.id as medicine_id ";
//     	$sql .= "         ,medicines.code ";
//     	$sql .= "         ,medicines.sales_packaging_code ";
//     	$sql .= "         ,medicines.generic_product_detail_devision ";
//     	$sql .= "         ,medicines.popular_name ";
//     	$sql .= "         ,pack_units.jan_code as jan_code ";
//     	$sql .= "         ,medicines.name as medicine_name ";
//     	$sql .= "         ,medicines.standard_unit as standard_unit ";
//     	$sql .= "         ,(CASE WHEN pa_own.maker_name is null ";
//     	$sql .= "                THEN coalesce(makers.name,fm_own.maker_name,fm_hq.maker_name) ";
//     	$sql .= "                ELSE pa_own.maker_name  ";
//     	$sql .= "           END) as maker_name  ";
//     	$sql .= "         ,medicine_prices.price as price ";
//     	$sql .= "         ,pa_own.comment as comment ";

//     	$sql .= "         ,(case when pa_trader.id is null then  ";
//     	$sql .= "                   case WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is null Then 11 ";
//     	$sql .= "                   WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status =10 THEN 11";
//     	$sql .= "                   WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status <= 7 THEN pa_own.status ";
//     	$sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is null then 11"; //採用可の期限切れ
//     	$sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is not null then pa_own.status"; //採用可の期限切れ後申請
//     	$sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS not NULL AND hq_fpr.fpr_cnt > 0 then 8"; //期限切れ後に採用可
//     	$sql .= "                   WHEN fm_own.facility_price_id is not NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL and pa_own.status is not null then pa_own.status "; //本部がいない病院のパターン
//     	$sql .= "                       when fm_own_trader.id is not null then ".Task::STATUS_DONE;
//     	$sql .= "                       when fm_hq.medicine_id is not null then ".Task::STATUS_ADOPTABLE;
//     	$sql .= "                       when pa_own.status is not null then pa_own.status ";
//     	$sql .= "                       else ".Task::STATUS_UNAPPLIED;
//     	$sql .= "                   end ";
//     	$sql .= "             else case when pa_trader.status = 10 and fm_own.facility_price_id is NULL  AND own_fpr.fpr_cnt > 0 AND fm_hq.facility_price_id IS not NULL AND hq_fpr.fpr_cnt > 0 then 8"; //期限切れ後に採用可
//     	$sql .= "                   ELSE pa_trader.status ";
//     	$sql .= "                   end ";
//     	$sql .= "           END) as status ";
//     	$sql .= "         ,coalesce(facilities.id,fm_own.sales_user_group_id) as facility_id ";
//     	$sql .= "         ,coalesce(facilities.name,fm_own.facility_name) as facility_name ";
//     	$sql .= "         ,users.name as user_name ";
//     	$sql .= "         ,pack_units.total_pack_unit ";
//     	$sql .= "         ,pack_units.price as pack_unit_price ";
//     	$sql .= "         ,pack_units.coefficient ";
//     	$sql .= "         ,pa_own.application_date as application_date ";
//     	$sql .= "         ,(case when pa_trader.id is null then  ";
//     	//$sql .= "            case WHEN pa_own.id is not null then trader.name ";
//     	$sql .= "                case when fm_own_trader.id is not null then fm_own_trader.trader_name ";
//     	$sql .= "                     when fm_hq.medicine_id is not null and fm_hq.sales_price is not null then fm_hq.trader_name ";
//     	$sql .= "                     when pa_own.id is not null then trader.name ";
//     	$sql .= "                end ";
//     	$sql .= "           ELSE pa_trader.trader_name ";
//     	$sql .= "           END) as trader_name ";
// //     	$sql .= "     ,(case when pa_own.id is not null then trader.name ";
// //     	$sql .= "           else coalesce(fm_own.trader_name,fm_hq.trader_name) ";
// //     	$sql .= "      END) as trader_name ";
//     	$sql .= "         ,coalesce(fm_hq.medicine_id,0) as fm_parent ";
//     	$sql .= "         ,coalesce(fm_own.medicine_id,0) as fm_own ";
//     	$sql .= "         ,(case ";
//     	$sql .= "               when fm_own_trader.id is not null then fm_own_trader.sales_price ";
//     	//$sql .= "           when fm_own_trader.id is null AND fm_own.id is not null then fm_own.sales_price ";
//     	$sql .= "               when fm_own_trader.id is null AND fm_own_trader.id is not null then fm_own_trader.sales_price ";

//     	$sql .= "               when fm_own_trader.id is null AND fm_hq.medicine_id is not null and fm_hq.sales_price is not null then fm_hq.sales_price ";
//     	$sql .= "               when fm_own_trader.id is null AND pa_own.id is not null then pa_own.sales_price ";
//     	$sql .= "          end)";
//     	$sql .= "          as sales_price ";
//     	$sql .= "         ,coalesce(fm_own_trader.facility_price_id,fm_hq.facility_price_id) as fp_id ";
//     	$sql .= "         ,coalesce(pa_own.id,0) as price_adoption_id ";
//     	$sql .= "         ,(case when pa_trader.id is null then  ";
//     	$sql .= "                  case when fm_own_trader.id is not null then fm_own_trader.sales_user_group_id ";
//     	$sql .= "                       when fm_hq.medicine_id is not null then ".$user->primary_user_group_id;
//     	$sql .= "                       when pa_own.status is not null then pa_own.sales_user_group_id ";
//     	$sql .= "                       else ".$user->primary_user_group_id;
//     	$sql .= "                end ";
//     	$sql .= "           ELSE pa_trader.sales_user_group_id ";
//     	$sql .= "           END) as sales_user_group_id ";
//     	//CSVダウンロード用、追加項目
//     	$sql .= "         ,production_maker.name as production_maker_name ";
//     	$sql .= "         ,medicines.dispensing_packaging_code ";
//     	$sql .= "         ,pack_units.hot_code ";
//     	$sql .= "         ,medicines.dosage_type_division ";
//     	$sql .= "         ,medicine_prices.price as medicine_price ";
//     	$sql .= "         ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//     	$sql .= "         ,medicine_prices.start_date as medicine_price_start_date ";
//     	$sql .= "         ,coalesce(fm_own.facility_price_start_date,fm_hq.facility_price_start_date,fm_own_trader.facility_price_start_date) as facility_price_start_date ";
//     	//2019.07.31 facilities撲滅 start
// //     	$sql .= "         ,(case when pa_trader.id is null then  ";
// //     	$sql .= "                case when fm_own_trader.id is not null then fm_own_trader.trader_code ";
// //     	$sql .= "                     when fm_hq.medicine_id is not null then fm_hq.trader_code ";
// //     	$sql .= "                     when pa_own.id is not null then trader.code ";
// //     	$sql .= "                end ";
// //     	$sql .= "           ELSE pa_trader.trader_code ";
// //     	$sql .= "           END) as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "         ,pa_own.created_at ";
//     	$sql .= "         ,pa_own.updated_at ";
//     	$sql .= "         ,fm_own.adoption_date ";
//     	$sql .= "         ,coalesce(fm_own.adoption_stop_date,'2999/12/31') as adoption_stop_date ";
//     	$sql .= "         ,medicines.production_stop_date ";
//     	$sql .= "         ,medicines.transitional_deadline ";
//     	$sql .= "         ,medicines.discontinuation_date ";
//     	$sql .= "         ,medicines.discontinuing_division ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "         ,coalesce(facilities.code,fm_own.facility_code) as facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	//2019.08.26 オプション項目追加 start
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key1,fm_own.optional_key1) as optional_key1 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key2,fm_own.optional_key2) as optional_key2 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key3,fm_own.optional_key3) as optional_key3 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key4,fm_own.optional_key4) as optional_key4 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key5,fm_own.optional_key5) as optional_key5 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key6,fm_own.optional_key6) as optional_key6 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key7,fm_own.optional_key7) as optional_key7 ";
//     	$sql .= "         ,coalesce(fm_own_trader.optional_key8,fm_own.optional_key8) as optional_key8 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key1,'') as hq_optional_key1 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key2,'') as hq_optional_key2 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key3,'') as hq_optional_key3 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key4,'') as hq_optional_key4 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key5,'') as hq_optional_key5 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key6,'') as hq_optional_key6 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key7,'') as hq_optional_key7 ";
//     	$sql .= "         ,coalesce(fm_hq.optional_key8,'') as hq_optional_key8 ";
//     	//2019.08.26 オプション項目追加 end
//     	$sql .= "         ,coalesce(fm_own.facility_price_end_date,fm_own_trader.facility_price_end_date) as facility_price_end_date ";
//     	$sql .= "     from ";
//     	$sql .= "         medicines";
//     	//自施設の採用
//     	$sql .= "         left join ( ";
//     	$sql .= "             select ";
//     	$sql .= "                  facility_medicines.id ";
//     	//$sql .= "                 ,facility_medicines.facility_id ";
//     	$sql .= "                 ,facility_medicines.medicine_id ";
//     	$sql .= "                 ,facility_medicines.maker_name ";
//     	$sql .= "                 ,facility_medicines.adoption_date ";
//     	$sql .= "                 ,facility_medicines.adoption_stop_date ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 ,facilities.code as facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 ,facilities.name as facility_name ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 ,trader.code as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 ,trader.name as trader_name ";
//     	$sql .= "                 ,facility_prices.sales_price ";
//     	$sql .= "                 ,facility_prices.id as facility_price_id ";
//     	$sql .= "                 ,facility_medicines.sales_user_group_id ";
//     	$sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//     	$sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//     	//2019.08.26 オプション項目追加 start
//     	$sql .= "                 ,facility_medicines.optional_key1 ";
//     	$sql .= "                 ,facility_medicines.optional_key2 ";
//     	$sql .= "                 ,facility_medicines.optional_key3 ";
//     	$sql .= "                 ,facility_medicines.optional_key4 ";
//     	$sql .= "                 ,facility_medicines.optional_key5 ";
//     	$sql .= "                 ,facility_medicines.optional_key6 ";
//     	$sql .= "                 ,facility_medicines.optional_key7 ";
//     	$sql .= "                 ,facility_medicines.optional_key8 ";
//     	//2019.08.26 オプション項目追加 end
//     	$sql .= "             from ";
//     	$sql .= "                 facility_medicines ";
//     	//2019.07.31 facilities撲滅 start
// //     	$sql .= "                 left join facilities ";
// //     	$sql .= "                     on facility_medicines.facility_id=facilities.id ";
//     	$sql .= "                 left join user_groups as facilities ";
//     	$sql .= "                     on facility_medicines.sales_user_group_id=facilities.id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 left join facility_prices ";
//     	$sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//     	$sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.deleted_at is null ";
//     	//2019.07.31 facilities撲滅 start
// //     	$sql .= "                 left join facilities as trader ";
// //     	$sql .= "                     on facility_prices.trader_id=trader.id ";
// //     	$sql .= "                 left join user_groups as trader ";
// //     	$sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//     	//2019.07.31 facilities撲滅 end

//     	$sql .= "             where ";
//     	$sql .= "                 facility_medicines.deleted_at is null ";
//     	$sql .= "                 and facility_medicines.sales_user_group_id = ".$user->primary_user_group_id;
//     	$sql .= "         ) as fm_own ";
//     	$sql .= "             on medicines.id = fm_own.medicine_id ";
//     	//自施設の申請
//     	$sql .= "         left join ( ";
//     	$sql .= "             select ";
//     	$sql .= "                  price_adoptions.*  ";
//     	$sql .= "             from ";
//     	$sql .= "                 price_adoptions ";
//     	$sql .= "             where ";
//     	$sql .= "                 price_adoptions.deleted_at is null ";
//     	$sql .= "                 and price_adoptions.sales_user_group_id = ".$user->primary_user_group_id;
//     	$sql .= "         ) as pa_own ";
//     	$sql .= "             on medicines.id = pa_own.medicine_id ";
//     	//本部の採用
//     	$sql .= "         left join ( ";
//     	$sql .= "             select ";
//     	$sql .= "                  facility_medicines.id ";
//     	//$sql .= "                 ,facility_medicines.facility_id ";
//     	$sql .= "                 ,user_group.name as facility_name ";
//     	$sql .= "                 ,facility_medicines.medicine_id ";
//     	$sql .= "                 ,facility_medicines.maker_name ";
//     	$sql .= "                 ,trader.id   as trader_id ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 ,trader.code as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 ,trader.name as trader_name ";
//     	$sql .= "                 ,facility_prices.sales_price ";
//     	$sql .= "                 ,facility_prices.id as facility_price_id ";
//     	$sql .= "                 ,facility_medicines.sales_user_group_id ";
//     	$sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//     	$sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//     	//2019.08.26 オプション項目追加 start
//     	$sql .= "                 ,facility_medicines.optional_key1 ";
//     	$sql .= "                 ,facility_medicines.optional_key2 ";
//     	$sql .= "                 ,facility_medicines.optional_key3 ";
//     	$sql .= "                 ,facility_medicines.optional_key4 ";
//     	$sql .= "                 ,facility_medicines.optional_key5 ";
//     	$sql .= "                 ,facility_medicines.optional_key6 ";
//     	$sql .= "                 ,facility_medicines.optional_key7 ";
//     	$sql .= "                 ,facility_medicines.optional_key8 ";
//     	//2019.08.26 オプション項目追加 end
//     	$sql .= "             from ";
//     	$sql .= "                 facility_medicines ";
//     	$sql .= "                 left join facility_prices ";
//     	$sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//     	$sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.deleted_at is null ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 left join facilities as trader ";
//     	//$sql .= "                     on facility_prices.trader_id=trader.id ";
//     	$sql .= "                 left join user_groups as trader ";
//     	$sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 left join user_groups as user_group ";
//     	$sql .= "                     on facility_medicines.sales_user_group_id=user_group.id ";
//     	$sql .= "             where ";
//     	$sql .= "                  facility_medicines.deleted_at is null ";
//     	$sql .= "                  and facility_medicines.sales_user_group_id=".$user->primary_honbu_user_group_id;
//     	$sql .= "         ) as fm_hq ";
//     	$sql .= "             on medicines.id = fm_hq.medicine_id ";
//     	$sql .= "         inner join pack_units ";
//     	$sql .= "             on medicines.id = pack_units.medicine_id ";
//     	$sql .= "         left join makers  ";
//     	$sql .= "             on medicines.selling_agency_code = makers.id ";
//     	$sql .= "         left join makers as production_maker ";
//     	$sql .= "             on medicines.maker_id = production_maker.id ";
//     	$sql .= "         left join medicine_prices  ";
//     	$sql .= "             on medicines.id = medicine_prices.medicine_id ";
//     	$sql .= "             and medicine_prices.end_date >= '".$today."'";
//     	$sql .= "             and medicine_prices.start_date <= '".$today."'";
//     	$sql .= "             and medicine_prices.deleted_at is null ";
//     	$sql .= "         left join ( ";
//     	$sql .= "             select ";
//     	$sql .= "                  price_adoptions.* ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 ,facilities.code as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 ,facilities.name as trader_name ";
//     	$sql .= "             from ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 price_adoptions left join facilities ";
//     	//$sql .= "                     on price_adoptions.trader_id=facilities.id ";
//     	$sql .= "                 price_adoptions left join user_groups as facilities ";
//     	$sql .= "                     on price_adoptions.purchase_user_group_id=facilities.id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "             where ";
//     	$sql .= "                 price_adoptions.deleted_at is null ";
//     	$sql .= "                 and price_adoptions.sales_user_group_id = ".$user->primary_user_group_id;
//     	$sql .= "         ) as pa_trader ";
//     	$sql .= "             on fm_hq.medicine_id = pa_trader.medicine_id ";
//     	$sql .= "             and fm_hq.trader_id = pa_trader.purchase_user_group_id ";

//     	$sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//     	$sql .= "             where deleted_at is null ";
//     	$sql .= "             group by facility_medicine_id ";
//     	$sql .= "             ) as own_fpr";
//     	$sql .= "             on fm_own.id=own_fpr.facility_medicine_id ";

//     	$sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//     	$sql .= "             where deleted_at is null ";
//     	//$sql .= "             and start_date <= '".$stop_date."'";
//     	//$sql .= "             and end_date >='".$stop_date."'";
//     	$sql .= "             group by facility_medicine_id ";
//     	$sql .= "             ) as hq_fpr";
//     	$sql .= "             on fm_hq.id=hq_fpr.facility_medicine_id ";


//     	$sql .= "         left join ( ";
//     	$sql .= "             select ";
//     	$sql .= "                  facility_medicines.id ";
//     	//$sql .= "                 ,facility_medicines.facility_id ";
//     	$sql .= "                 ,facility_medicines.medicine_id ";
//     	$sql .= "                 ,facility_medicines.maker_name ";
//     	$sql .= "                 ,facilities.name as facility_name ";
//     	$sql .= "                 ,trader.id as trader_id ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 ,trader.code as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 ,trader.name as trader_name ";
//     	$sql .= "                 ,facility_prices.sales_price ";
//     	$sql .= "                 ,facility_prices.id as facility_price_id ";
//     	$sql .= "                 ,facility_medicines.sales_user_group_id ";
//     	$sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//     	$sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//     	//2019.08.26 オプション項目追加 start
//     	$sql .= "                 ,facility_medicines.optional_key1 ";
//     	$sql .= "                 ,facility_medicines.optional_key2 ";
//     	$sql .= "                 ,facility_medicines.optional_key3 ";
//     	$sql .= "                 ,facility_medicines.optional_key4 ";
//     	$sql .= "                 ,facility_medicines.optional_key5 ";
//     	$sql .= "                 ,facility_medicines.optional_key6 ";
//     	$sql .= "                 ,facility_medicines.optional_key7 ";
//     	$sql .= "                 ,facility_medicines.optional_key8 ";
//     	//2019.08.26 オプション項目追加 end
//     	$sql .= "             from ";
//     	$sql .= "                 facility_medicines ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 left join facilities ";
//     	//$sql .= "                     on facility_medicines.facility_id=facilities.id ";
//     	$sql .= "                 left join user_groups as facilities ";
//     	$sql .= "                     on facility_medicines.sales_user_group_id=facilities.id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "                 left join facility_prices ";
//     	$sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//     	$sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//     	$sql .= "                     and facility_prices.deleted_at is null ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "                 left join facilities as trader ";
//     	//$sql .= "                     on facility_prices.trader_id=trader.id ";
//     	$sql .= "                 left join user_groups as trader ";
//     	$sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "             where ";
//     	$sql .= "                 facility_medicines.deleted_at is null ";
//     	$sql .= "                 and facility_medicines.sales_user_group_id = ".$user->primary_user_group_id;
//     	$sql .= "         ) as fm_own_trader ";
//     	$sql .= "             on medicines.id = fm_own_trader.medicine_id ";
//     	$sql .= "             and fm_hq.trader_id = fm_own_trader.trader_id ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "         left join facilities  ";
//     	//$sql .= "             on facilities.id = pa_own.facility_id ";
//     	$sql .= "         left join user_groups as facilities  ";
//     	$sql .= "             on facilities.id = pa_own.sales_user_group_id ";
//     	//2019.07.31 facilities撲滅 end
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "         left join facilities as trader  ";
//     	//$sql .= "             on trader.id = pa_own.trader_id ";
//     	$sql .= "         left join user_groups as trader  ";
//     	$sql .= "             on trader.id = pa_own.purchase_user_group_id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "         left join users ";
//     	$sql .= "             on users.id = pa_own.user_id ";
//     	$sql .= "     where ";
//     	$sql .= "        medicines.deleted_at is null";

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and coalesce(facilities.id, fm_own.sales_user_group_id) in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and medicines.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pack_units.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and medicines.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	//項目1
//         	//項目1
//     	if (!empty($request->optional_key1)) {
//     		$sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
//     		$sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";

//     	}

//     	//項目2
//     	if (!empty($request->optional_key2)) {
//     		$sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
//     		$sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";

//     	}

//         //項目3
//     	if (!empty($request->optional_key3)) {
//     		$sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
//     		$sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";

//     	}

//     	//項目4
//     	if (!empty($request->optional_key4)) {
//     		$sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
//     		$sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";

//     	}

//     	//項目5
//     	if (!empty($request->optional_key5)) {
//     		$sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
//     		$sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";

//     	}

//     	//項目6
//     	if (!empty($request->optional_key6)) {
//     		$sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
//     		$sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";

//     	}

//         //項目7
//     	if (!empty($request->optional_key7)) {
//     		$sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
//     		$sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";

//     	}

//         //項目8
//     	if (!empty($request->optional_key8)) {
//     		$sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
//     		$sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";

//     	}


//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";

//     	}
//     	//2019.08.26 オプション項目追加 end


//     	$sql .= "     ) as base ";
//     	$sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .= "         on vprpab.user_group_id = base.sales_user_group_id ";
//     	$sql .= "         and vprpab.user_id = ".$user->id;
//     	$sql .= "         and vprpab.status = base.status ";

//     	$sql .= "     left join v_privileges_row_yakuhinshinsei as vpry";
//     	$sql .= "         on vpry.user_group_id = base.sales_user_group_id ";
//     	$sql .= "         and vpry.user_id = ".$user->id;

//     	$sql .= " where ";
//     	$sql .= "     1=1";
//     	$sql .= "     and (vpry.show_status1 = '1' and base.status = 1 or base.status <> 1)";

//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(10, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 11;
//     			$sql .= " and coalesce(base.status,1) in (".implode(',', $req_status).")";
//     		} else {
//     		    $sql .= " and coalesce(base.status,1) in (".implode(',', $request->status).")";
//     		}

//     	}

//     	//採用中止日
//     	if (!empty($request->stop_date)) {
//     		$sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//     	}

//     	//自施設の採用、自施設の申請、自本部の採用 END

//     	//自施設以外の採用 START
//     	$sql .=" ) ";
//     	$sql .=" union all ";
//     	$sql .=" (select ";
//     	$sql .="       medicines.id as medicine_id ";
//     	$sql .="      ,medicines.code ";
//     	$sql .="      ,medicines.sales_packaging_code ";
//     	$sql .="      ,medicines.generic_product_detail_devision ";
//     	$sql .="      ,medicines.popular_name ";
//     	$sql .="      ,pack_units.jan_code as jan_code ";
//     	$sql .="      ,medicines.name as medicine_name ";
//     	$sql .="      ,medicines.standard_unit as standard_unit ";
//     	$sql .="      ,coalesce(makers.name,fm_otr.maker_name) as maker_name ";
//     	$sql .="      ,medicine_prices.price as price ";
//     	$sql .="      ,'' as comment ";
//     	//$sql .="      ,10 as status ";
//     	$sql .="    ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//     	$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//     	$sql .="		WHEN fp_expire.med_id is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 11 "; // 通常の場合は、採用可になるが再申請の場合は前のデータがあるので期限切れ
//     	$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//     	$sql .="      else 10 ";
//     	$sql .="      end) as status ";
//     	$sql .="      ,facilities.id as facility_id ";
//     	$sql .="      ,facilities.name as facility_name ";
//     	$sql .="      ,'' as user_name ";
//     	$sql .="      ,pack_units.total_pack_unit ";
//     	$sql .="      ,pack_units.price as pack_unit_price ";
//     	$sql .="      ,pack_units.coefficient ";
//     	$sql .="      ,null as application_date ";
//     	//$sql .= "     ,f_show_trader_name(10,fm_otr.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//     	$sql .="      ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN f_show_trader_name(fm_otr.price_status,fm_otr.exp_trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//     	$sql .="      else f_show_trader_name(10,fm_otr.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//     	$sql .="      end) as trader_name ";

//     	$sql .="      ,0 as fm_parent ";
//     	$sql .="      ,0 as fm_own ";
//     	$sql .= "     ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN f_show_sales_price(fm_otr.price_status,fm_otr.exp_sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//     	$sql .= "     else f_show_sales_price(10,fm_otr.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//     	$sql .= "     end) as sales_price ";
//     	//$sql .= "     ,f_show_sales_price(10,fm_otr.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// //     	$sql .= "     ,case ";
// //     	$sql .= "          when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else facility_prices.sales_price ";
// //     	$sql .= "      end as sales_price ";
//     	$sql .="      ,fm_otr.facility_price_id as fp_id ";
//     	$sql .="      ,coalesce(fm_otr.price_adoption_id,0) as price_adoption_id ";
// 		//CSVダウンロード追加項目 START
//     	$sql .= "     ,production_maker.name as production_maker_name ";
//     	$sql .= "     ,medicines.dispensing_packaging_code ";
//     	$sql .= "     ,pack_units.hot_code ";
//     	$sql .= "     ,medicines.dosage_type_division ";
//     	$sql .= "     ,medicine_prices.price as medicine_price ";
//     	$sql .= "     ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//     	$sql .= "     ,medicine_prices.start_date as medicine_price_start_date ";
//     	$sql .= "     ,fm_otr.facility_price_start_date ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,f_show_trader_name(10,fm_otr.trader_code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     ,fm_otr.created_at ";
//     	$sql .= "     ,fm_otr.updated_at ";
//     	$sql .= "     ,fm_otr.adoption_date ";
//     	$sql .= "     ,fm_otr.adoption_stop_date ";
//     	$sql .= "     ,medicines.production_stop_date ";
//     	$sql .= "     ,medicines.transitional_deadline ";
//     	$sql .= "     ,medicines.discontinuation_date ";
//     	$sql .= "     ,medicines.discontinuing_division ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,facilities.code as facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	//CSVダウンロード追加項目 END

//     	//$sql .= "     ,f_yakuhinshinsei_task_next(10,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_reject(10,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_withdraw(10,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//     	$sql .= "     ,vprpab.status_forward  ";
//     	$sql .= "     ,vprpab.status_back  ";
//     	$sql .= "     ,vprpab.status_back_text  ";
//     	$sql .= "     ,fm_otr.optional_key1 ";
//     	$sql .= "     ,fm_otr.optional_key2 ";
//     	$sql .= "     ,fm_otr.optional_key3 ";
//     	$sql .= "     ,fm_otr.optional_key4 ";
//     	$sql .= "     ,fm_otr.optional_key5 ";
//     	$sql .= "     ,fm_otr.optional_key6 ";
//     	$sql .= "     ,fm_otr.optional_key7 ";
//     	$sql .= "     ,fm_otr.optional_key8 ";
//     	$sql .= "     ,fm_otr.hq_optional_key1 ";
//     	$sql .= "     ,fm_otr.hq_optional_key2 ";
//     	$sql .= "     ,fm_otr.hq_optional_key3 ";
//     	$sql .= "     ,fm_otr.hq_optional_key4 ";
//     	$sql .= "     ,fm_otr.hq_optional_key5 ";
//     	$sql .= "     ,fm_otr.hq_optional_key6 ";
//     	$sql .= "     ,fm_otr.hq_optional_key7 ";
//     	$sql .= "     ,fm_otr.hq_optional_key8 ";
//     	$sql .= "     ,fm_otr.facility_price_end_date ";
//     	$sql .= " from ";
//     	$sql .= "     (select distinct ";
//     	$sql .= "           facility_medicines.id ";
//     	//$sql .= "          ,facility_medicines.facility_id ";
//     	$sql .= "          ,facility_medicines.medicine_id ";
//     	$sql .= "          ,facility_medicines.maker_name ";
//     	$sql .= "          ,facility_medicines.sales_user_group_id ";
//     	$sql .= "          ,facility_prices.start_date as facility_price_start_date ";
//     	$sql .= "          ,facility_prices.end_date as facility_price_end_date ";
//     	$sql .= "          ,price_adoptions.created_at ";
//     	$sql .= "          ,price_adoptions.updated_at ";
//     	$sql .= "          ,facility_medicines.adoption_date ";
//     	$sql .= "          ,coalesce(facility_medicines.adoption_stop_date,'2999/12/31') as adoption_stop_date ";
//     	$sql .= "          ,price_adoptions.id as price_adoption_id ";

//     	$sql .= "          ,price_adoptions.status as price_status ";
//     	$sql .= "          ,price_adoptions.sales_price as exp_sales_price ";
//     	$sql .= "          ,exp_trader.name as exp_trader_name ";

//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "          ,facilities.code as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "          ,facilities.name as trader_name ";
//     	$sql .= "          ,facility_prices.sales_price ";
//     	$sql .= "          ,facility_prices.id as facility_price_id ";
//     	//2019.08.26 オプション項目追加 start
//     	$sql .= "          ,facility_medicines.optional_key1 ";
//     	$sql .= "          ,facility_medicines.optional_key2 ";
//     	$sql .= "          ,facility_medicines.optional_key3 ";
//     	$sql .= "          ,facility_medicines.optional_key4 ";
//     	$sql .= "          ,facility_medicines.optional_key5 ";
//     	$sql .= "          ,facility_medicines.optional_key6 ";
//     	$sql .= "          ,facility_medicines.optional_key7 ";
//     	$sql .= "          ,facility_medicines.optional_key8 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key1 else hq_fm.optional_key1 end as hq_optional_key1 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key2 else hq_fm.optional_key2 end as hq_optional_key2 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key3 else hq_fm.optional_key3 end as hq_optional_key3 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key4 else hq_fm.optional_key4 end as hq_optional_key4 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key5 else hq_fm.optional_key5 end as hq_optional_key5 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key6 else hq_fm.optional_key6 end as hq_optional_key6 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key7 else hq_fm.optional_key7 end as hq_optional_key7 ";
//     	$sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key8 else hq_fm.optional_key8 end as hq_optional_key8 ";
//     	$sql .= "          ,hq_fm.sales_user_group_id as hq_sales_user_group_id ";

//     	//$sql .= "          ,hq_fp.id as hq_fp_id";
//     	$sql .= "          ,own_fpr.fpr_cnt as fpr_cnt";
//     	$sql .= "          ,hq_fpr.fpr_cnt as hq_fpr_cnt";

//     	//2019.08.26 オプション項目追加 end
//     	$sql .= "     from ";
//     	$sql .= "         facility_medicines ";
//     	$sql .= "         left join facility_prices ";
//     	$sql .= "             on facility_medicines.id=facility_prices.facility_medicine_id ";
//     	$sql .= "             and facility_prices.start_date <= '".$stop_date."'";
//     	$sql .= "             and facility_prices.end_date >='".$stop_date."'";
//     	$sql .= "             and facility_prices.deleted_at is null ";
//     	$sql .= "         left join price_adoptions ";
//     	$sql .= "             on facility_medicines.sales_user_group_id=price_adoptions.sales_user_group_id ";
//     	$sql .= "             and facility_medicines.medicine_id=price_adoptions.medicine_id ";
//     	$sql .= "             and price_adoptions.deleted_at is null ";

//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "         left join facilities ";
//     	//$sql .= "             on facility_prices.trader_id=facilities.id ";
//     	$sql .= "         left join user_groups as facilities ";
//     	$sql .= "             on facility_prices.purchase_user_group_id=facilities.id ";
//     	$sql .= "         left join user_groups as exp_trader ";
//     	$sql .= "             on price_adoptions.purchase_user_group_id=exp_trader.id ";
//     	$sql .= "         left join user_groups as ug ";
//     	$sql .= "             on facility_medicines.sales_user_group_id=ug.id ";
//     	$sql .= "         left join group_relations as gr ";
//     	$sql .= "             on facility_medicines.sales_user_group_id=gr.user_group_id ";
//     	$sql .= "         left join facility_medicines as hq_fm ";
//     	$sql .= "             on hq_fm.sales_user_group_id=gr.partner_user_group_id ";
//     	$sql .= "             and hq_fm.medicine_id = facility_medicines.medicine_id ";

//     	$sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//     	$sql .= "             where deleted_at is null ";
//     	$sql .= "             group by facility_medicine_id ";
//     	$sql .= "             ) as own_fpr";
//     	$sql .= "             on facility_medicines.id=own_fpr.facility_medicine_id ";

//     	$sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//     	$sql .= "             where deleted_at is null ";
//     	$sql .= "             and start_date <= '".$stop_date."'";
//     	$sql .= "             and end_date >='".$stop_date."'";
//     	$sql .= "             group by facility_medicine_id ";
//     	$sql .= "             ) as hq_fpr";
//     	$sql .= "             on hq_fm.id=hq_fpr.facility_medicine_id ";
//     	//$sql .= "             and ug.id = facility_medicines.sales_user_group_id ";
//     	//$sql .= "             and ug.group_type = '本部' ";

//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     where ";
//     	$sql .= "         facility_medicines.deleted_at is null ";
//     	$sql .= "         and facility_medicines.sales_user_group_id <> ".$user->primary_user_group_id;
//     	$sql .= "     ) as fm_otr ";
//     	$sql .= "     inner join ( ";
//     	$sql .= "         select  ";
//     	$sql .= "             c.user_group_id  ";
//     	$sql .= "         from ";
//     	$sql .= "             user_groups as b ";
//     	$sql .= "             inner join user_group_relations as c  ";
//     	$sql .= "                 on c.user_group_id = b.id ";
//     	$sql .= "                 and c.deleted_at is null  ";
//     	$sql .= "         where ";
//     	$sql .= "             c.user_id = ".$user->id;
//     	$sql .= "     ) as user_groups ";
//     	$sql .= "         on fm_otr.sales_user_group_id = user_groups.user_group_id ";
//     	$sql .= "     left join medicines ";
//     	$sql .= "         on fm_otr.medicine_id = medicines.id  ";
//     	$sql .= "     inner join pack_units ";
//     	$sql .= "         on medicines.id = pack_units.medicine_id ";
//     	$sql .= "     left join makers ";
//     	$sql .= "         on medicines.selling_agency_code = makers.id ";
//     	$sql .= "     left join makers as production_maker ";
//     	$sql .= "         on medicines.maker_id = production_maker.id ";
//     	$sql .= "     left join medicine_prices ";
//     	$sql .= "         on medicines.id = medicine_prices.medicine_id ";
//     	$sql .= "         and medicine_prices.end_date >= '".$today."'";
//     	$sql .= "         and medicine_prices.start_date <= '".$today."'";
//     	$sql .= "         and medicine_prices.deleted_at is null ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     left join facilities ";
//     	//$sql .= "         on facilities.id = fm_otr.facility_id  ";
//     	$sql .= "     left join user_groups as facilities ";
//     	$sql .= "         on facilities.id = fm_otr.sales_user_group_id  ";
//     	$sql .= "     left join user_groups as hq_group ";
//     	$sql .= "         on hq_group.id = fm_otr.hq_sales_user_group_id  ";

//     	$sql .= "LEFT JOIN ( ";
//     	$sql .= "   SELECT";
//     	$sql .= "   distinct ";
// 		$sql .= "	 facility_medicines.medicine_id AS med_id ";
// 		$sql .= "	 ,facility_medicines.sales_user_group_id AS group_id  ";
// 		$sql .= "	FROM facility_medicines";
// 		$sql .= "  inner join facility_prices";
//     	$sql .= "   ON facility_medicines.id = facility_prices.facility_medicine_id ";
//     	$sql .= "	 AND facility_prices.start_date <='".$today."' and facility_prices.end_date >= '".$today."'";
//         $sql .= "  AND facility_prices.deleted_at is null";
//         $sql .= " WHERE facility_medicines.deleted_at is null";
//         $sql .= " ) as fp_expire";
//         $sql .= " on medicines.id=fp_expire.med_id";
//         $sql .= " AND facilities.id = fp_expire.group_id";

//     	//2019.07.31 facilities撲滅 end
// //     	$sql .= "     left join facility_prices ";
// //     	$sql .= "         on fm_otr.id = facility_prices.facility_medicine_id  ";
// //     	$sql .= "         and facility_prices.start_date <= now() ";
// //     	$sql .= "         and facility_prices.end_date >= now() ";
// //     	$sql .= "         and facility_prices.deleted_at is null ";
// //     	$sql .= "     left join facilities as trader ";
// //     	$sql .= "         on facility_prices.trader_id = trader.id ";
//     	$sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .= "         on vprpab.user_group_id = fm_otr.sales_user_group_id ";
//     	$sql .= "         and vprpab.user_id = ".$user->id;

//     	$sql .= "         and vprpab.status = (CASE WHEN fm_otr.price_status = 10 THEN 10 ";
//     	$sql .= "         WHEN  fm_otr.price_status <> 10 THEN fm_otr.price_status ";
//     	$sql .= "         ELSE 10 ";
//     	$sql .= "         END) ";

//     	$sql .= "     left join v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .= "         on vpry.user_group_id = fm_otr.sales_user_group_id ";
//     	$sql .= "         and vpry.user_id = ".$user->id;

//     	$sql .= " where ";
//     	$sql .= "    medicines.deleted_at is null";

//     	// 状態
//     	if (!empty($request->status)) {
//     		if(in_array(10, $request->status)) {
//     			$req_status = $request->status;
//     			$req_status[] = 11;
//     		} else {
//     			$req_status = $request->status;
//     		}

//     		$sql .= " and ((11 = (case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 8 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
//     		$sql .="      else 10 ";
//             $sql .="      END)" ;
//     		$sql .= " and 11 in (".implode(',', $req_status)."))" ;
//     		$sql .= " or ( 10 = (case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 8 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
//     		$sql .="      else 10 ";
//             $sql .="      END)" ;
//     		$sql .= " and 10 in (".implode(',', $req_status).")))" ;

// //     		$sql .= " and ((case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
// //     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
// //     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
// //     		$sql .="		WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
// //     		$sql .="      else 10 ";
// //     		$sql .="      END) in (".implode(',', $req_status)."))" ;

//     	}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and medicines.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pack_units.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and medicines.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	//項目1
//     	if (!empty($request->optional_key1)) {
//     		$sql .= " and optional_key1 like '%".$request->optional_key1."%'";

//     	}

//     	//項目2
//     	if (!empty($request->optional_key2)) {
//     		$sql .= " and optional_key2 like '%".$request->optional_key2."%'";

//     	}

//     	//項目3
//     	if (!empty($request->optional_key3)) {
//     		$sql .= " and optional_key3 like '%".$request->optional_key3."%'";

//     	}

//     	//項目4
//     	if (!empty($request->optional_key4)) {
//     		$sql .= " and optional_key4 like '%".$request->optional_key4."%'";

//     	}

//     	//項目5
//     	if (!empty($request->optional_key5)) {
//     		$sql .= " and optional_key5 like '%".$request->optional_key5."%'";

//     	}

//     	//項目6
//     	if (!empty($request->optional_key6)) {
//     		$sql .= " and optional_key6 like '%".$request->optional_key6."%'";

//     	}

//     	//項目7
//     	if (!empty($request->optional_key7)) {
//     		$sql .= " and optional_key7 like '%".$request->optional_key7."%'";

//     	}

//     	//項目8
//     	if (!empty($request->optional_key8)) {
//     		$sql .= " and optional_key8 like '%".$request->optional_key8."%'";

//     	}

//     	//項目1
//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and hq_optional_key1 like '%".$request->hq_optional_key1."%' ";

//     	}
//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and hq_optional_key2 like '%".$request->hq_optional_key2."%' ";

//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and hq_optional_key3 like '%".$request->hq_optional_key3."%' ";

//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and hq_optional_key4 like '%".$request->hq_optional_key4."%' ";

//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and hq_optional_key5 like '%".$request->hq_optional_key5."%' ";

//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and hq_optional_key6 like '%".$request->hq_optional_key6."%' ";

//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and hq_optional_key7 like '%".$request->hq_optional_key7."%' ";

//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and hq_optional_key8 like '%".$request->hq_optional_key8."%' ";
//     	}
//     	//2019.08.26 オプション項目追加 end
//     	//採用中止日
//     	if (!empty($request->stop_date)) {
//     		$sql .= " and fm_otr.adoption_stop_date >= '".$stop_date."'";
//     	}

//     	//自施設以外の採用 END

//     	//自施設以外の申請(採用のないもの) START
//     	$sql .=" ) ";
//     	$sql .=" union all ";
//     	$sql .=" (select ";
//     	$sql .="       medicines.id as medicine_id ";
//     	$sql .="      ,medicines.code ";
//     	$sql .="      ,medicines.sales_packaging_code ";
//     	$sql .="      ,medicines.generic_product_detail_devision ";
//     	$sql .="      ,medicines.popular_name ";
//     	$sql .="      ,pack_units.jan_code as jan_code ";
//     	$sql .="      ,medicines.name as medicine_name ";
//     	$sql .="      ,medicines.standard_unit as standard_unit ";
//     	$sql .="      ,coalesce(price_adoptions.maker_name,makers.name) as maker_name ";
//     	$sql .="      ,medicine_prices.price as price ";
//     	$sql .="      ,price_adoptions.comment as comment ";
//     	$sql .="      ,price_adoptions.status as status ";
//     	$sql .="      ,facilities.id as facility_id ";
//     	$sql .="      ,facilities.name as facility_name ";
//     	$sql .="      ,users.name as user_name ";
//     	$sql .="      ,pack_units.total_pack_unit ";
//     	$sql .="      ,pack_units.price as pack_unit_price ";
//     	$sql .="      ,pack_units.coefficient ";
//     	$sql .="      ,price_adoptions.application_date as application_date ";

//     	$sql .= "     ,f_show_trader_name(price_adoptions.status,trader.name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//     	$sql .="      ,0 as fm_parent ";
//     	$sql .="      ,0 as fm_own ";
//     	$sql .= "     ,f_show_sales_price(price_adoptions.status,price_adoptions.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// //     	$sql .= "     ,case ";
// //     	$sql .= "          when price_adoptions.status < 7 then";
// //     	$sql .= "              case when coalesce(vpry.show_sales_price_before6,0) <= 0 then null else price_adoptions.sales_price end";
// //     	$sql .= "          else ";
// //     	$sql .= "              case when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else price_adoptions.sales_price end";
// //     	$sql .= "      end as sales_price ";
//     	$sql .="      ,0 as fp_id ";
//     	$sql .="      ,price_adoptions.id as price_adoption_id ";
//     	//CSVダウンロード追加項目 START
//     	$sql .= "     ,production_maker.name as production_maker_name ";
//     	$sql .= "     ,medicines.dispensing_packaging_code ";
//     	$sql .= "     ,pack_units.hot_code ";
//     	$sql .= "     ,medicines.dosage_type_division ";
//     	$sql .= "     ,medicine_prices.price as medicine_price ";
//     	$sql .= "     ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//     	$sql .= "     ,medicine_prices.start_date as medicine_price_start_date ";
//     	$sql .= "     ,null as facility_price_start_date";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,f_show_trader_name(price_adoptions.status,trader.code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     ,price_adoptions.created_at ";
//     	$sql .= "     ,price_adoptions.updated_at ";
//     	$sql .= "     ,null as adoption_date ";
//     	$sql .= "     ,null as adoption_stop_date ";
//     	$sql .= "     ,medicines.production_stop_date ";
//     	$sql .= "     ,medicines.transitional_deadline ";
//     	$sql .= "     ,medicines.discontinuation_date ";
//     	$sql .= "     ,medicines.discontinuing_division ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,facilities.code as facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	//CSVダウンロード追加項目 END

//     	//$sql .= "     ,f_yakuhinshinsei_task_next(price_adoptions.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_reject(price_adoptions.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//     	//$sql .= "     ,f_yakuhinshinsei_task_withdraw(price_adoptions.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//     	$sql .= "     ,vprpab.status_forward  ";
//     	$sql .= "     ,vprpab.status_back  ";
//     	$sql .= "     ,vprpab.status_back_text  ";
//     	$sql .= "     ,'' as optional_key1  ";
//     	$sql .= "     ,'' as optional_key2  ";
//     	$sql .= "     ,'' as optional_key3  ";
//     	$sql .= "     ,'' as optional_key4  ";
//     	$sql .= "     ,'' as optional_key5  ";
//     	$sql .= "     ,'' as optional_key6  ";
//     	$sql .= "     ,'' as optional_key7  ";
//     	$sql .= "     ,'' as optional_key8  ";
//     	$sql .= "     ,''  as hq_optional_key1 ";
//     	$sql .= "     ,''  as hq_optional_key2 ";
//     	$sql .= "     ,''  as hq_optional_key3 ";
//     	$sql .= "     ,''  as hq_optional_key4 ";
//     	$sql .= "     ,''  as hq_optional_key5 ";
//     	$sql .= "     ,''  as hq_optional_key6 ";
//     	$sql .= "     ,''  as hq_optional_key7 ";
//     	$sql .= "     ,''  as hq_optional_key8 ";
//     	$sql .= "     ,null as facility_price_end_date";
//     	$sql .= " from ";
//     	$sql .= "     (select ";
//     	$sql .= "          price_adoptions.* ";
//     	$sql .= "      from ";
//     	$sql .= "          price_adoptions left join facility_medicines";
//     	$sql .= "              on price_adoptions.medicine_id = facility_medicines.medicine_id ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "              and price_adoptions.facility_id = facility_medicines.facility_id ";
//     	$sql .= "              and price_adoptions.sales_user_group_id = facility_medicines.sales_user_group_id ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "      where ";
//     	$sql .= "          price_adoptions.deleted_at is null";
//     	$sql .= "          and facility_medicines.id is null";
//     	$sql .= "          and price_adoptions.sales_user_group_id <> ".$user->primary_user_group_id;

//     	$sql .= "     ) as price_adoptions ";
//     	$sql .= "     inner join ( ";
//     	$sql .= "         select distinct";
//     	$sql .= "             c.user_group_id  ";
//     	$sql .= "         from";
//     	$sql .= "             user_groups as b";
//     	$sql .= "             inner join user_group_relations as c";
//     	$sql .= "                 on c.user_group_id = b.id";
//     	$sql .= "                 and c.deleted_at is null";
//     	$sql .= "         where";
//     	$sql .= "             c.user_id = ".$user->id;
//     	$sql .= "     ) as user_groups ";
//     	$sql .= "         on price_adoptions.sales_user_group_id = user_groups.user_group_id ";
//     	$sql .= "     left join medicines ";
//     	$sql .= "         on price_adoptions.medicine_id = medicines.id ";
//     	$sql .= "     inner join pack_units ";
//     	$sql .= "         on medicines.id = pack_units.medicine_id ";
//     	$sql .= "     left join makers ";
//     	$sql .= "         on medicines.selling_agency_code = makers.id ";
//     	$sql .= "     left join makers as production_maker ";
//     	$sql .= "         on medicines.maker_id = production_maker.id ";
//     	$sql .= "     left join medicine_prices ";
//     	$sql .= "         on medicines.id = medicine_prices.medicine_id ";
//     	$sql .= "         and medicine_prices.end_date >='".$today."'";
//     	$sql .= "         and medicine_prices.start_date <= '".$today."'";
//     	$sql .= "         and medicine_prices.deleted_at is null ";
//     	//2019.07.31 facilities撲滅 start
// //     	$sql .= "     left join facilities ";
// //     	$sql .= "         on facilities.id = price_adoptions.facility_id";
// //     	$sql .= "     left join facilities as trader ";
// //     	$sql .= "         on trader.id = price_adoptions.trader_id";
//     	$sql .= "     left join user_groups as facilities ";
//     	$sql .= "         on facilities.id = price_adoptions.sales_user_group_id";
//     	$sql .= "     left join user_groups as trader ";
//     	$sql .= "         on trader.id = price_adoptions.purchase_user_group_id";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     left join users ";
//     	$sql .= "         on users.id = price_adoptions.user_id ";
//     	$sql .= "     left join v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .= "         on vpry.user_group_id = price_adoptions.sales_user_group_id ";
//     	$sql .= "         and vpry.user_id = ".$user->id;

//     	$sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .= "         on vprpab.user_group_id = price_adoptions.sales_user_group_id ";
//     	$sql .= "         and vprpab.user_id = ".$user->id;
//     	$sql .= "         and vprpab.status = price_adoptions.status ";
//     	$sql .= " where ";
//     	$sql .= "    medicines.deleted_at is null";

//     	// 状態
//     	if (!empty($request->status)) {
//     		$sql .= " and coalesce(price_adoptions.status,1) in (".implode(',', $request->status).")";

//     	}

//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and medicines.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}

//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and pack_units.coefficient = ".$request->coefficient;
//     	}

//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		$sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}

//     	$search_words = [];

//     	// 全文検索
//     	if (!empty($request->search)) {
//     		$search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//     		foreach($search_words as $word) {
//     			$sql .= " and medicines.search like '%".$word."%'";
//     		}
//     	}

//     	// 剤型区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and dosage_type_division = ".$request->dosage_type_division;
//     	}

//     	// 先発後発品区分
//     	if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//     		$sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//     	}

//     	//2019.08.26 オプション項目追加 start
//         //項目1
//     	if (!empty($request->optional_key1)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目2
//     	if (!empty($request->optional_key2)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目3
//     	if (!empty($request->optional_key3)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目4
//     	if (!empty($request->optional_key4)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目5
//     	if (!empty($request->optional_key5)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目6
//     	if (!empty($request->optional_key6)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目7
//     	if (!empty($request->optional_key7)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目8
//     	if (!empty($request->optional_key8)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	//2019.08.26 オプション項目追加 end

//     	$sql .=" ) ";

//     	//新規登録分
//     	$sql .="union all (";
//     	$sql .="  select";
//     	$sql .="    medicine_id as medicine_id";
//     	$sql .="    , '' as code";
//     	$sql .="    , price_adoptions.sales_packaging_code";
//     	$sql .="    , '' as generic_product_detail_devision";
//     	$sql .="    , '' as popular_name";
//     	$sql .="    , jan_code";
//     	$sql .="    , price_adoptions.name as medicine_name";
//     	$sql .="    , standard_unit";
//     	$sql .="    , maker_name";
//     	$sql .="    , medicine_price as price";
//     	$sql .="    , comment";
//     	$sql .="    , price_adoptions.status";
//     	$sql .="    , facilities.id as facility_id";
//     	$sql .="    , facilities.name as facility_name";
//     	$sql .="    , users.name as user_name";
//     	$sql .="    , pack_unit";
//     	$sql .="    , medicine_price as pack_unit_price";
//     	$sql .="    , coefficient";
//     	$sql .="    , application_date";
//     	$sql .="    ,f_show_trader_name(price_adoptions.status,trader.name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//     	$sql .="    , 0 as fm_parent";
//     	$sql .="    , 0 as fm_own";

//     	$sql .= "   ,f_show_sales_price(price_adoptions.status,price_adoptions.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";

// //     	$sql .="    ,case ";
// //     	$sql .="         when status < 7 then";
// //     	$sql .="             case when coalesce(vpry.show_sales_price_before6,0) <= 0 then null else price_adoptions.sales_price end";
// //     	$sql .="         else ";
// //     	$sql .="             case when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else price_adoptions.sales_price end";
// //     	$sql .="     end as sales_price ";

//     	$sql .="    , 0 as fp_id";
//     	$sql .="    , price_adoptions.id as price_adoption_id";
//     	//CSVダウンロード追加項目 START
//     	$sql .= "     ,null as production_maker_name ";
//     	$sql .= "     ,null as dispensing_packaging_code ";
//     	$sql .= "     ,null as hot_code ";
//     	$sql .= "     ,null as dosage_type_division ";
//     	$sql .= "     ,price_adoptions.medicine_price as medicine_price ";
//     	$sql .= "     ,price_adoptions.pack_unit_price as unit_medicine_price ";
//     	$sql .= "     ,null as medicine_price_start_date ";
//     	$sql .= "     ,null as facility_price_start_date";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,f_show_trader_name(status,trader.code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//     	//2019.07.31 facilities撲滅 end
//     	$sql .= "     ,price_adoptions.created_at ";
//     	$sql .= "     ,price_adoptions.updated_at ";
//     	$sql .= "     ,null as adoption_date ";
//     	$sql .= "     ,null as adoption_stop_date ";
//     	$sql .= "     ,null as production_stop_date ";
//     	$sql .= "     ,null as transitional_deadline ";
//     	$sql .= "     ,null as discontinuation_date ";
//     	$sql .= "     ,null as discontinuing_division ";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .= "     ,facilities.code as facility_code ";
//     	//2019.07.31 facilities撲滅 end
//     	//CSVダウンロード追加項目 END
//     	//$sql .="    ,f_yakuhinshinsei_task_next(price_adoptions.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//     	//$sql .="    ,f_yakuhinshinsei_task_reject(price_adoptions.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//     	//$sql .="    ,f_yakuhinshinsei_task_withdraw(price_adoptions.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//     	$sql .="    ,vprpab.status_forward  ";
//     	$sql .="    ,vprpab.status_back  ";
//     	$sql .="    ,vprpab.status_back_text  ";
//     	$sql .="    ,'' as optional_key1  ";
//     	$sql .="    ,'' as optional_key2  ";
//     	$sql .="    ,'' as optional_key3  ";
//     	$sql .="    ,'' as optional_key4  ";
//     	$sql .="    ,'' as optional_key5  ";
//     	$sql .="    ,'' as optional_key6  ";
//     	$sql .="    ,'' as optional_key7  ";
//     	$sql .="    ,'' as optional_key8  ";
//     	$sql .= "     ,''  as hq_optional_key1 ";
//     	$sql .= "     ,''  as hq_optional_key2 ";
//     	$sql .= "     ,''  as hq_optional_key3 ";
//     	$sql .= "     ,''  as hq_optional_key4 ";
//     	$sql .= "     ,''  as hq_optional_key5 ";
//     	$sql .= "     ,''  as hq_optional_key6 ";
//     	$sql .= "     ,''  as hq_optional_key7 ";
//     	$sql .= "     ,''  as hq_optional_key8 ";
//     	$sql .= "     ,null as facility_price_end_date";
//     	$sql .="  from";
//     	$sql .="    price_adoptions";
//     	$sql .="    inner join ( ";
//     	$sql .="        select distinct ";
//     	$sql .="            c.user_group_id ";
//     	$sql .="        from ";
//     	$sql .="            user_groups as b ";
//     	$sql .="            inner join user_group_relations as c ";
//     	$sql .="                on c.user_group_id = b.id ";
//     	$sql .="        where ";
//     	$sql .="            c.user_id = ".$user->id;
//     	$sql .="    ) as user_groups ";
//     	$sql .="        on price_adoptions.sales_user_group_id = user_groups.user_group_id  ";
//     	$sql .="    left join makers";
//     	$sql .="      on price_adoptions.maker_id = makers.id";
//     	//2019.07.31 facilities撲滅 start
//     	//$sql .="    left join facilities";
//     	//$sql .="      on facilities.id = price_adoptions.facility_id";
//     	//$sql .="    left join facilities as trader";
//     	//$sql .="      on trader.id = price_adoptions.trader_id";
//     	$sql .="    left join user_groups as facilities";
//     	$sql .="      on facilities.id = price_adoptions.sales_user_group_id";
//     	$sql .="    left join user_groups as trader";
//     	$sql .="      on trader.id = price_adoptions.purchase_user_group_id";

//     	//2019.07.31 facilities撲滅 end

//     	$sql .="    left join users";
//     	$sql .="      on users.id = price_adoptions.user_id";
//     	$sql .="    left join v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .="      on vpry.user_group_id = price_adoptions.sales_user_group_id ";
//     	$sql .="      and vpry.user_id = ".$user->id;

//     	$sql .= "   left join v_privileges_row_price_adoptions_bystate as vprpab ";
//     	$sql .= "     on vprpab.user_group_id = price_adoptions.sales_user_group_id ";
//     	$sql .= "     and vprpab.user_id = ".$user->id;
//     	$sql .= "     and vprpab.status = price_adoptions.status ";

//     	$sql .="  where";
//     	$sql .="    price_adoptions.deleted_at is null";
//     	$sql .="    and price_adoptions.medicine_id is null";

//     	// 検索条件
//     	//
//     	if (!empty($request->status)) {
//     		$sql .= " and coalesce(price_adoptions.status,1) in (".implode(',', $request->status).")";
//     	}
//     	// 施設
//     	if (!empty($request->user_group)) {
//     		$sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//     	}
//     	// 医薬品CD
//     	if (!empty($request->code)) {
//     		$sql .= " and price_adoptions.status = -1";
//     		//$sql .= " and price_adoptions.code like '%".$request->code."%'";
//     	}
//     	// 一般名
//     	if (!empty($request->popular_name)) {
//     		$sql .= " and price_adoptions.status = -1";
//     		//$sql .= " and popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//     	}
//     	// 販売中止日
//     	if (!empty($request->discontinuation_date)) {
//     		//$sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//     	}
//     	// 包装薬価係数
//     	if (!empty($request->coefficient)) {
//     		$sql .= " and price_adoptions.coefficient = ".$request->coefficient;
//     	}
//     	// 全文検索
//     	if (!empty($request->search)) {
//     		foreach($search_words as $word) {
//     			$sql .= " and price_adoptions.search like '%".$word."%'";
//     		}
//     	}

//     	// 先発後発品区分
//     	if (!empty($request->generic_product_detail_devision)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	// 剤形区分
//     	if (!empty($request->dosage_type_division)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//2019.08.26 オプション項目追加 start
//     	//項目1
//     	if (!empty($request->optional_key1)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目2
//     	if (!empty($request->optional_key2)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目3
//     	if (!empty($request->optional_key3)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目4
//     	if (!empty($request->optional_key4)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目5
//     	if (!empty($request->optional_key5)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目6
//     	if (!empty($request->optional_key6)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目7
//     	if (!empty($request->optional_key7)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//項目8
//     	if (!empty($request->optional_key8)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	if (!empty($request->hq_optional_key1)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	if (!empty($request->hq_optional_key2)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key3)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key4)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key5)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key6)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key7)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}
//     	if (!empty($request->hq_optional_key8)) {
//     		$sql .= " and price_adoptions.status = -1";
//     	}

//     	//2019.08.26 オプション項目追加 end
//     	$sql .=" ) ";

//     	$count_sql = " select count(*) from (".$sql.") as tmp ";
//     	$all_count = \DB::select($count_sql);

//     	$count2=$all_count[0]->count;

//     	$sql .=" order by ";
//     	$sql .="  maker_name asc ";
//     	$sql .=" ,medicine_name asc ";
//     	$sql .=" ,standard_unit asc ";
//     	$sql .=" ,jan_code asc ";
//     	$sql .=" ,facility_id asc ";

//     	if ($count == -1) {
//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     	} else {
//     		if (!isset($request->page_count)){
//     			$offset=0;
//     		} else {
//     			if ($request->page == 0) {
//     				$offset=0;
//     			} else {
//     				$offset=($request->page - 1) * $request->page_count;
//     			}
//     		}

//     		$per_page = isset($request->page_count) ? $request->page_count : 1;
//     		$sql .=" limit ".$per_page." offset ".$offset;

//     	}

//     	$all_rec = \DB::select($sql);
//     	$result = Medicine::query()->hydrate($all_rec);


//     	// ページ番号が指定されていなかったら１ページ目
//     	$page_num = isset($request->page) ? $request->page : 1;
//     	// ページ番号に従い、表示するレコードを切り出す
//     	$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

//     	// ページャーオブジェクトを生成
//     	$pager= new \Illuminate\Pagination\LengthAwarePaginator(
//     			$result, // ページ番号で指定された表示するレコード配列
//     			$count2, // 検索結果の全レコード総数
//     			$per_page, // 1ページ当りの表示数
//     			$page_num, // 表示するページ
//     			['path' => $request->url()] // ページャーのリンク先のURLを指定
//     			);

//     	return $pager;
//     }

    /*
     * 承認申請一覧
     */
//     public function listWithApply2($medicine_id, $user, $count, $fp_id=null)
//     {
//         if(isHospital()){
//             $facility = $user->facility->parent();
//         }else{
//             $facility = $user->facility;
//         }
//         // サブクエリ
//         $fm = new FacilityMedicine();

//         $query = $this->select(\DB::raw('medicines.id as medicine_id
//                             ,medicines.code
//                             ,medicines.popular_name
//                             ,medicines.name as medicine_name
//                             ,medicines.standard_unit as standard_unit
//                             ,fm.comment as comment
//                             ,facilities.name as facility_name
//                             ,users.name as user_name
//                             ,fm.adoption_date
//                             ,fm.adoption_stop_date
//                             ,facility_prices.sales_price'
//                             ))
//                 ->leftJoinSub($fm->getFacility($facility), 'fm', function($join){
//                     $join->on('medicines.id', '=', 'fm.medicine_id');
//                 })
//                 ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'fm.id')
//                 ->leftJoin('facilities', 'facilities.id', '=', 'fm.facility_id')
//                 ->leftJoin('users', 'users.id', '=', 'fm.user_id');

//         //本部薬品の業者IDで検索
//         if(!empty($fp_id)){
//             $fp_parent = \DB::table('facility_prices')->select('id','trader_id')->where('id', $fp_id)->where('deleted_at', null)->first();
//             if(!empty($fp_parent)){
//                 $query->where('facility_prices.trader_id', $fp_parent->trader_id);
//             }
//         }
//         $query->where('fm.medicine_id', $medicine_id);
//         $query->whereNull('facility_prices.deleted_at')->orderBy('facility_name', 'asc');

//         return $query->paginate($count);
//     }

    /*
     * 申請ステータスに売上・仕入表示権限を返す
     */
//     public function getShowPriceApply_old($user_id,$user_group_id,$status)
//     {
// 		if (empty($status)) {
// 			$status=1;
// 		}

//     	$sql  = "";
//     	$sql .= " select ";
//     	$sql .= "     case ";
//     	$sql .= "          when ".$status." < ". Task::STATUS_UNCONFIRMED . " then";
//     	//$sql .= "          when ".$status." < 7 then"; //7:採用承認
// 	   	$sql .= "              coalesce(vpry.show_sales_price_before6,0) ";
//     	$sql .= "          else ";
//     	$sql .= "              coalesce(vpry.show_sales_price_over6,0) ";
//     	$sql .= "     end as sales_price_kengen ";
//    		$sql .= "    ,coalesce(vpry.show_purchase_price,0)     as purchase_price_kengen";
//    		$sql .= "    ,coalesce(vpry.edit_sales_price_over6,0)  as edit_sales_price_kengen";
//    		$sql .= "    ,coalesce(vpry.edit_purchase_price_over6,0) as edit_purchase_price_kengen";
//    		$sql .= " from ";
//     	$sql .= "      v_privileges_row_yakuhinshinsei as vpry ";
//     	$sql .= " where ";
//     	$sql .= "      vpry.user_id = ".$user_id;
//     	$sql .= "      and vpry.user_group_id =".$user_group_id;

//     	$all_rec = \DB::select($sql);

//     	if (empty($all_rec)) {
//     		$all_rec = [];

//     		$stdObj = new \stdClass();
//     		$stdObj->sales_price_kengen = 0;
//     		$stdObj->purchase_price_kengen = 0;
//     		$all_rec[] = $stdObj;

//     	}

//     	return $all_rec;

//     }

    /*
     * 採用申請 メール送付先取得
     */
//     public function getEmailApply_old($status,$user_group_id, $exit_user_group_id = null) {
//         $sql  = " select ";
//         $sql .= "     a.email ";
//         $sql .= " from  ";
//         $sql .= " (select ";
//         $sql .= "      a.status_forward ";
//         $sql .= "     ,a.status_back ";
//         $sql .= "     ,a.status_back_text ";
//         $sql .= "     ,b.email ";
//         $sql .= "  from ";
//         $sql .= "     v_privileges_row_price_adoptions_bystate as a ";
//         $sql .= "     inner join users as b ";
//         $sql .= "          on a.user_id = b.id";
//         $sql .= "          and b.deleted_at is null ";
//         $sql .= "  where ";
//         $sql .= "      b.is_adoption_mail = true";
//         $sql .= "      and a.user_group_id = ".$user_group_id;
//         $sql .= "      and a.status = ".$status;

//         if (!empty($exit_user_group_id)) {
//             $sql .= "      and b.primary_user_group_id <> ".$exit_user_group_id;
//         }


//         $sql .= " ) as a ";
//         $sql .= " where  ";
//         $sql .= "     a.status_forward > 0 ";
//         if (env( 'ENV_TYPE' ) === 'staging') {
//             $sql .= " and (a.email like '%bunkaren.or.jp' ";
//             $sql .= "   or a.email like '%k-on.co.jp' ";
//             $sql .= "   or a.email like '%example.com') ";
//         }

//         $sql .= " group by  ";
//         $sql .= "     a.email ";


//         $all_rec = \DB::select($sql);

//         if (empty($all_rec)) {
//             $all_rec = [];

//             $stdObj = new \stdClass();
//             $stdObj->email = "";

//             $all_rec[] = $stdObj;
//         }

//         return $all_rec;

//     }
    /*
     * 申請可能なグループを返す
     */
//     public function getApplyGroupList_old($user_id, $user_group_id, $status) {
//         $sql  = " select ";
//         $sql .= "      a.user_group_id ";
//         $sql .= "     ,user_groups.name ";
//         $sql .= " from ";
//         $sql .= " (select ";
//         $sql .= "      user_group_id ";
//         $sql .= "     ,status_forward ";
//         $sql .= "     ,status_back ";
//         $sql .= "     ,status_back_text ";
//         $sql .= "  from ";
//         $sql .= "     v_privileges_row_price_adoptions_bystate as a ";
//         $sql .= "  where ";
//         $sql .= "      a.user_id = ".$user_id;
//         $sql .= "      and a.status = ".$status;
//         $sql .= "      and a.status_forward > 0 ";
//         $sql .= " ) as a ";
//         $sql .= " inner join user_groups ";
//         $sql .= "     on a.user_group_id = user_groups.id ";
//         $sql .= "     and user_groups.deleted_at is null ";
//         $sql .= " group by ";
//         $sql .= "      a.user_group_id ";
//         $sql .= "     ,user_groups.name ";

//         $all_rec = \DB::select($sql);

//         if (empty($all_rec)) {
//             $all_rec = [];
//         }

//         return $all_rec;

//     }

    /**
     * 採用一覧取得
     *
     * @param Request $request リクエスト情報
     * @param Facility $facility 施設情報
     * @param int $count 表示件数
     * @param $user ユーザ情報
     * @return
     */
//     public function listWithApplyTest_old(Request $request, int $count, $user)
//     {
//         $today = date('Y/m/d');
//         $stop_date =date('Y/m/d');

//         $apply_privilege=$this->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);

//         //自施設の採用、自施設の申請、自本部の採用
//         $sql  = " (select ";
//         $sql .= "      base.medicine_id as medicine_id ";
//         $sql .= "     ,base.code ";
//         $sql .= "     ,base.sales_packaging_code ";
//         $sql .= "     ,base.generic_product_detail_devision ";
//         $sql .= "     ,base.popular_name ";
//         $sql .= "     ,base.jan_code as jan_code ";
//         $sql .= "     ,base.medicine_name ";
//         $sql .= "     ,base.standard_unit ";
//         $sql .= "     ,base.maker_name ";
//         $sql .= "     ,base.price as price ";
//         $sql .= "     ,base.comment as comment ";
//         $sql .= "     ,base.status ";
//         $sql .= "     ,base.facility_id ";
//         $sql .= "     ,base.facility_name ";
//         $sql .= "     ,base.user_name ";
//         $sql .= "     ,base.total_pack_unit ";
//         $sql .= "     ,base.pack_unit_price as pack_unit_price ";
//         $sql .= "     ,base.coefficient ";
//         $sql .= "     ,base.application_date as application_date ";
//         $sql .= "     ,f_show_trader_name(base.status,base.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//         $sql .= "     ,base.fm_parent ";
//         $sql .= "     ,base.fm_own ";
//         $sql .= "     ,f_show_sales_price(base.status,base.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
//         $sql .= "     ,base.fp_id ";
//         $sql .= "     ,base.price_adoption_id ";
//         //CSVダウンロード追加項目 START
//         $sql .= "     ,base.production_maker_name ";
//         $sql .= "     ,base.dispensing_packaging_code ";
//         $sql .= "     ,base.hot_code ";
//         $sql .= "     ,base.dosage_type_division ";
//         $sql .= "     ,base.medicine_price ";
//         $sql .= "     ,base.unit_medicine_price ";
//         $sql .= "     ,base.medicine_price_start_date ";
//         $sql .= "     ,base.facility_price_start_date ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,f_show_trader_name(base.status,base.trader_code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "     ,base.created_at ";
//         $sql .= "     ,base.updated_at ";
//         $sql .= "     ,base.adoption_date ";
//         $sql .= "     ,base.adoption_stop_date ";
//         $sql .= "     ,base.production_stop_date ";
//         $sql .= "     ,base.transitional_deadline ";
//         $sql .= "     ,base.discontinuation_date ";
//         $sql .= "     ,base.discontinuing_division ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,base.facility_code ";
//         //2019.07.31 facilities撲滅 end
//         //CSVダウンロード追加項目 END
//         //$sql .= "     ,f_yakuhinshinsei_task_next(base.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//         //$sql .= "     ,f_yakuhinshinsei_task_reject(base.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//         //$sql .= "     ,f_yakuhinshinsei_task_withdraw(base.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//         $sql .= "     ,vprpab.status_forward  ";
//         $sql .= "     ,vprpab.status_back  ";
//         $sql .= "     ,vprpab.status_back_text  ";
//         //追加項目 START
//         $sql .= "     ,base.optional_key1 ";
//         $sql .= "     ,base.optional_key2 ";
//         $sql .= "     ,base.optional_key3 ";
//         $sql .= "     ,base.optional_key4 ";
//         $sql .= "     ,base.optional_key5 ";
//         $sql .= "     ,base.optional_key6 ";
//         $sql .= "     ,base.optional_key7 ";
//         $sql .= "     ,base.optional_key8 ";
//         $sql .= "     ,base.hq_optional_key1 ";
//         $sql .= "     ,base.hq_optional_key2 ";
//         $sql .= "     ,base.hq_optional_key3 ";
//         $sql .= "     ,base.hq_optional_key4 ";
//         $sql .= "     ,base.hq_optional_key5 ";
//         $sql .= "     ,base.hq_optional_key6 ";
//         $sql .= "     ,base.hq_optional_key7 ";
//         $sql .= "     ,base.hq_optional_key8 ";
//         $sql .= "     ,base.facility_price_end_date ";
//         $sql .= " from ";
//         $sql .= "     (select distinct ";
//         $sql .= "          medicines.id as medicine_id ";
//         $sql .= "         ,medicines.code ";
//         $sql .= "         ,medicines.sales_packaging_code ";
//         $sql .= "         ,medicines.generic_product_detail_devision ";
//         $sql .= "         ,medicines.popular_name ";
//         $sql .= "         ,pack_units.jan_code as jan_code ";
//         $sql .= "         ,medicines.name as medicine_name ";
//         $sql .= "         ,medicines.standard_unit as standard_unit ";
//         $sql .= "         ,(CASE WHEN pa_own.maker_name is null ";
//         $sql .= "                THEN coalesce(makers.name,fm_own.maker_name,fm_hq.maker_name) ";
//         $sql .= "                ELSE pa_own.maker_name  ";
//         $sql .= "           END) as maker_name  ";
//         $sql .= "         ,medicine_prices.price as price ";
//         $sql .= "         ,pa_own.comment as comment ";

//         $sql .= "         ,(case when pa_trader.id is null then  ";
//         $sql .= "                   case WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is null Then 11 ";
//         $sql .= "                   WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status =10 THEN 11";
//         $sql .= "                   WHEN fm_own.facility_price_id is NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status <= 7 THEN pa_own.status ";
//         $sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is null then 11"; //採用可の期限切れ
//         $sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS NULL AND hq_fpr.fpr_cnt > 0 and pa_own.status is not null then pa_own.status"; //採用可の期限切れ後申請
//         $sql .= "                   when fm_own.facility_price_id is NULL AND fm_hq.facility_price_id IS not NULL AND hq_fpr.fpr_cnt > 0 then 8"; //期限切れ後に採用可
//         $sql .= "                   WHEN fm_own.facility_price_id is not NULL AND own_fpr.fpr_cnt > 0 and fm_hq.facility_price_id IS NULL and pa_own.status is not null then pa_own.status "; //本部がいない病院のパターン
//         $sql .= "                       when fm_own_trader.id is not null then ".Task::STATUS_DONE;
//         $sql .= "                       when fm_hq.medicine_id is not null then ".Task::STATUS_ADOPTABLE;
//         $sql .= "                       when pa_own.status is not null then pa_own.status ";
//         $sql .= "                       else ".Task::STATUS_UNAPPLIED;
//         $sql .= "                   end ";
//         $sql .= "             else case when pa_trader.status = 10 and fm_own.facility_price_id is NULL  AND own_fpr.fpr_cnt > 0 AND fm_hq.facility_price_id IS not NULL AND hq_fpr.fpr_cnt > 0 then 8"; //期限切れ後に採用可
//         $sql .= "                   ELSE pa_trader.status ";
//         $sql .= "                   end ";
//         $sql .= "           END) as status ";
//         $sql .= "         ,coalesce(facilities.id,fm_own.sales_user_group_id) as facility_id ";
//         $sql .= "         ,coalesce(facilities.name,fm_own.facility_name) as facility_name ";
//         $sql .= "         ,users.name as user_name ";
//         $sql .= "         ,pack_units.total_pack_unit ";
//         $sql .= "         ,pack_units.price as pack_unit_price ";
//         $sql .= "         ,pack_units.coefficient ";
//         $sql .= "         ,pa_own.application_date as application_date ";
//         $sql .= "         ,(case when pa_trader.id is null then  ";
//         //$sql .= "            case WHEN pa_own.id is not null then trader.name ";
//         $sql .= "                case when fm_own_trader.id is not null then fm_own_trader.trader_name ";
//         $sql .= "                     when fm_hq.medicine_id is not null and fm_hq.sales_price is not null then fm_hq.trader_name ";
//         $sql .= "                     when pa_own.id is not null then trader.name ";
//         $sql .= "                end ";
//         $sql .= "           ELSE pa_trader.trader_name ";
//         $sql .= "           END) as trader_name ";
//          /*
//         $sql .= "     ,(case when pa_own.id is not null then trader.name ";
//         $sql .= "           else coalesce(fm_own.trader_name,fm_hq.trader_name) ";
//         $sql .= "      END) as trader_name ";
// */
//         $sql .= "         ,coalesce(fm_hq.medicine_id,0) as fm_parent ";
//         $sql .= "         ,coalesce(fm_own.medicine_id,0) as fm_own ";
//         $sql .= "         ,(case ";
//         $sql .= "               when fm_own_trader.id is not null then fm_own_trader.sales_price ";
//         //$sql .= "           when fm_own_trader.id is null AND fm_own.id is not null then fm_own.sales_price ";
//         $sql .= "               when fm_own_trader.id is null AND fm_own_trader.id is not null then fm_own_trader.sales_price ";

//         $sql .= "               when fm_own_trader.id is null AND fm_hq.medicine_id is not null and fm_hq.sales_price is not null then fm_hq.sales_price ";
//         $sql .= "               when fm_own_trader.id is null AND pa_own.id is not null then pa_own.sales_price ";
//         $sql .= "          end)";
//         $sql .= "          as sales_price ";
//         $sql .= "         ,coalesce(fm_own_trader.facility_price_id,fm_hq.facility_price_id) as fp_id ";
//         $sql .= "         ,coalesce(pa_own.id,0) as price_adoption_id ";
//         $sql .= "         ,(case when pa_trader.id is null then  ";
//         $sql .= "                  case when fm_own_trader.id is not null then fm_own_trader.sales_user_group_id ";
//         $sql .= "                       when fm_hq.medicine_id is not null then ".$user->primary_user_group_id;
//         $sql .= "                       when pa_own.status is not null then pa_own.sales_user_group_id ";
//         $sql .= "                       else ".$user->primary_user_group_id;
//         $sql .= "                end ";
//         $sql .= "           ELSE pa_trader.sales_user_group_id ";
//         $sql .= "           END) as sales_user_group_id ";
//         //CSVダウンロード用、追加項目
//         $sql .= "         ,production_maker.name as production_maker_name ";
//         $sql .= "         ,medicines.dispensing_packaging_code ";
//         $sql .= "         ,pack_units.hot_code ";
//         $sql .= "         ,medicines.dosage_type_division ";
//         $sql .= "         ,medicine_prices.price as medicine_price ";
//         $sql .= "         ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//         $sql .= "         ,medicine_prices.start_date as medicine_price_start_date ";
//         $sql .= "         ,coalesce(fm_own.facility_price_start_date,fm_hq.facility_price_start_date,fm_own_trader.facility_price_start_date) as facility_price_start_date ";
//         //2019.07.31 facilities撲滅 start
//         /*
//         $sql .= "         ,(case when pa_trader.id is null then  ";
//         $sql .= "                case when fm_own_trader.id is not null then fm_own_trader.trader_code ";
//         $sql .= "                     when fm_hq.medicine_id is not null then fm_hq.trader_code ";
//         $sql .= "                     when pa_own.id is not null then trader.code ";
//         $sql .= "                end ";
//         $sql .= "           ELSE pa_trader.trader_code ";
//         $sql .= "           END) as trader_code ";
//         */
//         //2019.07.31 facilities撲滅 end
//         $sql .= "         ,pa_own.created_at ";
//         $sql .= "         ,pa_own.updated_at ";
//         $sql .= "         ,fm_own.adoption_date ";
//         $sql .= "         ,coalesce(fm_own.adoption_stop_date,'2999/12/31') as adoption_stop_date ";
//         $sql .= "         ,medicines.production_stop_date ";
//         $sql .= "         ,medicines.transitional_deadline ";
//         $sql .= "         ,medicines.discontinuation_date ";
//         $sql .= "         ,medicines.discontinuing_division ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "         ,coalesce(facilities.code,fm_own.facility_code) as facility_code ";
//         //2019.07.31 facilities撲滅 end
//         //2019.08.26 オプション項目追加 start
//         $sql .= "         ,coalesce(fm_own_trader.optional_key1,fm_own.optional_key1) as optional_key1 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key2,fm_own.optional_key2) as optional_key2 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key3,fm_own.optional_key3) as optional_key3 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key4,fm_own.optional_key4) as optional_key4 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key5,fm_own.optional_key5) as optional_key5 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key6,fm_own.optional_key6) as optional_key6 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key7,fm_own.optional_key7) as optional_key7 ";
//         $sql .= "         ,coalesce(fm_own_trader.optional_key8,fm_own.optional_key8) as optional_key8 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key1,'') as hq_optional_key1 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key2,'') as hq_optional_key2 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key3,'') as hq_optional_key3 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key4,'') as hq_optional_key4 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key5,'') as hq_optional_key5 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key6,'') as hq_optional_key6 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key7,'') as hq_optional_key7 ";
//         $sql .= "         ,coalesce(fm_hq.optional_key8,'') as hq_optional_key8 ";
//         //2019.08.26 オプション項目追加 end
//         $sql .= "         ,coalesce(fm_own.facility_price_end_date,fm_own_trader.facility_price_end_date) as facility_price_end_date ";
//         $sql .= "     from ";
//         $sql .= "         medicines";
//         //自施設の採用
//         $sql .= "         left join ( ";
//         $sql .= "             select ";
//         $sql .= "                  facility_medicines.id ";
//         //$sql .= "                 ,facility_medicines.facility_id ";
//         $sql .= "                 ,facility_medicines.medicine_id ";
//         $sql .= "                 ,facility_medicines.maker_name ";
//         $sql .= "                 ,facility_medicines.adoption_date ";
//         $sql .= "                 ,facility_medicines.adoption_stop_date ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 ,facilities.code as facility_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 ,facilities.name as facility_name ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 ,trader.code as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 ,trader.name as trader_name ";
//         $sql .= "                 ,facility_prices.sales_price ";
//         $sql .= "                 ,facility_prices.id as facility_price_id ";
//         $sql .= "                 ,facility_medicines.sales_user_group_id ";
//         $sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//         $sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//         //2019.08.26 オプション項目追加 start
//         $sql .= "                 ,facility_medicines.optional_key1 ";
//         $sql .= "                 ,facility_medicines.optional_key2 ";
//         $sql .= "                 ,facility_medicines.optional_key3 ";
//         $sql .= "                 ,facility_medicines.optional_key4 ";
//         $sql .= "                 ,facility_medicines.optional_key5 ";
//         $sql .= "                 ,facility_medicines.optional_key6 ";
//         $sql .= "                 ,facility_medicines.optional_key7 ";
//         $sql .= "                 ,facility_medicines.optional_key8 ";
//         //2019.08.26 オプション項目追加 end
//         $sql .= "             from ";
//         $sql .= "                 facility_medicines ";
//         //2019.07.31 facilities撲滅 start
//         /*
//         $sql .= "                 left join facilities ";
//         $sql .= "                     on facility_medicines.facility_id=facilities.id ";
//         */
//         $sql .= "                 left join user_groups as facilities ";
//         $sql .= "                     on facility_medicines.sales_user_group_id=facilities.id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 left join facility_prices ";
//         $sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//         $sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//         $sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//         $sql .= "                     and facility_prices.deleted_at is null ";
//         //2019.07.31 facilities撲滅 start
//         /*
//         $sql .= "                 left join facilities as trader ";
//         $sql .= "                     on facility_prices.trader_id=trader.id ";
//         */
//         $sql .= "                 left join user_groups as trader ";
//         $sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//         //2019.07.31 facilities撲滅 end

//         $sql .= "             where ";
//         $sql .= "                 facility_medicines.deleted_at is null ";
//         $sql .= "                 and facility_medicines.sales_user_group_id = ".$user->primary_user_group_id;
//         $sql .= "         ) as fm_own ";
//         $sql .= "             on medicines.id = fm_own.medicine_id ";
//         //自施設の申請
//         $sql .= "         left join ( ";
//         $sql .= "             select ";
//         $sql .= "                  price_adoptions.*  ";
//         $sql .= "             from ";
//         $sql .= "                 price_adoptions ";
//         $sql .= "             where ";
//         $sql .= "                 price_adoptions.deleted_at is null ";
//         $sql .= "                 and price_adoptions.sales_user_group_id = ".$user->primary_user_group_id;
//         $sql .= "         ) as pa_own ";
//         $sql .= "             on medicines.id = pa_own.medicine_id ";
//         //本部の採用
//         $sql .= "         left join ( ";
//         $sql .= "             select ";
//         $sql .= "                  facility_medicines.id ";
//         //$sql .= "                 ,facility_medicines.facility_id ";
//         $sql .= "                 ,user_group.name as facility_name ";
//         $sql .= "                 ,facility_medicines.medicine_id ";
//         $sql .= "                 ,facility_medicines.maker_name ";
//         $sql .= "                 ,trader.id   as trader_id ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 ,trader.code as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 ,trader.name as trader_name ";
//         $sql .= "                 ,facility_prices.sales_price ";
//         $sql .= "                 ,facility_prices.id as facility_price_id ";
//         $sql .= "                 ,facility_medicines.sales_user_group_id ";
//         $sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//         $sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//         //2019.08.26 オプション項目追加 start
//         $sql .= "                 ,facility_medicines.optional_key1 ";
//         $sql .= "                 ,facility_medicines.optional_key2 ";
//         $sql .= "                 ,facility_medicines.optional_key3 ";
//         $sql .= "                 ,facility_medicines.optional_key4 ";
//         $sql .= "                 ,facility_medicines.optional_key5 ";
//         $sql .= "                 ,facility_medicines.optional_key6 ";
//         $sql .= "                 ,facility_medicines.optional_key7 ";
//         $sql .= "                 ,facility_medicines.optional_key8 ";
//         //2019.08.26 オプション項目追加 end
//         $sql .= "             from ";
//         $sql .= "                 facility_medicines ";
//         $sql .= "                 left join facility_prices ";
//         $sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//         $sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//         $sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//         $sql .= "                     and facility_prices.deleted_at is null ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 left join facilities as trader ";
//         //$sql .= "                     on facility_prices.trader_id=trader.id ";
//         $sql .= "                 left join user_groups as trader ";
//         $sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 left join user_groups as user_group ";
//         $sql .= "                     on facility_medicines.sales_user_group_id=user_group.id ";
//         $sql .= "             where ";
//         $sql .= "                  facility_medicines.deleted_at is null ";
//         $sql .= "                  and facility_medicines.sales_user_group_id=".$user->primary_honbu_user_group_id;
//         $sql .= "         ) as fm_hq ";
//         $sql .= "             on medicines.id = fm_hq.medicine_id ";
//         $sql .= "         inner join pack_units ";
//         $sql .= "             on medicines.id = pack_units.medicine_id ";
//         $sql .= "         left join makers  ";
//         $sql .= "             on medicines.selling_agency_code = makers.id ";
//         $sql .= "         left join makers as production_maker ";
//         $sql .= "             on medicines.maker_id = production_maker.id ";
//         $sql .= "         left join medicine_prices  ";
//         $sql .= "             on medicines.id = medicine_prices.medicine_id ";
//         $sql .= "             and medicine_prices.end_date >= '".$today."'";
//         $sql .= "             and medicine_prices.start_date <= '".$today."'";
//         $sql .= "             and medicine_prices.deleted_at is null ";
//         $sql .= "         left join ( ";
//         $sql .= "             select ";
//         $sql .= "                  price_adoptions.* ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 ,facilities.code as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 ,facilities.name as trader_name ";
//         $sql .= "             from ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 price_adoptions left join facilities ";
//         //$sql .= "                     on price_adoptions.trader_id=facilities.id ";
//         $sql .= "                 price_adoptions left join user_groups as facilities ";
//         $sql .= "                     on price_adoptions.purchase_user_group_id=facilities.id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "             where ";
//         $sql .= "                 price_adoptions.deleted_at is null ";
//         $sql .= "                 and price_adoptions.sales_user_group_id = ".$user->primary_user_group_id;
//         $sql .= "         ) as pa_trader ";
//         $sql .= "             on fm_hq.medicine_id = pa_trader.medicine_id ";
//         $sql .= "             and fm_hq.trader_id = pa_trader.purchase_user_group_id ";

//         $sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//         $sql .= "             where deleted_at is null ";
//         $sql .= "             group by facility_medicine_id ";
//         $sql .= "             ) as own_fpr";
//         $sql .= "             on fm_own.id=own_fpr.facility_medicine_id ";

//         $sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//         $sql .= "             where deleted_at is null ";
//         //$sql .= "             and start_date <= '".$stop_date."'";
//         //$sql .= "             and end_date >='".$stop_date."'";
//         $sql .= "             group by facility_medicine_id ";
//         $sql .= "             ) as hq_fpr";
//         $sql .= "             on fm_hq.id=hq_fpr.facility_medicine_id ";


//         $sql .= "         left join ( ";
//         $sql .= "             select ";
//         $sql .= "                  facility_medicines.id ";
//         //$sql .= "                 ,facility_medicines.facility_id ";
//         $sql .= "                 ,facility_medicines.medicine_id ";
//         $sql .= "                 ,facility_medicines.maker_name ";
//         $sql .= "                 ,facilities.name as facility_name ";
//         $sql .= "                 ,trader.id as trader_id ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 ,trader.code as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 ,trader.name as trader_name ";
//         $sql .= "                 ,facility_prices.sales_price ";
//         $sql .= "                 ,facility_prices.id as facility_price_id ";
//         $sql .= "                 ,facility_medicines.sales_user_group_id ";
//         $sql .= "                 ,facility_prices.start_date as facility_price_start_date";
//         $sql .= "                 ,facility_prices.end_date as facility_price_end_date ";
//         //2019.08.26 オプション項目追加 start
//         $sql .= "                 ,facility_medicines.optional_key1 ";
//         $sql .= "                 ,facility_medicines.optional_key2 ";
//         $sql .= "                 ,facility_medicines.optional_key3 ";
//         $sql .= "                 ,facility_medicines.optional_key4 ";
//         $sql .= "                 ,facility_medicines.optional_key5 ";
//         $sql .= "                 ,facility_medicines.optional_key6 ";
//         $sql .= "                 ,facility_medicines.optional_key7 ";
//         $sql .= "                 ,facility_medicines.optional_key8 ";
//         //2019.08.26 オプション項目追加 end
//         $sql .= "             from ";
//         $sql .= "                 facility_medicines ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 left join facilities ";
//         //$sql .= "                     on facility_medicines.facility_id=facilities.id ";
//         $sql .= "                 left join user_groups as facilities ";
//         $sql .= "                     on facility_medicines.sales_user_group_id=facilities.id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "                 left join facility_prices ";
//         $sql .= "                     on facility_medicines.id=facility_prices.facility_medicine_id ";
//         $sql .= "                     and facility_prices.start_date <= '".$stop_date."'";
//         $sql .= "                     and facility_prices.end_date >= '".$stop_date."'";
//         $sql .= "                     and facility_prices.deleted_at is null ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "                 left join facilities as trader ";
//         //$sql .= "                     on facility_prices.trader_id=trader.id ";
//         $sql .= "                 left join user_groups as trader ";
//         $sql .= "                     on facility_prices.purchase_user_group_id=trader.id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "             where ";
//         $sql .= "                 facility_medicines.deleted_at is null ";
//         $sql .= "                 and facility_medicines.sales_user_group_id = ".$user->primary_user_group_id;
//         $sql .= "         ) as fm_own_trader ";
//         $sql .= "             on medicines.id = fm_own_trader.medicine_id ";
//         $sql .= "             and fm_hq.trader_id = fm_own_trader.trader_id ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "         left join facilities  ";
//         //$sql .= "             on facilities.id = pa_own.facility_id ";
//         $sql .= "         left join user_groups as facilities  ";
//         $sql .= "             on facilities.id = pa_own.sales_user_group_id ";
//         //2019.07.31 facilities撲滅 end
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "         left join facilities as trader  ";
//         //$sql .= "             on trader.id = pa_own.trader_id ";
//         $sql .= "         left join user_groups as trader  ";
//         $sql .= "             on trader.id = pa_own.purchase_user_group_id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "         left join users ";
//         $sql .= "             on users.id = pa_own.user_id ";
//         $sql .= "     where ";
//         $sql .= "        medicines.deleted_at is null";

//         // 施設
//         if (!empty($request->user_group)) {
//             $sql .= " and coalesce(facilities.id, fm_own.sales_user_group_id) in (".implode(',', $request->user_group).")";
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $sql .= " and medicines.code like '%".$request->code."%'";
//         }
//         // 一般名
//         if (!empty($request->popular_name)) {
//             $sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//         }

//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $sql .= " and pack_units.coefficient = ".$request->coefficient;
//         }

//         // 販売中止日
//         if (!empty($request->discontinuation_date)) {
//             $sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//         }

//         $search_words = [];

//         // 全文検索
//         if (!empty($request->search)) {
//             $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//             foreach($search_words as $word) {
//                 $sql .= " and medicines.search like '%".$word."%'";
//             }
//         }

//         // 剤型区分
//         if (!empty($request->dosage_type_division)) {
//             $sql .= " and dosage_type_division = ".$request->dosage_type_division;
//         }

//         // 先発後発品区分
//         if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//             $sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//         }

//         //2019.08.26 オプション項目追加 start
//         //項目1
//         //項目1
//         if (!empty($request->optional_key1)) {
//             $sql .= " and (fm_own.optional_key1 like '%".$request->optional_key1."%' ";
//             $sql .= "   or fm_own_trader.optional_key1 like '%".$request->optional_key1."%')";
//         }

//         //項目2
//         if (!empty($request->optional_key2)) {
//             $sql .= " and (fm_own.optional_key2 like '%".$request->optional_key2."%' ";
//             $sql .= "   or fm_own_trader.optional_key2 like '%".$request->optional_key2."%')";
//         }

//         //項目3
//         if (!empty($request->optional_key3)) {
//             $sql .= " and (fm_own.optional_key3 like '%".$request->optional_key3."%' ";
//             $sql .= "   or fm_own_trader.optional_key3 like '%".$request->optional_key3."%')";
//         }

//         //項目4
//         if (!empty($request->optional_key4)) {
//             $sql .= " and (fm_own.optional_key4 like '%".$request->optional_key4."%' ";
//             $sql .= "   or fm_own_trader.optional_key4 like '%".$request->optional_key4."%')";
//         }

//         //項目5
//         if (!empty($request->optional_key5)) {
//             $sql .= " and (fm_own.optional_key5 like '%".$request->optional_key5."%' ";
//             $sql .= "   or fm_own_trader.optional_key5 like '%".$request->optional_key5."%')";
//         }

//         //項目6
//         if (!empty($request->optional_key6)) {
//             $sql .= " and (fm_own.optional_key6 like '%".$request->optional_key6."%' ";
//             $sql .= "   or fm_own_trader.optional_key6 like '%".$request->optional_key6."%')";
//         }

//         //項目7
//         if (!empty($request->optional_key7)) {
//             $sql .= " and (fm_own.optional_key7 like '%".$request->optional_key7."%' ";
//             $sql .= "   or fm_own_trader.optional_key7 like '%".$request->optional_key7."%')";
//         }

//         //項目8
//         if (!empty($request->optional_key8)) {
//             $sql .= " and (fm_own.optional_key8 like '%".$request->optional_key8."%' ";
//             $sql .= "   or fm_own_trader.optional_key8 like '%".$request->optional_key8."%')";
//         }


//         //項目1
//         if (!empty($request->hq_optional_key1)) {
//             $sql .= " and fm_hq.optional_key1 like '%".$request->hq_optional_key1."%' ";
//         }
//         if (!empty($request->hq_optional_key2)) {
//             $sql .= " and fm_hq.optional_key2 like '%".$request->hq_optional_key2."%' ";
//         }
//         if (!empty($request->hq_optional_key3)) {
//             $sql .= " and fm_hq.optional_key3 like '%".$request->hq_optional_key3."%' ";
//         }
//         if (!empty($request->hq_optional_key4)) {
//             $sql .= " and fm_hq.optional_key4 like '%".$request->hq_optional_key4."%' ";
//         }
//         if (!empty($request->hq_optional_key5)) {
//             $sql .= " and fm_hq.optional_key5 like '%".$request->hq_optional_key5."%' ";
//         }
//         if (!empty($request->hq_optional_key6)) {
//             $sql .= " and fm_hq.optional_key6 like '%".$request->hq_optional_key6."%' ";
//         }
//         if (!empty($request->hq_optional_key7)) {
//             $sql .= " and fm_hq.optional_key7 like '%".$request->hq_optional_key7."%' ";
//         }
//         if (!empty($request->hq_optional_key8)) {
//             $sql .= " and fm_hq.optional_key8 like '%".$request->hq_optional_key8."%' ";
//         }
//         //2019.08.26 オプション項目追加 end


//         $sql .= "     ) as base ";
//         $sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//         $sql .= "         on vprpab.user_group_id = base.sales_user_group_id ";
//         $sql .= "         and vprpab.user_id = ".$user->id;
//         $sql .= "         and vprpab.status = base.status ";

//         $sql .= "     left join v_privileges_row_yakuhinshinsei as vpry";
//         $sql .= "         on vpry.user_group_id = base.sales_user_group_id ";
//         $sql .= "         and vpry.user_id = ".$user->id;

//         $sql .= " where ";
//         $sql .= "     1=1";
//         $sql .= "     and (vpry.show_status1 = '1' and base.status = 1 or base.status <> 1)";

//         // 状態
//         if (!empty($request->status)) {
//             if(in_array(10, $request->status)) {
//                 $req_status = $request->status;
//                 $req_status[] = 11;
//                 $sql .= " and coalesce(base.status,1) in (".implode(',', $req_status).")";
//             } else {
//                 $sql .= " and coalesce(base.status,1) in (".implode(',', $request->status).")";
//             }

//         }

//         //採用中止日
//         if (!empty($request->stop_date)) {
//             $sql .= " and base.adoption_stop_date >= '".$stop_date."'";
//         }

//         //自施設の採用、自施設の申請、自本部の採用 END

//         //自施設以外の採用 START
//         $sql .=" ) ";
//         $sql .=" union all ";
//         $sql .=" (select ";
//         $sql .="       medicines.id as medicine_id ";
//         $sql .="      ,medicines.code ";
//         $sql .="      ,medicines.sales_packaging_code ";
//         $sql .="      ,medicines.generic_product_detail_devision ";
//         $sql .="      ,medicines.popular_name ";
//         $sql .="      ,pack_units.jan_code as jan_code ";
//         $sql .="      ,medicines.name as medicine_name ";
//         $sql .="      ,medicines.standard_unit as standard_unit ";
//         $sql .="      ,coalesce(makers.name,fm_otr.maker_name) as maker_name ";
//         $sql .="      ,medicine_prices.price as price ";
//         $sql .="      ,'' as comment ";
//         //$sql .="      ,10 as status ";
//         $sql .="    ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//         $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//         $sql .="        WHEN fp_expire.med_id is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 11 "; // 通常の場合は、採用可になるが再申請の場合は前のデータがあるので期限切れ
//         $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//         $sql .="      else 10 ";
//         $sql .="      end) as status ";
//         $sql .="      ,facilities.id as facility_id ";
//         $sql .="      ,facilities.name as facility_name ";
//         $sql .="      ,'' as user_name ";
//         $sql .="      ,pack_units.total_pack_unit ";
//         $sql .="      ,pack_units.price as pack_unit_price ";
//         $sql .="      ,pack_units.coefficient ";
//         $sql .="      ,null as application_date ";
//         //$sql .= "     ,f_show_trader_name(10,fm_otr.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//         $sql .="      ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN f_show_trader_name(fm_otr.price_status,fm_otr.exp_trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//         $sql .="      else f_show_trader_name(10,fm_otr.trader_name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//         $sql .="      end) as trader_name ";

//         $sql .="      ,0 as fm_parent ";
//         $sql .="      ,0 as fm_own ";
//         $sql .= "     ,(case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN f_show_sales_price(fm_otr.price_status,fm_otr.exp_sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//         $sql .= "     else f_show_sales_price(10,fm_otr.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) ";
//         $sql .= "     end) as sales_price ";
//         //$sql .= "     ,f_show_sales_price(10,fm_otr.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// /*
//         $sql .= "     ,case ";
//         $sql .= "          when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else facility_prices.sales_price ";
//         $sql .= "      end as sales_price ";
// */
//         $sql .="      ,fm_otr.facility_price_id as fp_id ";
//         $sql .="      ,coalesce(fm_otr.price_adoption_id,0) as price_adoption_id ";
//         //CSVダウンロード追加項目 START
//         $sql .= "     ,production_maker.name as production_maker_name ";
//         $sql .= "     ,medicines.dispensing_packaging_code ";
//         $sql .= "     ,pack_units.hot_code ";
//         $sql .= "     ,medicines.dosage_type_division ";
//         $sql .= "     ,medicine_prices.price as medicine_price ";
//         $sql .= "     ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//         $sql .= "     ,medicine_prices.start_date as medicine_price_start_date ";
//         $sql .= "     ,fm_otr.facility_price_start_date ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,f_show_trader_name(10,fm_otr.trader_code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "     ,fm_otr.created_at ";
//         $sql .= "     ,fm_otr.updated_at ";
//         $sql .= "     ,fm_otr.adoption_date ";
//         $sql .= "     ,fm_otr.adoption_stop_date ";
//         $sql .= "     ,medicines.production_stop_date ";
//         $sql .= "     ,medicines.transitional_deadline ";
//         $sql .= "     ,medicines.discontinuation_date ";
//         $sql .= "     ,medicines.discontinuing_division ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,facilities.code as facility_code ";
//         //2019.07.31 facilities撲滅 end
//         //CSVダウンロード追加項目 END

//         //$sql .= "     ,f_yakuhinshinsei_task_next(10,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//         //$sql .= "     ,f_yakuhinshinsei_task_reject(10,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//         //$sql .= "     ,f_yakuhinshinsei_task_withdraw(10,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//         $sql .= "     ,vprpab.status_forward  ";
//         $sql .= "     ,vprpab.status_back  ";
//         $sql .= "     ,vprpab.status_back_text  ";
//         $sql .= "     ,fm_otr.optional_key1 ";
//         $sql .= "     ,fm_otr.optional_key2 ";
//         $sql .= "     ,fm_otr.optional_key3 ";
//         $sql .= "     ,fm_otr.optional_key4 ";
//         $sql .= "     ,fm_otr.optional_key5 ";
//         $sql .= "     ,fm_otr.optional_key6 ";
//         $sql .= "     ,fm_otr.optional_key7 ";
//         $sql .= "     ,fm_otr.optional_key8 ";
//         $sql .= "     ,fm_otr.hq_optional_key1 ";
//         $sql .= "     ,fm_otr.hq_optional_key2 ";
//         $sql .= "     ,fm_otr.hq_optional_key3 ";
//         $sql .= "     ,fm_otr.hq_optional_key4 ";
//         $sql .= "     ,fm_otr.hq_optional_key5 ";
//         $sql .= "     ,fm_otr.hq_optional_key6 ";
//         $sql .= "     ,fm_otr.hq_optional_key7 ";
//         $sql .= "     ,fm_otr.hq_optional_key8 ";
//         $sql .= "     ,fm_otr.facility_price_end_date ";
//         $sql .= " from ";
//         $sql .= "     (select distinct ";
//         $sql .= "           facility_medicines.id ";
//         //$sql .= "          ,facility_medicines.facility_id ";
//         $sql .= "          ,facility_medicines.medicine_id ";
//         $sql .= "          ,facility_medicines.maker_name ";
//         $sql .= "          ,facility_medicines.sales_user_group_id ";
//         $sql .= "          ,facility_prices.start_date as facility_price_start_date ";
//         $sql .= "          ,facility_prices.end_date as facility_price_end_date ";
//         $sql .= "          ,price_adoptions.created_at ";
//         $sql .= "          ,price_adoptions.updated_at ";
//         $sql .= "          ,facility_medicines.adoption_date ";
//         $sql .= "          ,coalesce(facility_medicines.adoption_stop_date,'2999/12/31') as adoption_stop_date ";
//         $sql .= "          ,price_adoptions.id as price_adoption_id ";

//         $sql .= "          ,price_adoptions.status as price_status ";
//         $sql .= "          ,price_adoptions.sales_price as exp_sales_price ";
//         $sql .= "          ,exp_trader.name as exp_trader_name ";

//         //2019.07.31 facilities撲滅 start
//         //$sql .= "          ,facilities.code as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "          ,facilities.name as trader_name ";
//         $sql .= "          ,facility_prices.sales_price ";
//         $sql .= "          ,facility_prices.id as facility_price_id ";
//         //2019.08.26 オプション項目追加 start
//         $sql .= "          ,facility_medicines.optional_key1 ";
//         $sql .= "          ,facility_medicines.optional_key2 ";
//         $sql .= "          ,facility_medicines.optional_key3 ";
//         $sql .= "          ,facility_medicines.optional_key4 ";
//         $sql .= "          ,facility_medicines.optional_key5 ";
//         $sql .= "          ,facility_medicines.optional_key6 ";
//         $sql .= "          ,facility_medicines.optional_key7 ";
//         $sql .= "          ,facility_medicines.optional_key8 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key1 else hq_fm.optional_key1 end as hq_optional_key1 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key2 else hq_fm.optional_key2 end as hq_optional_key2 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key3 else hq_fm.optional_key3 end as hq_optional_key3 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key4 else hq_fm.optional_key4 end as hq_optional_key4 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key5 else hq_fm.optional_key5 end as hq_optional_key5 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key6 else hq_fm.optional_key6 end as hq_optional_key6 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key7 else hq_fm.optional_key7 end as hq_optional_key7 ";
//         $sql .= "          ,case when ug.group_type='本部' then facility_medicines.optional_key8 else hq_fm.optional_key8 end as hq_optional_key8 ";
//         $sql .= "          ,hq_fm.sales_user_group_id as hq_sales_user_group_id ";

//         //$sql .= "          ,hq_fp.id as hq_fp_id";
//         $sql .= "          ,own_fpr.fpr_cnt as fpr_cnt";
//         $sql .= "          ,hq_fpr.fpr_cnt as hq_fpr_cnt";

//         //2019.08.26 オプション項目追加 end
//         $sql .= "     from ";
//         $sql .= "         facility_medicines ";
//         $sql .= "         left join facility_prices ";
//         $sql .= "             on facility_medicines.id=facility_prices.facility_medicine_id ";
//         $sql .= "             and facility_prices.start_date <= '".$stop_date."'";
//         $sql .= "             and facility_prices.end_date >='".$stop_date."'";
//         $sql .= "             and facility_prices.deleted_at is null ";
//         $sql .= "         left join price_adoptions ";
//         $sql .= "             on facility_medicines.sales_user_group_id=price_adoptions.sales_user_group_id ";
//         $sql .= "             and facility_medicines.medicine_id=price_adoptions.medicine_id ";
//         $sql .= "             and price_adoptions.deleted_at is null ";

//         //2019.07.31 facilities撲滅 start
//         //$sql .= "         left join facilities ";
//         //$sql .= "             on facility_prices.trader_id=facilities.id ";
//         $sql .= "         left join user_groups as facilities ";
//         $sql .= "             on facility_prices.purchase_user_group_id=facilities.id ";
//         $sql .= "         left join user_groups as exp_trader ";
//         $sql .= "             on price_adoptions.purchase_user_group_id=exp_trader.id ";
//         $sql .= "         left join user_groups as ug ";
//         $sql .= "             on facility_medicines.sales_user_group_id=ug.id ";
//         $sql .= "         left join group_relations as gr ";
//         $sql .= "             on facility_medicines.sales_user_group_id=gr.user_group_id ";
//         $sql .= "         left join facility_medicines as hq_fm ";
//         $sql .= "             on hq_fm.sales_user_group_id=gr.partner_user_group_id ";
//         $sql .= "             and hq_fm.medicine_id = facility_medicines.medicine_id ";

//         $sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//         $sql .= "             where deleted_at is null ";
//         $sql .= "             group by facility_medicine_id ";
//         $sql .= "             ) as own_fpr";
//         $sql .= "             on facility_medicines.id=own_fpr.facility_medicine_id ";

//         $sql .= "         left join ( select facility_medicine_id, count(*) as fpr_cnt from facility_prices";
//         $sql .= "             where deleted_at is null ";
//         $sql .= "             and start_date <= '".$stop_date."'";
//         $sql .= "             and end_date >='".$stop_date."'";
//         $sql .= "             group by facility_medicine_id ";
//         $sql .= "             ) as hq_fpr";
//         $sql .= "             on hq_fm.id=hq_fpr.facility_medicine_id ";
//         //$sql .= "             and ug.id = facility_medicines.sales_user_group_id ";
//         //$sql .= "             and ug.group_type = '本部' ";

//         //2019.07.31 facilities撲滅 end
//         $sql .= "     where ";
//         $sql .= "         facility_medicines.deleted_at is null ";
//         $sql .= "         and facility_medicines.sales_user_group_id <> ".$user->primary_user_group_id;
//         $sql .= "     ) as fm_otr ";
//         $sql .= "     inner join ( ";
//         $sql .= "         select  ";
//         $sql .= "             c.user_group_id  ";
//         $sql .= "         from ";
//         $sql .= "             user_groups as b ";
//         $sql .= "             inner join user_group_relations as c  ";
//         $sql .= "                 on c.user_group_id = b.id ";
//         $sql .= "                 and c.deleted_at is null  ";
//         $sql .= "         where ";
//         $sql .= "             c.user_id = ".$user->id;
//         $sql .= "     ) as user_groups ";
//         $sql .= "         on fm_otr.sales_user_group_id = user_groups.user_group_id ";
//         $sql .= "     left join medicines ";
//         $sql .= "         on fm_otr.medicine_id = medicines.id  ";
//         $sql .= "     inner join pack_units ";
//         $sql .= "         on medicines.id = pack_units.medicine_id ";
//         $sql .= "     left join makers ";
//         $sql .= "         on medicines.selling_agency_code = makers.id ";
//         $sql .= "     left join makers as production_maker ";
//         $sql .= "         on medicines.maker_id = production_maker.id ";
//         $sql .= "     left join medicine_prices ";
//         $sql .= "         on medicines.id = medicine_prices.medicine_id ";
//         $sql .= "         and medicine_prices.end_date >= '".$today."'";
//         $sql .= "         and medicine_prices.start_date <= '".$today."'";
//         $sql .= "         and medicine_prices.deleted_at is null ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     left join facilities ";
//         //$sql .= "         on facilities.id = fm_otr.facility_id  ";
//         $sql .= "     left join user_groups as facilities ";
//         $sql .= "         on facilities.id = fm_otr.sales_user_group_id  ";
//         $sql .= "     left join user_groups as hq_group ";
//         $sql .= "         on hq_group.id = fm_otr.hq_sales_user_group_id  ";

//         $sql .= "LEFT JOIN ( ";
//         $sql .= "   SELECT";
//         $sql .= "   distinct ";
//         $sql .= "     facility_medicines.medicine_id AS med_id ";
//         $sql .= "     ,facility_medicines.sales_user_group_id AS group_id  ";
//         $sql .= "    FROM facility_medicines";
//         $sql .= "  inner join facility_prices";
//         $sql .= "   ON facility_medicines.id = facility_prices.facility_medicine_id ";
//         $sql .= "     AND facility_prices.start_date <='".$today."' and facility_prices.end_date >= '".$today."'";
//         $sql .= "  AND facility_prices.deleted_at is null";
//         $sql .= " WHERE facility_medicines.deleted_at is null";
//         $sql .= " ) as fp_expire";
//         $sql .= " on medicines.id=fp_expire.med_id";
//         $sql .= " AND facilities.id = fp_expire.group_id";

//         //2019.07.31 facilities撲滅 end
//         /*
//         $sql .= "     left join facility_prices ";
//         $sql .= "         on fm_otr.id = facility_prices.facility_medicine_id  ";
//         $sql .= "         and facility_prices.start_date <= now() ";
//         $sql .= "         and facility_prices.end_date >= now() ";
//         $sql .= "         and facility_prices.deleted_at is null ";
//         $sql .= "     left join facilities as trader ";
//         $sql .= "         on facility_prices.trader_id = trader.id ";
//         */
//         $sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//         $sql .= "         on vprpab.user_group_id = fm_otr.sales_user_group_id ";
//         $sql .= "         and vprpab.user_id = ".$user->id;

//         $sql .= "         and vprpab.status = (CASE WHEN fm_otr.price_status = 10 THEN 10 ";
//         $sql .= "         WHEN  fm_otr.price_status <> 10 THEN fm_otr.price_status ";
//         $sql .= "         ELSE 10 ";
//         $sql .= "         END) ";

//         $sql .= "     left join v_privileges_row_yakuhinshinsei as vpry ";
//         $sql .= "         on vpry.user_group_id = fm_otr.sales_user_group_id ";
//         $sql .= "         and vpry.user_id = ".$user->id;

//         $sql .= " where ";
//         $sql .= "    medicines.deleted_at is null";

//         // 状態
//         if (!empty($request->status)) {
//             if(in_array(10, $request->status)) {
//                 $req_status = $request->status;
//                 $req_status[] = 11;
//             } else {
//                 $req_status = $request->status;
//             }

//             $sql .= " and ((11 = (case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 8 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
//             $sql .="      else 10 ";
//             $sql .="      END)" ;
//             $sql .= " and 11 in (".implode(',', $req_status)."))" ;
//             $sql .= " or ( 10 = (case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt > 0 THEN 8 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
//             $sql .="      else 10 ";
//             $sql .="      END)" ;
//             $sql .= " and 10 in (".implode(',', $req_status).")))" ;

// /*
//             $sql .= " and ((case WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status = 10 THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is not null and fm_otr.price_status <>10 THEN fm_otr.price_status ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null and fm_otr.fpr_cnt > 0 and fm_otr.hq_fpr_cnt is null THEN 11 ";
//             $sql .="        WHEN fp_expire.med_id is null and fm_otr.price_status is null THEN 11 ";
//             $sql .="      else 10 ";
//             $sql .="      END) in (".implode(',', $req_status)."))" ;*/



//         }

//         // 施設
//         if (!empty($request->user_group)) {
//             $sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $sql .= " and medicines.code like '%".$request->code."%'";
//         }
//         // 一般名
//         if (!empty($request->popular_name)) {
//             $sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//         }

//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $sql .= " and pack_units.coefficient = ".$request->coefficient;
//         }

//         // 販売中止日
//         if (!empty($request->discontinuation_date)) {
//             $sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//         }

//         $search_words = [];

//         // 全文検索
//         if (!empty($request->search)) {
//             $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//             foreach($search_words as $word) {
//                 $sql .= " and medicines.search like '%".$word."%'";
//             }
//         }

//         // 剤型区分
//         if (!empty($request->dosage_type_division)) {
//             $sql .= " and dosage_type_division = ".$request->dosage_type_division;
//         }

//         // 先発後発品区分
//         if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//             $sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//         }

//         //2019.08.26 オプション項目追加 start
//         //項目1
//         if (!empty($request->optional_key1)) {
//             $sql .= " and optional_key1 like '%".$request->optional_key1."%'";

//         }

//         //項目2
//         if (!empty($request->optional_key2)) {
//             $sql .= " and optional_key2 like '%".$request->optional_key2."%'";

//         }

//         //項目3
//         if (!empty($request->optional_key3)) {
//             $sql .= " and optional_key3 like '%".$request->optional_key3."%'";

//         }

//         //項目4
//         if (!empty($request->optional_key4)) {
//             $sql .= " and optional_key4 like '%".$request->optional_key4."%'";

//         }

//         //項目5
//         if (!empty($request->optional_key5)) {
//             $sql .= " and optional_key5 like '%".$request->optional_key5."%'";

//         }

//         //項目6
//         if (!empty($request->optional_key6)) {
//             $sql .= " and optional_key6 like '%".$request->optional_key6."%'";

//         }

//         //項目7
//         if (!empty($request->optional_key7)) {
//             $sql .= " and optional_key7 like '%".$request->optional_key7."%'";

//         }

//         //項目8
//         if (!empty($request->optional_key8)) {
//             $sql .= " and optional_key8 like '%".$request->optional_key8."%'";

//         }

//         //項目1
//         if (!empty($request->hq_optional_key1)) {
//             $sql .= " and hq_optional_key1 like '%".$request->hq_optional_key1."%' ";

//         }
//         if (!empty($request->hq_optional_key2)) {
//             $sql .= " and hq_optional_key2 like '%".$request->hq_optional_key2."%' ";

//         }
//         if (!empty($request->hq_optional_key3)) {
//             $sql .= " and hq_optional_key3 like '%".$request->hq_optional_key3."%' ";

//         }
//         if (!empty($request->hq_optional_key4)) {
//             $sql .= " and hq_optional_key4 like '%".$request->hq_optional_key4."%' ";

//         }
//         if (!empty($request->hq_optional_key5)) {
//             $sql .= " and hq_optional_key5 like '%".$request->hq_optional_key5."%' ";

//         }
//         if (!empty($request->hq_optional_key6)) {
//             $sql .= " and hq_optional_key6 like '%".$request->hq_optional_key6."%' ";

//         }
//         if (!empty($request->hq_optional_key7)) {
//             $sql .= " and hq_optional_key7 like '%".$request->hq_optional_key7."%' ";

//         }
//         if (!empty($request->hq_optional_key8)) {
//             $sql .= " and hq_optional_key8 like '%".$request->hq_optional_key8."%' ";
//         }
//         //2019.08.26 オプション項目追加 end
//         //採用中止日
//         if (!empty($request->stop_date)) {
//             $sql .= " and fm_otr.adoption_stop_date >= '".$stop_date."'";
//         }

//         //自施設以外の採用 END

//         //自施設以外の申請(採用のないもの) START
//         $sql .=" ) ";
//         $sql .=" union all ";
//         $sql .=" (select ";
//         $sql .="       medicines.id as medicine_id ";
//         $sql .="      ,medicines.code ";
//         $sql .="      ,medicines.sales_packaging_code ";
//         $sql .="      ,medicines.generic_product_detail_devision ";
//         $sql .="      ,medicines.popular_name ";
//         $sql .="      ,pack_units.jan_code as jan_code ";
//         $sql .="      ,medicines.name as medicine_name ";
//         $sql .="      ,medicines.standard_unit as standard_unit ";
//         $sql .="      ,coalesce(price_adoptions.maker_name,makers.name) as maker_name ";
//         $sql .="      ,medicine_prices.price as price ";
//         $sql .="      ,price_adoptions.comment as comment ";
//         $sql .="      ,price_adoptions.status as status ";
//         $sql .="      ,facilities.id as facility_id ";
//         $sql .="      ,facilities.name as facility_name ";
//         $sql .="      ,users.name as user_name ";
//         $sql .="      ,pack_units.total_pack_unit ";
//         $sql .="      ,pack_units.price as pack_unit_price ";
//         $sql .="      ,pack_units.coefficient ";
//         $sql .="      ,price_adoptions.application_date as application_date ";

//         $sql .= "     ,f_show_trader_name(price_adoptions.status,trader.name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//         $sql .="      ,0 as fm_parent ";
//         $sql .="      ,0 as fm_own ";
//         $sql .= "     ,f_show_sales_price(price_adoptions.status,price_adoptions.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// /*
//         $sql .= "     ,case ";
//         $sql .= "          when price_adoptions.status < 7 then";
//         $sql .= "              case when coalesce(vpry.show_sales_price_before6,0) <= 0 then null else price_adoptions.sales_price end";
//         $sql .= "          else ";
//         $sql .= "              case when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else price_adoptions.sales_price end";
//         $sql .= "      end as sales_price ";
// */
//         $sql .="      ,0 as fp_id ";
//         $sql .="      ,price_adoptions.id as price_adoption_id ";
//         //CSVダウンロード追加項目 START
//         $sql .= "     ,production_maker.name as production_maker_name ";
//         $sql .= "     ,medicines.dispensing_packaging_code ";
//         $sql .= "     ,pack_units.hot_code ";
//         $sql .= "     ,medicines.dosage_type_division ";
//         $sql .= "     ,medicine_prices.price as medicine_price ";
//         $sql .= "     ,medicine_prices.price * pack_units.coefficient as unit_medicine_price ";
//         $sql .= "     ,medicine_prices.start_date as medicine_price_start_date ";
//         $sql .= "     ,null as facility_price_start_date";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,f_show_trader_name(price_adoptions.status,trader.code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "     ,price_adoptions.created_at ";
//         $sql .= "     ,price_adoptions.updated_at ";
//         $sql .= "     ,null as adoption_date ";
//         $sql .= "     ,null as adoption_stop_date ";
//         $sql .= "     ,medicines.production_stop_date ";
//         $sql .= "     ,medicines.transitional_deadline ";
//         $sql .= "     ,medicines.discontinuation_date ";
//         $sql .= "     ,medicines.discontinuing_division ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,facilities.code as facility_code ";
//         //2019.07.31 facilities撲滅 end
//         //CSVダウンロード追加項目 END

//         //$sql .= "     ,f_yakuhinshinsei_task_next(price_adoptions.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//         //$sql .= "     ,f_yakuhinshinsei_task_reject(price_adoptions.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//         //$sql .= "     ,f_yakuhinshinsei_task_withdraw(price_adoptions.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//         $sql .= "     ,vprpab.status_forward  ";
//         $sql .= "     ,vprpab.status_back  ";
//         $sql .= "     ,vprpab.status_back_text  ";
//         $sql .= "     ,'' as optional_key1  ";
//         $sql .= "     ,'' as optional_key2  ";
//         $sql .= "     ,'' as optional_key3  ";
//         $sql .= "     ,'' as optional_key4  ";
//         $sql .= "     ,'' as optional_key5  ";
//         $sql .= "     ,'' as optional_key6  ";
//         $sql .= "     ,'' as optional_key7  ";
//         $sql .= "     ,'' as optional_key8  ";
//         $sql .= "     ,''  as hq_optional_key1 ";
//         $sql .= "     ,''  as hq_optional_key2 ";
//         $sql .= "     ,''  as hq_optional_key3 ";
//         $sql .= "     ,''  as hq_optional_key4 ";
//         $sql .= "     ,''  as hq_optional_key5 ";
//         $sql .= "     ,''  as hq_optional_key6 ";
//         $sql .= "     ,''  as hq_optional_key7 ";
//         $sql .= "     ,''  as hq_optional_key8 ";
//         $sql .= "     ,null as facility_price_end_date";
//         $sql .= " from ";
//         $sql .= "     (select ";
//         $sql .= "          price_adoptions.* ";
//         $sql .= "      from ";
//         $sql .= "          price_adoptions left join facility_medicines";
//         $sql .= "              on price_adoptions.medicine_id = facility_medicines.medicine_id ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "              and price_adoptions.facility_id = facility_medicines.facility_id ";
//         $sql .= "              and price_adoptions.sales_user_group_id = facility_medicines.sales_user_group_id ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "      where ";
//         $sql .= "          price_adoptions.deleted_at is null";
//         $sql .= "          and facility_medicines.id is null";
//         $sql .= "          and price_adoptions.sales_user_group_id <> ".$user->primary_user_group_id;

//         $sql .= "     ) as price_adoptions ";
//         $sql .= "     inner join ( ";
//         $sql .= "         select distinct";
//         $sql .= "             c.user_group_id  ";
//         $sql .= "         from";
//         $sql .= "             user_groups as b";
//         $sql .= "             inner join user_group_relations as c";
//         $sql .= "                 on c.user_group_id = b.id";
//         $sql .= "                 and c.deleted_at is null";
//         $sql .= "         where";
//         $sql .= "             c.user_id = ".$user->id;
//         $sql .= "     ) as user_groups ";
//         $sql .= "         on price_adoptions.sales_user_group_id = user_groups.user_group_id ";
//         $sql .= "     left join medicines ";
//         $sql .= "         on price_adoptions.medicine_id = medicines.id ";
//         $sql .= "     inner join pack_units ";
//         $sql .= "         on medicines.id = pack_units.medicine_id ";
//         $sql .= "     left join makers ";
//         $sql .= "         on medicines.selling_agency_code = makers.id ";
//         $sql .= "     left join makers as production_maker ";
//         $sql .= "         on medicines.maker_id = production_maker.id ";
//         $sql .= "     left join medicine_prices ";
//         $sql .= "         on medicines.id = medicine_prices.medicine_id ";
//         $sql .= "         and medicine_prices.end_date >='".$today."'";
//         $sql .= "         and medicine_prices.start_date <= '".$today."'";
//         $sql .= "         and medicine_prices.deleted_at is null ";
//         //2019.07.31 facilities撲滅 start
//         /*
//         $sql .= "     left join facilities ";
//         $sql .= "         on facilities.id = price_adoptions.facility_id";
//         $sql .= "     left join facilities as trader ";
//         $sql .= "         on trader.id = price_adoptions.trader_id";
//         */
//         $sql .= "     left join user_groups as facilities ";
//         $sql .= "         on facilities.id = price_adoptions.sales_user_group_id";
//         $sql .= "     left join user_groups as trader ";
//         $sql .= "         on trader.id = price_adoptions.purchase_user_group_id";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "     left join users ";
//         $sql .= "         on users.id = price_adoptions.user_id ";
//         $sql .= "     left join v_privileges_row_yakuhinshinsei as vpry ";
//         $sql .= "         on vpry.user_group_id = price_adoptions.sales_user_group_id ";
//         $sql .= "         and vpry.user_id = ".$user->id;

//         $sql .= "     left join v_privileges_row_price_adoptions_bystate as vprpab ";
//         $sql .= "         on vprpab.user_group_id = price_adoptions.sales_user_group_id ";
//         $sql .= "         and vprpab.user_id = ".$user->id;
//         $sql .= "         and vprpab.status = price_adoptions.status ";
//         $sql .= " where ";
//         $sql .= "    medicines.deleted_at is null";

//         // 状態
//         if (!empty($request->status)) {
//             $sql .= " and coalesce(price_adoptions.status,1) in (".implode(',', $request->status).")";

//         }

//         // 施設
//         if (!empty($request->user_group)) {
//             $sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $sql .= " and medicines.code like '%".$request->code."%'";
//         }
//         // 一般名
//         if (!empty($request->popular_name)) {
//             $sql .= " and medicines.popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//         }

//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $sql .= " and pack_units.coefficient = ".$request->coefficient;
//         }

//         // 販売中止日
//         if (!empty($request->discontinuation_date)) {
//             $sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//         }

//         $search_words = [];

//         // 全文検索
//         if (!empty($request->search)) {
//             $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
//             foreach($search_words as $word) {
//                 $sql .= " and medicines.search like '%".$word."%'";
//             }
//         }

//         // 剤型区分
//         if (!empty($request->dosage_type_division)) {
//             $sql .= " and dosage_type_division = ".$request->dosage_type_division;
//         }

//         // 先発後発品区分
//         if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
//             $sql .= " and generic_product_detail_devision = '".$request->generic_product_detail_devision."'";
//         }

//         //2019.08.26 オプション項目追加 start
//         //項目1
//         if (!empty($request->optional_key1)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目2
//         if (!empty($request->optional_key2)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目3
//         if (!empty($request->optional_key3)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目4
//         if (!empty($request->optional_key4)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目5
//         if (!empty($request->optional_key5)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目6
//         if (!empty($request->optional_key6)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目7
//         if (!empty($request->optional_key7)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目8
//         if (!empty($request->optional_key8)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         if (!empty($request->hq_optional_key1)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         if (!empty($request->hq_optional_key2)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key3)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key4)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key5)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key6)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key7)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key8)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         //2019.08.26 オプション項目追加 end

//         $sql .=" ) ";

//         //新規登録分
//         $sql .="union all (";
//         $sql .="  select";
//         $sql .="    medicine_id as medicine_id";
//         $sql .="    , '' as code";
//         $sql .="    , price_adoptions.sales_packaging_code";
//         $sql .="    , '' as generic_product_detail_devision";
//         $sql .="    , '' as popular_name";
//         $sql .="    , jan_code";
//         $sql .="    , price_adoptions.name as medicine_name";
//         $sql .="    , standard_unit";
//         $sql .="    , maker_name";
//         $sql .="    , medicine_price as price";
//         $sql .="    , comment";
//         $sql .="    , price_adoptions.status";
//         $sql .="    , facilities.id as facility_id";
//         $sql .="    , facilities.name as facility_name";
//         $sql .="    , users.name as user_name";
//         $sql .="    , pack_unit";
//         $sql .="    , medicine_price as pack_unit_price";
//         $sql .="    , coefficient";
//         $sql .="    , application_date";
//         $sql .="    ,f_show_trader_name(price_adoptions.status,trader.name,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_name ";
//         $sql .="    , 0 as fm_parent";
//         $sql .="    , 0 as fm_own";

//         $sql .= "   ,f_show_sales_price(price_adoptions.status,price_adoptions.sales_price,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as sales_price ";
// /*
//         $sql .="    ,case ";
//         $sql .="         when status < 7 then";
//         $sql .="             case when coalesce(vpry.show_sales_price_before6,0) <= 0 then null else price_adoptions.sales_price end";
//         $sql .="         else ";
//         $sql .="             case when coalesce(vpry.show_sales_price_over6,0) <= 0 then null else price_adoptions.sales_price end";
//         $sql .="     end as sales_price ";
// */
//         $sql .="    , 0 as fp_id";
//         $sql .="    , price_adoptions.id as price_adoption_id";
//         //CSVダウンロード追加項目 START
//         $sql .= "     ,null as production_maker_name ";
//         $sql .= "     ,null as dispensing_packaging_code ";
//         $sql .= "     ,null as hot_code ";
//         $sql .= "     ,null as dosage_type_division ";
//         $sql .= "     ,price_adoptions.medicine_price as medicine_price ";
//         $sql .= "     ,price_adoptions.pack_unit_price as unit_medicine_price ";
//         $sql .= "     ,null as medicine_price_start_date ";
//         $sql .= "     ,null as facility_price_start_date";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,f_show_trader_name(status,trader.code,coalesce(vpry.show_sales_price_before6,0),coalesce(vpry.show_sales_price_over6,0)) as trader_code ";
//         //2019.07.31 facilities撲滅 end
//         $sql .= "     ,price_adoptions.created_at ";
//         $sql .= "     ,price_adoptions.updated_at ";
//         $sql .= "     ,null as adoption_date ";
//         $sql .= "     ,null as adoption_stop_date ";
//         $sql .= "     ,null as production_stop_date ";
//         $sql .= "     ,null as transitional_deadline ";
//         $sql .= "     ,null as discontinuation_date ";
//         $sql .= "     ,null as discontinuing_division ";
//         //2019.07.31 facilities撲滅 start
//         //$sql .= "     ,facilities.code as facility_code ";
//         //2019.07.31 facilities撲滅 end
//         //CSVダウンロード追加項目 END
//         //$sql .="    ,f_yakuhinshinsei_task_next(price_adoptions.status,vpry.allow2,vpry.allow3,vpry.allow4,vpry.allow5,vpry.allow6,vpry.allow7,vpry.allow8,vpry.allow9,vpry.allow10) as next_status  ";
//         //$sql .="    ,f_yakuhinshinsei_task_reject(price_adoptions.status,vpry.reject1,vpry.reject2,vpry.reject3,vpry.reject4,vpry.reject5,vpry.reject6) as reject_status ";
//         //$sql .="    ,f_yakuhinshinsei_task_withdraw(price_adoptions.status,vpry.withdraw1,vpry.withdraw2,vpry.withdraw3,vpry.withdraw4,vpry.withdraw5,vpry.withdraw6) as withdraw_status";
//         $sql .="    ,vprpab.status_forward  ";
//         $sql .="    ,vprpab.status_back  ";
//         $sql .="    ,vprpab.status_back_text  ";
//         $sql .="    ,'' as optional_key1  ";
//         $sql .="    ,'' as optional_key2  ";
//         $sql .="    ,'' as optional_key3  ";
//         $sql .="    ,'' as optional_key4  ";
//         $sql .="    ,'' as optional_key5  ";
//         $sql .="    ,'' as optional_key6  ";
//         $sql .="    ,'' as optional_key7  ";
//         $sql .="    ,'' as optional_key8  ";
//         $sql .= "     ,''  as hq_optional_key1 ";
//         $sql .= "     ,''  as hq_optional_key2 ";
//         $sql .= "     ,''  as hq_optional_key3 ";
//         $sql .= "     ,''  as hq_optional_key4 ";
//         $sql .= "     ,''  as hq_optional_key5 ";
//         $sql .= "     ,''  as hq_optional_key6 ";
//         $sql .= "     ,''  as hq_optional_key7 ";
//         $sql .= "     ,''  as hq_optional_key8 ";
//         $sql .= "     ,null as facility_price_end_date";
//         $sql .="  from";
//         $sql .="    price_adoptions";
//         $sql .="    inner join ( ";
//         $sql .="        select distinct ";
//         $sql .="            c.user_group_id ";
//         $sql .="        from ";
//         $sql .="            user_groups as b ";
//         $sql .="            inner join user_group_relations as c ";
//         $sql .="                on c.user_group_id = b.id ";
//         $sql .="        where ";
//         $sql .="            c.user_id = ".$user->id;
//         $sql .="    ) as user_groups ";
//         $sql .="        on price_adoptions.sales_user_group_id = user_groups.user_group_id  ";
//         $sql .="    left join makers";
//         $sql .="      on price_adoptions.maker_id = makers.id";
//         //2019.07.31 facilities撲滅 start
//         //$sql .="    left join facilities";
//         //$sql .="      on facilities.id = price_adoptions.facility_id";
//         //$sql .="    left join facilities as trader";
//         //$sql .="      on trader.id = price_adoptions.trader_id";
//         $sql .="    left join user_groups as facilities";
//         $sql .="      on facilities.id = price_adoptions.sales_user_group_id";
//         $sql .="    left join user_groups as trader";
//         $sql .="      on trader.id = price_adoptions.purchase_user_group_id";

//         //2019.07.31 facilities撲滅 end

//         $sql .="    left join users";
//         $sql .="      on users.id = price_adoptions.user_id";
//         $sql .="    left join v_privileges_row_yakuhinshinsei as vpry ";
//         $sql .="      on vpry.user_group_id = price_adoptions.sales_user_group_id ";
//         $sql .="      and vpry.user_id = ".$user->id;

//         $sql .= "   left join v_privileges_row_price_adoptions_bystate as vprpab ";
//         $sql .= "     on vprpab.user_group_id = price_adoptions.sales_user_group_id ";
//         $sql .= "     and vprpab.user_id = ".$user->id;
//         $sql .= "     and vprpab.status = price_adoptions.status ";

//         $sql .="  where";
//         $sql .="    price_adoptions.deleted_at is null";
//         $sql .="    and price_adoptions.medicine_id is null";

//         // 検索条件
//         //
//         if (!empty($request->status)) {
//             $sql .= " and coalesce(price_adoptions.status,1) in (".implode(',', $request->status).")";
//         }
//         // 施設
//         if (!empty($request->user_group)) {
//             $sql .= " and facilities.id in (".implode(',', $request->user_group).")";
//         }
//         // 医薬品CD
//         if (!empty($request->code)) {
//             $sql .= " and price_adoptions.status = -1";
//             //$sql .= " and price_adoptions.code like '%".$request->code."%'";
//         }
//         // 一般名
//         if (!empty($request->popular_name)) {
//             $sql .= " and price_adoptions.status = -1";
//             //$sql .= " and popular_name like '%".mb_convert_kana($request->popular_name, 'KVAS')."%'";
//         }
//         // 販売中止日
//         if (!empty($request->discontinuation_date)) {
//             //$sql .= " and medicines.discontinuation_date > '".date('Y-m-d H:i:s')."'";
//         }
//         // 包装薬価係数
//         if (!empty($request->coefficient)) {
//             $sql .= " and price_adoptions.coefficient = ".$request->coefficient;
//         }
//         // 全文検索
//         if (!empty($request->search)) {
//             foreach($search_words as $word) {
//                 $sql .= " and price_adoptions.search like '%".$word."%'";
//             }
//         }

//         // 先発後発品区分
//         if (!empty($request->generic_product_detail_devision)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         // 剤形区分
//         if (!empty($request->dosage_type_division)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //2019.08.26 オプション項目追加 start
//         //項目1
//         if (!empty($request->optional_key1)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目2
//         if (!empty($request->optional_key2)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目3
//         if (!empty($request->optional_key3)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目4
//         if (!empty($request->optional_key4)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目5
//         if (!empty($request->optional_key5)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目6
//         if (!empty($request->optional_key6)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目7
//         if (!empty($request->optional_key7)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //項目8
//         if (!empty($request->optional_key8)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         if (!empty($request->hq_optional_key1)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         if (!empty($request->hq_optional_key2)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key3)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key4)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key5)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key6)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key7)) {
//             $sql .= " and price_adoptions.status = -1";
//         }
//         if (!empty($request->hq_optional_key8)) {
//             $sql .= " and price_adoptions.status = -1";
//         }

//         //2019.08.26 オプション項目追加 end
//         $sql .=" ) ";

//         $count_sql = " select count(*) from (".$sql.") as tmp ";
//         $all_count = \DB::select($count_sql);

//         $count2=$all_count[0]->count;

//         $sql .=" order by ";
//         $sql .="  maker_name asc ";
//         $sql .=" ,medicine_name asc ";
//         $sql .=" ,standard_unit asc ";
//         $sql .=" ,jan_code asc ";
//         $sql .=" ,facility_id asc ";

//         if ($count == -1) {
//             $per_page = isset($request->page_count) ? $request->page_count : 1;
//         } else {
//             if (!isset($request->page_count)){
//                 $offset=0;
//             } else {
//                 if ($request->page == 0) {
//                     $offset=0;
//                 } else {
//                     $offset=($request->page - 1) * $request->page_count;
//                 }
//             }

//             $per_page = isset($request->page_count) ? $request->page_count : 1;
//             $sql .=" limit ".$per_page." offset ".$offset;

//         }

//         $all_rec = \DB::select($sql);
//         $result = Medicine::query()->hydrate($all_rec);


//         // ページ番号が指定されていなかったら１ページ目
//         $page_num = isset($request->page) ? $request->page : 1;
//         // ページ番号に従い、表示するレコードを切り出す
//         $disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

//         // ページャーオブジェクトを生成
//         $pager= new \Illuminate\Pagination\LengthAwarePaginator(
//                 $result, // ページ番号で指定された表示するレコード配列
//                 $count2, // 検索結果の全レコード総数
//                 $per_page, // 1ページ当りの表示数
//                 $page_num, // 表示するページ
//                 ['path' => $request->url()] // ページャーのリンク先のURLを指定
//                 );

//         return $pager;
//     }
// }
