<?php

namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Model\Facility;
use App\Model\FacilityMedicine;
use App\Model\PriceAdoption;
use App\Model\MedicinePrice;
use Illuminate\Http\Request;
use App\Model\Concerns\Calc as CalcTrait;

class Medicine extends Model
{
    use SoftDeletes;
    use CalcTrait;

    protected $guraded = ['id'];
    protected $fillable = [
        'kon_medicine_id',
        'bunkaren_code',
        'code', 
        'medicine_code',
        'receipt_computerized_processing1',
        'receipt_computerized_processing2', 
        'popular_name', 
        'name', 
        'phonetic', 
        'standard_unit', 
        'package_presentation', 
        'unit', 
        'drug_price_equivalent', 
        'maker_id', 
        'selling_agency_code', 
        'medicine_effet_id', 
        'dosage_type_division', 
        'transitional_deadline', 
        'danger_poison_category1', 
        'danger_poison_category2', 
        'danger_poison_category3', 
        'danger_poison_category4', 
        'danger_poison_category5', 
        'prescription_drug_category', 
        'biological_product_classification', 
        'generic_product_flag', 
        'production_stop_date', 
        'discontinuation_date', 
        'hospital_code', 
        'owner_classification', 
        'deleter', 
        'creater', 
        'updater'
    ];

    const DOSAGE_TYPE_INTERNAL = 1;
    const DOSAGE_TYPE_EXTERNAL = 2;
    const DOSAGE_TYPE_INJECTION = 3;
    const DOSAGE_TYPE_DENTISTRY = 4;
    const DOSAGE_TYPE_OTHER = 5;

    const OWNER_CLASSIFICATION_BUNKAREN = 1;
    const OWNER_CLASSIFICATION_HQ = 2;
    const OWNER_CLASSIFICATION_HOSPITAL = 3;

