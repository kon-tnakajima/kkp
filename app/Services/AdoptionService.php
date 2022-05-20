<?php
declare(strict_types=1);
namespace App\Services;

use App\User;
use App\Model\Medicine;
use App\Model\PriceAdoption;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use App\Model\UserGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Apply;

class AdoptionService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->medicine = new Medicine();
        $this->priceAdoption = new PriceAdoption();
        $this->facilityMedicine = new FacilityMedicine();
        $this->facilityPrice = new FacilityPrice();
        $this->user = new User();
        $this->userGroup = new UserGroup();
    }
    /*
     * 採用申請一覧取得
     */
    public function getAdoptionList(Request $request, $user)
    {

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->facilityMedicine->getList($request, $user->userGroup(), $count);

        return $list;
    }

    /*
     * 採用申請詳細取得
     */
    public function getAdoptionDetail(Request $request)
    {
        $detail = $this->facilityMedicine->find($request->id);
        if (is_null($detail->medicine_id) ) {
            $detail->priceAdoption = $this->priceAdoption->find($detail->price_adoption_id);
            $facility_price = $this->facilityPrice->getData($detail->id);

            $detail->jan_code = $detail->priceAdoption->jan_code;
            $detail->name = $detail->priceAdoption->name;
            $detail->phonetic = $detail->priceAdoption->phonetic;
            $detail->standard_unit = $detail->priceAdoption->standard_unit;
            $detail->unit = $detail->priceAdoption->unit;
            $detail->maker_name = $detail->priceAdoption->maker_name;
            $detail->medicine_price = $detail->priceAdoption->medicine_price;
            $detail->pack_unit = $detail->priceAdoption->pack_unit;

            $detail->start_date = $facility_price->start_date;
            $detail->end_date = $facility_price->end_date;

            $detail->selling_maker_name = "";
            $detail->popular_name = "";
            $detail->coefficient = "";
            $detail->pack_count = "";
            $detail->code = "";
            $detail->pack_unit_price = $detail->priceAdoption->pack_unit_price * $detail->priceAdoption->coefficient;
            $detail->production_stop_date = "";
            $detail->transitional_deadline = "";
            $detail->discontinuation_date = "";
            $detail->dosage_type_division = "";
            $detail->danger_poison = "";
            $detail->owner_classification = "";
            $detail->comment = $detail->comment;
        } else {
            $detail->priceAdoption = $this->priceAdoption->getData($detail->facility_id, $detail->medicine_id);
            $medicine = $this->medicine->find($detail->medicine_id);
            $facility_price = $this->facilityPrice->getData($detail->id);

            $detail->jan_code = $medicine->packUnit->jan_code;
            $detail->name = $medicine->name;
            $detail->phonetic = $medicine->phonetic;
            $detail->standard_unit = $medicine->standard_unit;
            $detail->unit = $medicine->unit;
            if(!empty($medicine->maker)){
                $detail->maker_name = $medicine->maker->name;
            }else{
                $detail->selling_maker_name = $detail->maker_name;
            }
            $detail->medicine_price = $medicine->medicinePrice->price;
            $detail->pack_unit = $medicine->packUnit->pack_unit;

            $detail->start_date = $facility_price->start_date;
            $detail->end_date = $facility_price->end_date;

            if(!empty($medicine->sellingMaker)){
                $detail->selling_maker_name = $medicine->sellingMaker->name;
            }else{
                $detail->selling_maker_name = $detail->maker_name;
            }

            $detail->popular_name = $medicine->popular_name;
            $detail->coefficient = $medicine->packUnit->coefficient;
            $detail->pack_count = $medicine->packUnit->pack_count;
            $detail->code = $medicine->code;
            $detail->pack_unit_price = $medicine->medicinePrice->price * $medicine->packUnit->coefficient;
            $detail->production_stop_date = $medicine->production_stop_date;
            $detail->transitional_deadline = $medicine->transitional_deadline;
            $detail->discontinuation_date = $medicine->discontinuation_date;
            $detail->dosage_type_division = $medicine->dosage_type_division;
            $detail->danger_poison = $medicine->getDangerPoison();
            $detail->owner_classification = $medicine->owner_classification;

            //グランドマスタ追加
            $detail->sales_packaging_code = $medicine->sales_packaging_code;
            $detail->dispensing_packaging_code = $medicine->dispensing_packaging_code;
            $detail->hot_code = $medicine->packUnit->hot_code;
            $detail->medicine_price_revision_date = date('Y/m/d',  strtotime($medicine->medicine_price_revision_date) );
            $detail->generic_product_detail_devision = $medicine->generic_product_detail_devision;
            $detail->medicine_code = $medicine->medicine_code;

            $text = [];
            if ( !empty($medicine->room_temperature) ) {
                $text[] = $medicine->room_temperature;
            }
            if ( !empty($medicine->cold_place) ) {
                $text[] = $medicine->cold_place;
            }
            if ( !empty($medicine->refrigeration) ) {
                $text[] = $medicine->refrigeration;
            }
            if ( !empty($medicine->forzen) ) {
                $text[] = $medicine->forzen;
            }
            $detail->storage_temperature = implode("・", $text);

            $text2 = [];
            if ( !empty($medicine->dark_place) ) {
                $text2[] = $medicine->dark_place;
            }
            if ( !empty($medicine->shade) ) {
                $text2[] = $medicine->shade;
            }
            $detail->storage_place = implode("・", $text2);
        }

        $trader = $this->getTrader($facility_price->trader_id);
        $detail->fp_id = $facility_price->id;

        if(!empty($trader)){
            $detail->trader_name = $trader->name;
        }else{
            $detail->trader_name = "";
        }

        if (!empty($detail->updater)) {
            $updater = $this->user->find($detail->updater);
            $detail->updater_name = $updater->name;
        }

        // $detail->facility_name = $detail->facility->name;
        $detail->user_name = (is_null($detail->user)) ? "" : $detail->user->name;
        $detail->sales_price = $facility_price->sales_price;
        $detail->purchase_price = $facility_price->purchase_price;
        $detail->facility_price_start_date = date('Y/m/d',  strtotime($facility_price->start_date) );

        if(!empty($request->page)){
            $detail->page = $request->page;
        }

        return $detail;
    }

    /*
     * 更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $facilityMedicine = $this->facilityMedicine->find($request->id);
            if (is_null($facilityMedicine)) {
                $request->session()->flash('errorMessage', 'データが不正です。');
                return false;
            }

            $facilityMedicine->adoption_stop_date = $request->adoption_stop_date;
            $facilityMedicine->comment = $request->comment;
            $facilityMedicine->save();
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            return true;

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }
    /*
     * 検索フォームの施設チェックボックスに使用する施設一覧
     */
    public function getUserGroups($user)
    {
        return $this->userGroup->getUserGroups($user);
        // return $this->facility->getFacility($user->facility, 'id', true)->get();
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function getConditions(Request $request)
    {
        // 検索条件
        $conditions = array();
        $conditions['facility'] = array();
        $conditions['popular_name'] = '';
        $conditions['code'] = '';
        $conditions['coefficient'] = '';
        $conditions['adoption_stop_date'] = 1;
        $conditions['search'] = '';
        $conditions['dosage_type_division'] = '';
        $conditions['generic_product_detail_devision'] = null;

        $is_cond = false;
        $conditions['is_condition'] = $is_cond;

        //検索実行の場合
        if (!empty($request->is_search)) {
            // 医薬品CD
            if (!empty($request->code)) {
                $conditions['code'] = $request->code;
                $is_cond = true;
            }
            // 一般名
            if (!empty($request->popular_name)) {
                $conditions['popular_name'] = $request->popular_name;
                $is_cond = true;
            }
            // 包装薬価係数
            if (!empty($request->coefficient)) {
                $conditions['coefficient'] = $request->coefficient;
                $is_cond = true;
            }
            // 採用中止日
            if (!empty($request->adoption_stop_date)) {
                $conditions['adoption_stop_date'] = 1;
                $is_cond = true;
            }else{
                $conditions['adoption_stop_date'] = 0;
                $is_cond = true;
            }
            // 施設
            if (!empty($request->facility)) {
                $conditions['facility'] = $request->facility;
                $is_cond = true;
            }else{
                $conditions['facility'] = array();
            }
            // 剤型区分
            if (!empty($request->dosage_type_division)) {
            	$conditions['dosage_type_division'] = $request->dosage_type_division;
            	$is_cond = true;
            }
            // 先発後発品区分
            if (!is_null($request->generic_product_detail_devision) && $request->generic_product_detail_devision != "") {
            	$conditions['generic_product_detail_devision'] = $request->generic_product_detail_devision;
            	$is_cond = true;
            }

            // 全文検索
            if (!empty($request->search)) {
                $conditions['search'] = $request->search;
            }
            $conditions['is_condition'] = $is_cond;
            session()->put(['adoption_conditions' => $conditions]);
        }else{
            if( !empty(session()->get('adoption_conditions')) ){
                $conditions = session()->get('adoption_conditions');

                //リクエストに値を設定する。
                // 施設
                if (!empty( $conditions['facility'] )) {
                    $request->facility = $conditions['facility'];
                }else{
                    $request->facility = array();
                }
                // 医薬品CD
                if (!empty( $conditions['code'] )) {
                    $request->code = $conditions['code'];
                }
                // 一般名
                if (!empty( $conditions['popular_name'] )) {
                    $request->popular_name = $conditions['popular_name'];
                }
                // 包装薬価係数
                if (!empty( $conditions['coefficient'] )) {
                    $request->coefficient = $conditions['coefficient'];
                }
                // 採用中止日
                if (!empty($conditions['adoption_stop_date'])) {
                    $request->adoption_stop_date = $conditions['adoption_stop_date'];
                }
                // 剤型区分
                if (!empty($conditions['dosage_type_division'])) {
                	$request->dosage_type_division = $conditions['dosage_type_division'];
                }

                // 先発後発品区分
                if (!is_null($conditions['generic_product_detail_devision']) && $conditions['generic_product_detail_devision'] != "") {
                	$request->generic_product_detail_devision = $conditions['generic_product_detail_devision'];
                }

                // 全文検索
                if (!empty( $conditions['search'] )) {
                    $request->search = $conditions['search'];
                }
            }
        }
        // 採用中止日
        if (!empty($conditions['adoption_stop_date'])) {
            $request->adoption_stop_date = $conditions['adoption_stop_date'];
            $conditions['is_condition'] = true;
        }

        return $conditions;
    }

    /*
     * 採用申請一覧取得
     */
    public function getAdoptionListForCSV(Request $request, $user)
    {
        $list =  $this->facilityMedicine->getList($request, $user->userGroup(), -1);
        $data = $list->toArray();

        $csv_data = array();
        foreach ($data as $row) {
            if(!empty($row['dosage_type_division'])){
                $row['dosage_type_division'] = \App\Helpers\Apply\getDosageTypeDivisionText($row['dosage_type_division']);
            }
            if(!empty($row['owner_classification'])){
                $row['owner_classification'] = \App\Helpers\Apply\getOwnerClassification( $row['owner_classification'] );
            }
            if(isset($row['generic_product_detail_devision'])){
                $row['generic_product_detail_devision'] = \App\Helpers\Apply\getGenericProductDetailDivision( $row['generic_product_detail_devision'] );
            }
            if(!empty($row['discount'])){
                if($row['discount'] < 0){
                $row['discount'] = $row['discount'] * -1;
                $row['discount'] = "▲".$row['discount'];
                }
                $row['discount'] = $row['discount']."%";
            }
            if(!empty($row['markup'])){
                if($row['markup'] < 0){
                $row['markup'] = $row['markup'] * -1;
                $row['markup'] = "▲".$row['markup'];
                }
                $row['markup'] = $row['markup']."%";
            }
            $csv_data[] = $row;
        }

        $csvHeader = [  '販売メーカー名', '製造メーカー名', '商品名', '一般名', '規格容量', 'GS1販売', 'GS1調剤', 'HOT番号', 'JANコード'
                        , '医薬品CD', '先後発区分', '剤型', '単位薬価', '包装薬価係数', '包装薬価', '包装納入単価'
                        , '値引率(納入)', '掛率(納入)','薬価有効開始日','納入価有効開始日', '業者コード', '業者', '申請日'
                        , '更新日', '採用日', '採用中止日', '製造中止日', '経過措置期限', '販売中止日', 'オーナー区分'
                        , '施設コード', '施設名', '申請者', 'コメント'
        ];

        array_unshift($csv_data, $csvHeader);

        return $csv_data;
    }

    /*
     * 業者を取得
     */
    public function getTrader($trader_id)
    {
        return $this->userGroup->where('id', $trader_id)->where('group_type', \Config::get('const.trader_name'))->first();
        // return $this->facility->where('id', $trader_id)->first();
    }

    /*
     * 採用中止(削除)
     */
    public function stopAdoption(Request $request)
    {
        \DB::beginTransaction();
        try {
            $fm = $this->facilityMedicine->find($request->id);
            $fm->adoption_stop_date = date('Y-m-d');
            $fm->save();
            \DB::commit();
            $request->session()->flash('message', '削除しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }

    }

    /*
     * 採用状況取得
     */
    public function getAdoptionList2(Request $request, $user, $medicine_id, $fp_id)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list = $this->medicine->listWithApply2($medicine_id, $user, $count, $fp_id);

        return $list;
    }

}
