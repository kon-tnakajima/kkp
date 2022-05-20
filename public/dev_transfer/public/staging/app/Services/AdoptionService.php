<?php

namespace App\Services;

use App\Model\Medicine;
use App\Model\PriceAdoption;
use App\Model\Facility;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Actor;
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
        $this->facility = new Facility();
    }
    /*
     * 採用申請一覧取得
     */
    public function getAdoptionList(Request $request, $user)
    {

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->facilityMedicine->getList($request, $user->facility, $count);

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
            }
            $detail->medicine_price = $medicine->medicinePrice->price;
            $detail->pack_unit = $medicine->packUnit->pack_unit;

            $detail->start_date = $facility_price->start_date;
            $detail->end_date = $facility_price->end_date;

            if(!empty($medicine->maker)){
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
        }

        $trader = $this->getTrader($facility_price->trader_id);

        if(!empty($trader)){
            $detail->trader_name = $trader->name;
        }else{
            $detail->trader_name = "";
        }

        $detail->facility_name = $detail->facility->name;
        $detail->user_name = (is_null($detail->user)) ? "" : $detail->user->name;
        $detail->sales_price = $facility_price->sales_price;
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
    public function getFacilities($user)
    {
        return $this->facility->getFacility($user->facility, 'id', true)->get();
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
        $list =  $this->facilityMedicine->getList($request, $user->facility, -1);
        $data = $list->toArray();

        $csv_data = array();
        foreach ($data as $row) {
            if(!empty($row['dosage_type_division'])){
                $row['dosage_type_division'] = \App\Helpers\Apply\getDosageTypeDivisionText($row['dosage_type_division']);
            }
            if(!empty($row['owner_classification'])){
                $row['owner_classification'] = \App\Helpers\Apply\getOwnerClassification( $row['owner_classification'] );
            }
            if(!empty($row['discount'])){
                if($row['discount'] < 0){
                $row['discount'] = $row['discount'] * -1;
                $row['discount'] = "▲".$row['discount'];
                }
                $row['discount'] = $row['discount']."%";
            }
            $csv_data[] = $row;
        }

        $csvHeader = [  '販売メーカー名', '製造メーカー名', '商品名', '一般名', '規格容量', 'JANコード'
                        , '医薬品CD', '包装薬価係数', '単位薬価', '単位納入単価', '包装薬価', '納入単価', '値引率','薬価有効開始日','納入価有効開始日', '業者コード', '業者', '剤型', '申請日'
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
        return $this->facility->where('id', $trader_id)->first();
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
    public function getAdoptionList2(Request $request, $user,$medicine_id)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list = $this->medicine->listWithApply2($medicine_id, $user, $count);

        return $list;
    }

}