    const OWNER_CLASSIFICATION_STR = [
        self::OWNER_CLASSIFICATION_BUNKAREN => '文化連',
        self::OWNER_CLASSIFICATION_HQ => '本部',
        self::OWNER_CLASSIFICATION_HOSPITAL => '病院',
        ];

    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT1 = " ";
    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT2 = "生";
    const BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT3 = "特";
    /*
     * 承認申請一覧
     */
    public function listWithApply(Request $request, Facility $facility, $count)
    {
        // サブクエリ
        $pa = new PriceAdoption();

        $p_query = $pa->select(\DB::raw('medicine_id as medicine_id
                               ,\'\' as code
                               ,price_adoptions.name as popular_name
                               ,jan_code
                               ,price_adoptions.name as medicine_name
                               ,standard_unit
                               ,maker_name
                               ,medicine_price as price
                               ,comment
                               ,status
                               ,facilities.id as facility_id
                               ,facilities.name as facility_name
                               ,users.name as user_name
                               ,pack_unit
                               ,medicine_price as pack_unit_price
                               ,coefficient
                               ,application_date
                               ,trader.name as trader_name
                               ,0 as fm_parent
                               ,0 as fm_own
                               ,sales_price
                               ,price_adoptions.id as price_adoption_id'))
                    ->leftJoin('makers', 'price_adoptions.maker_id', '=', 'makers.id')
                    ->leftJoin('facilities', 'facilities.id', '=', 'price_adoptions.facility_id')
                    ->leftJoin('facilities as trader', 'trader.id', '=', 'price_adoptions.trader_id')
                    ->leftJoin('users', 'users.id', '=', 'price_adoptions.user_id')
                    ->getFacility($facility, 'price_adoptions.facility_id');

        // 状態
        if (!empty($request->status)) {
            $p_query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
        }
        // 施設
        if (!empty($request->facility)) {
            $p_query->whereIn('facilities.id', $request->facility);
        }
        // 医薬品CD
        if (!empty($request->code)) {
            $p_query->where('status', -1);
        }
        // 包装薬価係数
        if (!empty($request->coefficient)) {
            $p_query->where("coefficient", \DB::raw($request->coefficient));
        }
        $search_words = [];
        // 全文検索
        if (!empty($request->search)) {
            $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
            foreach($search_words as $word) {
                $p_query->where('price_adoptions.search', 'like', '%'.$word.'%');
            }
        }
        // オーナー区分
        if (!empty($request->owner_classification)) {
            $p_query->where('price_adoptions.owner_classification', $request->owner_classification);
        }
        $p_query->whereNull("price_adoptions.medicine_id");

//-----------------------------------
        // サブクエリ(本部の施設薬品)
        $fm_parent = \DB::table('facility_medicines')->select('facility_id', 'medicine_id')->where('facility_id', $facility->id)->where('deleted_at', null);
        $fm_own = \DB::table('facility_medicines')->select('facility_id', 'medicine_id')->where('facility_id', $facility->id)->where('deleted_at', null);
        if($facility->isHospital()){
            $fm_parent = \DB::table('facility_medicines')->select('facility_id', 'medicine_id')->where('facility_id', $facility->parent()->id)->where('deleted_at', null);
            $fm_own = \DB::table('facility_medicines')->select('facility_id', 'medicine_id')->where('facility_id', $facility->id)->where('deleted_at', null);
        }

        $medicineOnly = \DB::table('price_adoptions')->where('facility_id', $facility->id)->where('deleted_at', null);
        $m_query = $this->select(\DB::raw('medicines.id as medicine_id
                            ,medicines.code
                            ,medicines.popular_name
                            ,pack_units.jan_code as jan_code
                            ,medicines.name as medicine_name
                            ,medicines.standard_unit as standard_unit
                            ,( CASE WHEN pa.maker_name is null THEN makers.name ELSE pa.maker_name END ) as maker_name
                            ,medicine_prices.price as price
                            ,pa.comment as comment
                            ,(
                                CASE WHEN fm_own.medicine_id is null 
                                    THEN
                                        CASE
                                            WHEN fm_parent.medicine_id is not null THEN ' . Task::STATUS_ADOPTABLE .
                                            'ELSE pa.status 
                                        END
                                    ELSE ' . Task::STATUS_DONE . 'END
                            ) as status
                            ,(CASE WHEN fm_own is not null THEN fm_own.facility_id ELSE fm_parent.facility_id END) as facility_id
                            ,(CASE WHEN own_facility.name is null THEN \'\' ELSE own_facility.name END) as facility_name
                            ,\'\' as user_name
                            ,pack_units.total_pack_unit
                            ,pack_units.price as pack_unit_price
                            ,pack_units.coefficient
                            ,pa.application_date as application_date
                            ,\'\' as trader_name
                            ,fm_parent.medicine_id as fm_parent
                            ,fm_own.medicine_id as fm_own
                            ,pa.sales_price
                            ,pa.id as price_adoption_id'
                            ))
                            ->join('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
                            ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
                            ->join('medicine_prices', function ($query) {
                                $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
                                ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
                            })
                            ->leftJoinSub($medicineOnly, 'pa', function ($join) {
                                $join->on('medicines.id', '=', 'pa.medicine_id');
                            })
                            ->leftJoinSub($fm_parent, 'fm_parent', function ($join) {
                                $join->on('medicines.id', '=', 'fm_parent.medicine_id');
                            })
                            ->leftJoinSub($fm_own, 'fm_own', function ($join) {
                                $join->on('medicines.id', '=', 'fm_own.medicine_id');
                            })
                            ->leftJoin('facilities as own_facility', 'own_facility.id', '=', 'fm_own.facility_id');
                                        
            // 検索条件
            // 状態
            if (!empty($request->status)) {
                if (in_array(Task::STATUS_ADOPTABLE, $request->status)){
                    $m_query->whereIn(\DB::raw('coalesce(
                        (
                            CASE WHEN fm_own.medicine_id is null
                                THEN
                                    CASE
                                        WHEN fm_parent.medicine_id is not null THEN ' .  Task::STATUS_ADOPTABLE .
                                        'ELSE pa.status
                                    END
                                ELSE ' . Task::STATUS_DONE .' END
                        ),1)'), $request->status);
                } else {
                     $m_query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
                }
            }
            // 医薬品CD
            if (!empty($request->code)) {
                $m_query->where('medicines.code', 'like', '%'.$request->code.'%');
            }
            // 一般名
            if (!empty($request->popular_name)) {
                $m_query->where('medicines.popular_name', 'like', '%'.mb_convert_kana($request->popular_name, 'KVAS').'%');
            }
            // 施設
            if (!empty($request->facility)) {
                $m_query->whereIn('fm_own.facility_id', $request->facility);
            }
            // 販売中止日
            if (!empty($request->discontinuation_date)) {
                $m_query->where('medicines.discontinuation_date', '>', date('Y-m-d H:i:s'));
            }
            // 包装薬価係数
            if (!empty($request->coefficient)) {
                $m_query->where("pack_units.coefficient", \DB::raw($request->coefficient));
            }
            // 全文検索
            if (!empty($request->search)) {
                foreach($search_words as $word) {
                    $m_query->where('medicines.search', 'like', '%'.$word.'%');
                }
            }
            $m_query->whereNull("pa.status");
//-----------------------------------

        $query = $this->select(\DB::raw('medicines.id as medicine_id
                              ,medicines.code
                              ,medicines.popular_name
                              ,pack_units.jan_code as jan_code
                              ,medicines.name as medicine_name
                              ,medicines.standard_unit as standard_unit
                              ,( CASE WHEN pa.maker_name is null THEN makers.name ELSE pa.maker_name END ) as maker_name
                              ,medicine_prices.price as price
                              ,pa.comment as comment
                              ,pa.status as status
                              ,facilities.id as facility_id
                              ,facilities.name as facility_name
                              ,users.name as user_name
                              ,pack_units.total_pack_unit
                              ,pack_units.price as pack_unit_price
                              ,pack_units.coefficient
                              ,pa.application_date as application_date
                              ,trader.name as trader_name
                              ,fm_parent.medicine_id as fm_parent
                              ,fm_own.medicine_id as fm_own
                              ,pa.sales_price
                              ,pa.id as price_adoption_id'
                             ))
                ->joinSub($pa->getFacility($facility), 'pa', function($join){
                    $join->on('medicines.id', '=', 'pa.medicine_id');
                })->unionAll($m_query)->unionAll($p_query)
                ->join('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
                ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
                ->join('medicine_prices', function ($query) {
                    $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
                    ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
                })
                ->leftJoin('facilities', 'facilities.id', '=', 'pa.facility_id')
                ->leftJoin('facilities as trader', 'trader.id', '=', 'pa.trader_id')
                ->leftJoinSub($fm_parent, 'fm_parent', function ($join) {
                    $join->on('medicines.id', '=', 'fm_parent.medicine_id');
                })
                ->leftJoinSub($fm_own, 'fm_own', function ($join) {
                    $join->on('medicines.id', '=', 'fm_own.medicine_id');
                })
                ->leftJoin('users', 'users.id', '=', 'pa.user_id');

        // 検索条件
        // 状態
        if (!empty($request->status)) {
            $query->whereIn(\DB::raw('coalesce(status,1)'), $request->status);
        }
        // 施設
        if (!empty($request->facility)) {
            $query->whereIn('facilities.id', $request->facility);
        }
        // 医薬品CD
        if (!empty($request->code)) {
            $query->where('medicines.code', 'like', '%'.$request->code.'%');
        }
        // 一般名
        if (!empty($request->popular_name)) {
            $query->where('popular_name', 'like', '%'.mb_convert_kana($request->popular_name, 'KVAS').'%');
        }
        // 販売中止日
        if (!empty($request->discontinuation_date)) {
            $query->where('medicines.discontinuation_date', '>', date('Y-m-d H:i:s'));
        }
        // 包装薬価係数
        if (!empty($request->coefficient)) {
            $query->where("pack_units.coefficient", \DB::raw($request->coefficient));
        }
        // 全文検索
        if (!empty($request->search)) {
            foreach($search_words as $word) {
                $query->where('medicines.search', 'like', '%'.$word.'%');
            }
        }
//echo $query->toSql();
        $query->orderBy('maker_name', 'asc')->orderBy('medicine_name', 'asc')->orderBy('standard_unit', 'asc');
        return $query->paginate($count);
    }

    /*
     * 薬価の取得
     */
    public function medicinePrice()
    {
        return $this->hasOne('App\Model\MedicinePrice')->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
//        return $this->hasOne('App\Model\MedicinePrice');
    }

    /*
     * 薬価の取得
     */
    public function getMedicinePrices($medicine_id)
    {
        return $this->where('id', $medicine_id)
        ->where('medicine_id', $medicine_id)->first();
    }

    /*
     * 包装マスタの取得
     */
    public function packUnit()
    {
        return $this->hasOne('App\Model\PackUnit');
    }

    /*
     * 製造メーカーの取得
     */
    public function maker()
    {
        return $this->belongsTo('App\Model\Maker');
    }

    /*
     * 販売メーカーの取得
     */
    public function sellingMaker()
    {
        return $this->belongsTo('App\Model\Maker', 'selling_agency_code');
    }

    /* 
     * 毒物区分
     */
    public function getDangerPoison()
    {
        $text = [];
        if ($this->biological_product_classification == "1") {
        }else if($this->biological_product_classification == "2"){
            $text[] = Medicine::BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT2;
        }else if($this->biological_product_classification == "3"){
            $text[] = Medicine::BIOLOGICAL_PRODUCT_CLASSIFICATION_TEXT3;
        }
        if ($this->danger_poison_category1) {
            $text[] = $this->danger_poison_category1;
        }
        if ($this->danger_poison_category2) {
            $text[] = $this->danger_poison_category2;
        }
        if ($this->danger_poison_category3) {
            $text[] = $this->danger_poison_category3;
        }
        if ($this->danger_poison_category4) {
            $text[] = $this->danger_poison_category4;
        }
        if ($this->danger_poison_category5) {
            $text[] = $this->danger_poison_category5;
        }
        return implode("・", $text);
    }

    /*
     * 承認申請一覧
     */
    public function listWithApply2($medicine_id, $user, $count)
    {
        if(isHospital()){
            $facility = $user->facility->parent();
        }else{
            $facility = $user->facility;
        }
        // サブクエリ
        $fm = new FacilityMedicine();

        $query = $this->select(\DB::raw('medicines.id as medicine_id
                            ,medicines.code
                            ,medicines.popular_name
                            ,medicines.name as medicine_name
                            ,medicines.standard_unit as standard_unit
                            ,fm.comment as comment
                            ,facilities.name as facility_name
                            ,users.name as user_name
                            ,fm.adoption_date
                            ,fm.adoption_stop_date
                            ,facility_prices.sales_price'
                            ))
                ->leftJoinSub($fm->getFacility($facility), 'fm', function($join){
                    $join->on('medicines.id', '=', 'fm.medicine_id');
                })
                ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'fm.id')
                ->leftJoin('facilities', 'facilities.id', '=', 'fm.facility_id')
                ->leftJoin('users', 'users.id', '=', 'fm.user_id');
                
        $query->where('fm.medicine_id', $medicine_id);
        $query->orderBy('facility_name', 'asc');

        return $query->paginate($count);
    }    

    /*
     * 一覧用のsalesPrice取得
     */
    public function getSalesPrice()
    {
        if (!$this->sales_price) {
            $fp = $this->getFacilityPrice();

            if (!is_null($fp)) {
                return $fp->sales_price;
            }
        }
        return $this->sales_price;
    }

    /*
     * 一覧用のsalesPrice取得
     */
    public function getTraderName()
    {
        if (empty($this->trader_name)) {
            $fp = $this->getFacilityPrice();
            if (!is_null($fp)) {
                $trader = Facility::find($fp->trader_id);
                return $trader->name;
            }
        }
        return $this->trader_name;
    }

    /*
     * 一覧のデータのfacility_medicineを取得
     */
    public function getFacilityPrice()
    {
        if (!$this->fm_parent && !$this->fm_own) {
            return null;
        }
        $medicine_id = null;
        if ($this->fm_parent) {
            $medicine_id = $this->fm_parent;
        }
        if ($this->fm_own) {
            $medicine_id = $this->fm_own;
        }
        
        if (!is_null($medicine_id)) {
            $medicine = self::find($medicine_id);
            $fmModel = new FacilityMedicine();
            $fm = $fmModel->getData($this->facility_id, $medicine_id);
            return ($fm) ? $fm->facilityPrice : null;
        }
        return null;

    }

    /*
     * 標準薬品一覧
     */
    public function listWithMedicines(Request $request, $count)
    {
        // サブクエリ
        $query = $this->select('medicines.id as medicine_id' 
                              ,'medicines.kon_medicine_id'
                              ,'pack_units.jan_code'
                              ,'medicines.name as medicine_name'
                              ,'medicines.standard_unit as standard_unit'
                              ,'makers.name as maker_name'
                              ,'medicine_prices.price as price'
                              ,'medicine_effects.name as medicine_effect'
                             )
                ->join('pack_units', 'pack_units.medicine_id', '=', 'medicines.id')
                ->join('makers', 'makers.id', '=', 'medicines.maker_id')
                ->join('medicine_prices', 'medicine_prices.medicine_id', '=', 'medicines.id')
                ->leftJoin('medicine_effects', 'medicine_effects.id', '=', 'medicines.medicine_effet_id');
        // 検索条件
        // JANコード
        if (!empty($request->jan_code)) {
            $query->where('jan_code', $request->jan_code);
        }
        // 一般名
        if (!empty($request->popular_name)) {
            $query->where('popular_name', 'like', '%'.$request->popular_name.'%');
        }
        // 商品名
        if (!empty($request->name)) {
            $query->where('medicines.name', 'like', '%'.$request->name.'%');
        }
        // 剤型区分
        if (!empty($request->dosage_type_division)) {
            $query->where('dosage_type_division', $request->dosage_type_division);
        }
        // オーナー区分
        if (!empty($request->owner_classification)) {
            $query->where('owner_classification', $request->owner_classification);
        }
        // 全文検索
        if (!empty($request->search)) {
            $query->where('medicines.search', 'like', '%'.$request->search.'%');
        }
        return $query->paginate($count);
    }
}
