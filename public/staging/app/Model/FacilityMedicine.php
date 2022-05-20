<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Concerns\UserGroup as UserGroupTrait;
use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Model\UserGroup;
use App\Observers\AuthorObserver;

class FacilityMedicine extends Model
{
    use SoftDeletes;
    use UserGroupTrait;
    use CalcTrait;

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * 標準薬品を取得
     */
    public function medicine()
    {
        return $this->belongsTo('App\Model\Medicine');
    }

    /*
     * メーカーの取得
     */
    public function maker()
    {
        return $this->belongsTo('App\Model\Maker');
    }

    /*
     * 施設の取得
     */
    // public function facility()
    // {
    //     return $this->belongsTo('App\Model\Facility');
    // }

    /*
     * 採用一覧
     */
    public function getList(Request $request, UserGroup $userGroup, $count)
    {

        if($count < 0){

            $query = $this->select(\DB::raw(
            "makers.name as maker_name
            ,production_maker.name as production_maker_name
            ,(CASE WHEN medicines.name is null THEN price_adoptions.name ELSE medicines.name END) as medicine_name
            ,medicines.popular_name
            ,(CASE WHEN medicines.standard_unit is null THEN price_adoptions.standard_unit ELSE medicines.standard_unit END) as standard_unit
            ,medicines.sales_packaging_code
            ,medicines.dispensing_packaging_code
            ,pack_units.hot_code
            ,(CASE WHEN pack_units.jan_code is null THEN price_adoptions.jan_code ELSE pack_units.jan_code END) as jan_code
            ,medicines.code
            ,medicines.generic_product_detail_devision
            ,medicines.dosage_type_division
            ,(CASE WHEN medicine_prices.price is null THEN price_adoptions.medicine_price ELSE medicine_prices.price END) as medicine_price
            ,(CASE WHEN pack_units.coefficient is null THEN price_adoptions.coefficient ELSE pack_units.coefficient END) as pack_units_coefficient
            ,(CASE WHEN facility_medicines.medicine_id is null THEN price_adoptions.pack_unit_price * price_adoptions.coefficient ELSE medicine_prices.price * pack_units.coefficient END) as unit_medicine_price
            ,facility_prices.sales_price
            ,(CASE WHEN pack_units.coefficient is null THEN round( ( 1 - ( NULLIF(facility_prices.sales_price, 0) / cast( NULLIF(price_adoptions.pack_unit_price, 0) * NULLIF(price_adoptions.coefficient, 0) as numeric) ) ) * 100, 3) ELSE round( ( 1 - ( NULLIF(facility_prices.sales_price, 0) / cast( NULLIF(medicine_prices.price, 0) * NULLIF(pack_units.coefficient, 0) as numeric) ) ) * 100, 3) END) as discount
            ,round( cast( NULLIF(facility_prices.sales_price, 0) / NULLIF(pack_units.price, 0) as numeric) * 100, 3 ) as markup
            ,(CASE WHEN facility_medicines.medicine_id is null THEN null ELSE medicine_prices.start_date END) as medicine_price_start_date
            ,facility_prices.start_date
            ,'' as trader_code
            ,trader.name as trader_name
            ,price_adoptions.created_at
            ,price_adoptions.updated_at
            ,facility_medicines.adoption_date
            ,facility_medicines.adoption_stop_date
            ,medicines.production_stop_date
            ,medicines.transitional_deadline
            ,medicines.discontinuation_date
            ,medicines.owner_classification
            ,'' as facility_code
            ,user_groups.name as facility_name
            ,users.name as user_name
            ,facility_medicines.comment
            "
            )
           )
//            ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'facility_medicines.id')
            ->leftJoin('facility_prices', function ($query) {
                $query->on('facility_medicines.id', '=', 'facility_prices.facility_medicine_id')
                ->where('facility_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('facility_prices.start_date', '<=', date('Y-m-d H:i:s'));
            })
            ->leftJoin('medicines', 'facility_medicines.medicine_id', '=', 'medicines.id')
            // ->leftJoin('facilities', 'facility_medicines.facility_id', '=', 'facilities.id')
            ->leftJoin('user_groups', 'facility_medicines.sales_user_group_id', '=', 'user_groups.id')
            ->leftJoin('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
            ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
            ->leftJoin('makers as production_maker', 'medicines.maker_id', '=', 'production_maker.id')
            ->leftJoin('medicine_prices', function ($query) {
                $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
                ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
            })
            ->leftJoin('price_adoptions', 'facility_medicines.price_adoption_id', '=', 'price_adoptions.id')
            // ->leftJoin('facilities as trader', 'trader.id', '=', 'facility_prices.trader_id')
            ->leftJoin('user_groups as trader', 'trader.id', '=', 'facility_prices.trader_id')
            // ->getFacility($facility, 'facility_medicines.facility_id', true)
            ->getUserGroupScope($userGroup, 'facility_medicines.sales_user_group_id', true)
            ->leftJoin('users', 'users.id', '=', 'price_adoptions.user_id');

        }else{
            $query = $this->select('facility_medicines.id as facility_medicine_id'
            ,'facility_medicines.price_adoption_id as pa_id'
            ,'facility_medicines.medicine_id as medicine_id'
            ,'facility_medicines.adoption_stop_date'
            ,'pack_units.jan_code'
            ,'medicines.sales_packaging_code'
            ,'medicines.generic_product_detail_devision'
            ,'facility_medicines.maker_name as fm_maker_name'
            ,'makers.name as maker_name'
            ,'medicines.name as medicine_name'
            ,'medicines.owner_classification'
            ,'price_adoptions.owner_classification'
            ,'medicines.dosage_type_division'
            ,'medicines.standard_unit as standard_unit'
            ,'makers.name as facility_name'
            ,'pack_units.coefficient as pack_units_coefficient'
            ,'pack_units.price as pack_unit_price'
            ,'medicine_prices.price as medicine_price'
            ,'price_adoptions.jan_code as pa_jan_code'
            ,'price_adoptions.name as pa_name'
            ,'price_adoptions.maker_name as pa_maker_name'
            ,'facility_prices.sales_price'
            ,'trader.name as trader_name'
            ,'price_adoptions.standard_unit as pa_standard_unit'
            ,'price_adoptions.coefficient as pa_coefficient'
            ,'price_adoptions.pack_unit_price as pa_pack_unit_price'
           )
//           ->leftJoin('facility_prices', 'facility_prices.facility_medicine_id', '=', 'facility_medicines.id')
           ->leftJoin('facility_prices', function ($query) {
                $query->on('facility_medicines.id', '=', 'facility_prices.facility_medicine_id')
                ->where('facility_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('facility_prices.start_date', '<=', date('Y-m-d H:i:s'));
            })
           ->leftJoin('medicines', 'facility_medicines.medicine_id', '=', 'medicines.id')
            // ->leftJoin('facilities', 'facility_medicines.facility_id', '=', 'facilities.id')
            ->leftJoin('user_groups', 'facility_medicines.sales_user_group_id', '=', 'user_groups.id')
            ->leftJoin('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
            ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
            ->leftJoin('medicine_prices', function ($query) {
                $query->on('medicines.id', '=', 'medicine_prices.medicine_id')
                ->where('medicine_prices.end_date', '>=', date('Y-m-d H:i:s'))->where('medicine_prices.start_date', '<=', date('Y-m-d H:i:s'));
            })
            ->leftJoin('price_adoptions', 'facility_medicines.price_adoption_id', '=', 'price_adoptions.id')
            // ->leftJoin('facilities as trader', 'trader.id', '=', 'facility_prices.trader_id')
            ->leftJoin('user_groups as trader', 'trader.id', '=', 'facility_prices.trader_id')
            // ->getFacility($facility, 'facility_medicines.facility_id', true);
            ->getUserGroupScope($userGroup, 'facility_medicines.sales_user_group_id', true);
        }

        // 検索条件
        // 施設
        if (!empty($request->facility)) {
            $query->whereIn('user_groups.id', $request->facility);
        }
        // 医薬品CD
        if (!empty($request->code)) {
            $query->where('medicines.code', 'like', '%'.$request->code.'%');
        }
        // 一般名
        if (!empty($request->popular_name)) {
            $query->where('medicines.popular_name', 'like', '%'.mb_convert_kana($request->popular_name, 'KVAS').'%');
        }
        // 包装薬価係数
        if (!empty($request->coefficient)) {
            $coefficient = \DB::raw($request->coefficient);
            $query->where(function($q) use ( $coefficient ) {
                $q->where('pack_units.coefficient', $coefficient)
                    ->orWhere('price_adoptions.coefficient', $coefficient);
            });
        }
        // 採用中止日
        if (!empty($request->adoption_stop_date)) {
            $query->whereNull('facility_medicines.adoption_stop_date');
        }

        // 全文検索
        if (!empty($request->search)) {
            $search_words = explode(' ', mb_convert_kana($request->search, 'KVAs'));
            foreach($search_words as $word) {
                $query->where(function($q) use ($word) {
                    $q->where('price_adoptions.search', 'like', '%'.$word.'%')
                     ->orWhere('medicines.search', 'like', '%'.$word.'%');
                });
            }
        }

        // 剤型区分
        if (!empty($request->dosage_type_division)) {
        	$query->where('dosage_type_division', $request->dosage_type_division);
        }

        // 先発後発品区分
        if (!is_null($request->generic_product_detail_devision) && strlen($request->generic_product_detail_devision) > 0) {
        	$query->where('generic_product_detail_devision', $request->generic_product_detail_devision);
        }


        if($count < 0){
            return $query->get();
        }else{
            return $query->paginate($count);
        }
    }

    /*
     * JANコード
     */
    public function getJancode()
    {
        return ($this->isRegisted()) ? $this->pa_jan_code : $this->jan_code;
    }

    /*
     * メーカー名
     */
    public function getMakerName()
    {
        return ($this->isRegisted()) ? $this->fm_maker_name : $this->maker_name;
    }

    /*
     * 薬名
     */
    public function getMedicineName()
    {
        return ($this->isRegisted()) ? $this->pa_name : $this->medicine_name;
    }

    /*
     * 規格単位
     */
    public function getStandardUnit()
    {
        return ($this->isRegisted()) ? $this->pa_standard_unit : $this->standard_unit;
    }

    /*
     * 包装薬価係数
     * getListの戻り値に対して使うメソッド
     */
    public function getCoefficient()
    {
        return ($this->isRegisted()) ? $this->pa_coefficient : $this->pack_units_coefficient;
    }

    /*
     * 包装薬価
     * getListの戻り値に対して使うメソッド
     */
    public function getPackUnitPrice()
    {
        return ($this->isRegisted()) ? $this->pa_pack_unit_price : $this->pack_unit_price;
    }

    /*
     * 登録された薬品かどうか
     */
    public function isRegisted()
    {
        return is_null($this->maker_name);
    }

    /*
     * ユーザーの取得
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*
     * 施設と薬品でデータ取得
     */
    public function getData(int $user_group_id, int $medicine_id)
    {
        return $this->where('sales_user_group_id', $user_group_id)->where('medicine_id', $medicine_id)->first();
    }

    /*
     * 自施設のデータ取得
     */
    public function getOwnData(int $user_group_id, int $medicine_id, int $user_id)
    {
    	return $this->where('sales_user_group_id', $user_group_id)->where('medicine_id', $medicine_id)->where('user_id', $user_id)->first();
    }

    /*
     * 施設薬品の価格
     */
    public function facilityPrice()
    {
        return $this->hasOne('App\Model\FacilityPrice')->where('end_date', '>=', date('Y-m-d H:i:s'))->where('start_date', '<=', date('Y-m-d H:i:s'));
    }

}
