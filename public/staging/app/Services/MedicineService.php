<?php
declare(strict_types=1);
namespace App\Services;

use App\Model\Medicine;
use App\Model\MedicineEffect;
use App\Model\MedicinePrice;
use App\Model\PackUnit;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Model\Maker;
use App\Model\Valiation;
use App\Model\Facility;
use App\Model\FacilityMedicine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\BaseService;

class MedicineService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->medicine = new Medicine();
        $this->medicineEffect = new MedicineEffect();
        $this->medicinePrice = new MedicinePrice();
        $this->valiation = new Valiation();
        $this->facility = new Facility();
        $this->tasks = new Task();
        $this->maker = new Maker();
        $this->packUnit = new PackUnit();
    }
    /*
     * 標準薬品一覧
     */
    public function getMedicineList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->medicine->listWithMedicines($request, $count);

        return $list;
    }

    /*
     * 標準薬品詳細
     */
    public function getMedicineDetail(Request $request)
    {
        $detail = $this->medicine->find($request->id);

        return $detail;
    }

    /*
     * 新規登録
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $data = array();

            // medicine作成
            echo "create";
            $data['bunkaren_code'] = $request->bunkaren_code;
            $data['kon_medicine_id'] = $request->kon_medicine_id;
            $data['code'] = $request->code;
            $data['medicine_code'] = $request->medicine_code;
            $data['receipt_computerized_processing1'] = $request->receipt_computerized_processing1;
            $data['receipt_computerized_processing2'] = $request->receipt_computerized_processing2;
            $data['popular_name'] = $request->popular_name;
            $data['name'] = $request->name;
            $data['phonetic'] = $request->phonetic;

            $data['standard_unit'] = $request->standard_unit;
            $data['package_presentation'] = $request->package_presentation;
            $data['unit'] = $request->unit;
            $data['drug_price_equivalent'] = $request->drug_price_equivalent;
            $data['maker_id'] = $request->maker_id;
            $data['selling_agency_code'] = $request->selling_agency_code;

            $data['medicine_effet_id'] = $request->medicine_effet_id;
            $data['dosage_type_division'] = $request->dosage_type_division;
            $data['transitional_deadline'] = $request->transitional_deadline;
            $data['danger_poison_category1'] = $request->danger_poison_category1;
            $data['danger_poison_category2'] = $request->danger_poison_category2;
            $data['danger_poison_category3'] = $request->danger_poison_category3;
            $data['danger_poison_category4'] = $request->danger_poison_category4;
            $data['danger_poison_category5'] = $request->danger_poison_category5;

            $data['prescription_drug_category'] = $request->prescription_drug_category;
            $data['biological_product_classification'] = $request->biological_product_classification;
            $data['generic_product_flag'] = $request->generic_product_flag;
            $data['production_stop_date'] = $request->production_stop_date;
            $data['discontinuation_date'] = $request->discontinuation_date;
            $data['hospital_code'] = $request->hospital_code;
            $data['owner_classification'] = $request->owner_classification;

            $data['creater'] = $user->id;
            $data['updater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $data['search'] = $request->code
                                .$request->bunkaren_code
                                .$request->medicine_code;

            $medicine = $this->medicine->create($data);

            // 包装単位
            $data = array();

            $data['medicine_id'] = $medicine->id;
            $data['jan_code'] = $request->jan_code;
            $data['hot_code'] = $request->hot_code;
            $data['display_pack_unit'] = $request->display_pack_unit;
            $data['pack_count'] = $request->pack_count;
            $data['pack_unit'] = $request->pack_unit;
            $data['total_pack_count'] = $request->total_pack_count;
            $data['total_pack_unit'] = $request->total_pack_unit;
            $data['creater'] = $user->id;
            $data['updater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $this->packUnit->create($data);

            // 薬価
            $data = array();

            $data['medicine_id'] = $medicine->id;
            $data['start_date'] = $request->start_date;
            $data['end_date'] = $request->end_date;
            $data['price'] = $request->price;
            $data['creater'] = $user->id;
            $data['updater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $this->medicinePrice->create($data);

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            exit;
            return false;
        }
    }

    /*
     * 更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $data = array();

            // medicine作成
            echo "create";
            $medicine = $this->medicine->find($request->id);
            if (is_null($medicine)) {
                return false;
            }
            $medicine->bunkaren_code = $request->bunkaren_code;
            //$medicine->kon_medicine_id = $request->kon_medicine_id;
            $medicine->code = $request->code;
            $medicine->medicine_code = $request->medicine_code;
            $medicine->receipt_computerized_processing1 = $request->receipt_computerized_processing1;
            $medicine->receipt_computerized_processing2 = $request->receipt_computerized_processing2;
            $medicine->popular_name = $request->popular_name;
            $medicine->name = $request->name;
            $medicine->phonetic = $request->phonetic;
            $medicine->standard_unit = $request->standard_unit;
            $medicine->package_presentation = $request->package_presentation;
            $medicine->unit = $request->unit;

            $medicine->drug_price_equivalent = $request->drug_price_equivalent;
            $medicine->maker_id = $request->maker_id;
            $medicine->selling_agency_code = $request->selling_agency_code;
            $medicine->medicine_effet_id = $request->medicine_effet_id;
            $medicine->dosage_type_division = $request->dosage_type_division;
            $medicine->transitional_deadline = $request->transitional_deadline;

            $medicine->danger_poison_category1 = $request->danger_poison_category1;
            $medicine->danger_poison_category2 = $request->danger_poison_category2;
            $medicine->danger_poison_category3 = $request->danger_poison_category3;
            $medicine->danger_poison_category4 = $request->danger_poison_category4;
            $medicine->danger_poison_category5 = $request->danger_poison_category5;
            $medicine->prescription_drug_category = $request->prescription_drug_category;
            $medicine->biological_product_classification = $request->biological_product_classification;

            $medicine->generic_product_flag = $request->generic_product_flag;
            $medicine->production_stop_date = $request->production_stop_date;
            $medicine->discontinuation_date = $request->discontinuation_date;
            $medicine->hospital_code = $request->hospital_code;
            $medicine->owner_classification = $request->owner_classification;
            $medicine->search = $request->code.$request->bunkaren_code.$request->medicine_code;
            $medicine->updater = $user->id;
            $medicine->updated_at = date('Y-m-d H:i:s');

            $medicine->save();

            // 包装単位
            $packUnit = $this->packUnit->where('medicine_id', $request->id)->first();
            if (is_null($packUnit)) {
                return false;
            }
            $packUnit->jan_code = $request->jan_code;
            $packUnit->hot_code = $request->hot_code;
            $packUnit->display_pack_unit = $request->display_pack_unit;
            $packUnit->pack_count = $request->pack_count;
            $packUnit->pack_unit = $request->pack_unit;
            $packUnit->total_pack_count = $request->total_pack_count;
            $packUnit->total_pack_unit = $request->total_pack_unit;
            $packUnit->updater = $user->id;
            $packUnit->updated_at = date('Y-m-d H:i:s');
            $packUnit->save();

            // 薬価
            $medicinePrice = $this->medicinePrice->where('medicine_id', $request->id)->first();
            if (is_null($medicinePrice)) {
                return false;
            }
            $medicinePrice->start_date = $request->start_date;
            $medicinePrice->end_date = $request->end_date;
            $medicinePrice->price = $request->price;
            $medicinePrice->updater = $user->id;
            $medicinePrice->updated_at = date('Y-m-d H:i:s');
            $medicinePrice->save();

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
     * 登録・詳細の施設一覧
     */
    public function getConditions(Request $request)
    {
        // 検索条件
        $conditions = array();
        $conditions['jan_code'] = '';
        $conditions['popular_name'] = '';
        $conditions['name'] = '';
        $conditions['dosage_type_division'] = '';
        $conditions['owner_classification'] = '';
        $conditions['search'] = '';

        $is_cond = false;
        $conditions['is_condition'] = $is_cond;

        //検索実行の場合
        if (!empty($request->is_search)) {
            // JANコード
            if (!empty($request->jan_code)) {
                $conditions['jan_code'] = $request->jan_code;
                $is_cond = true;
            }
            // 一般名
            if (!empty($request->popular_name)) {
                $conditions['popular_name'] = $request->popular_name;
                $is_cond = true;
            }
            // 商品名
            if (!empty($request->name)) {
                $conditions['name'] = $request->name;
                $is_cond = true;
            }
            // 剤型区分
            if (!empty($request->dosage_type_division)) {
                $conditions['dosage_type_division'] = $request->dosage_type_division;
                $is_cond = true;
            }
            // オーナー区分
            if (!empty($request->owner_classification)) {
                $conditions['owner_classification'] = $request->owner_classification;
                $is_cond = true;
            }
            // 全文検索
            if (!empty($request->search)) {
                $conditions['search'] = $request->search;
            }
            $conditions['is_condition'] = $is_cond;
            session()->put(['medicine_conditions' => $conditions]);
        }else{
            //リクエストに値を設定する。
            if( !empty(session()->get('medicine_conditions')) ){

                $conditions = session()->get('medicine_conditions');

                // JANコード
                if (!empty( $conditions['jan_code'] )) {
                    $request->jan_code = $conditions['jan_code'];
                }
                // 一般名
                if (!empty( $conditions['popular_name'] )) {
                    $request->popular_name = $conditions['popular_name'];
                }
                // 商品名
                if (!empty( $conditions['name'] )) {
                    $request->name = $conditions['name'];
                }
                // 剤型区分
                if (!empty( $conditions['dosage_type_division'] )) {
                    $request->dosage_type_division = $conditions['dosage_type_division'];
                }
                // オーナー区分
                if (!empty( $conditions['owner_classification'] )) {
                    $request->owner_classification = $conditions['owner_classification'];
                }
                // 全文検索
                if (!empty( $conditions['search'] )) {
                    $request->search = $conditions['search'];
                }
            }
        }

        return $conditions;
    }

    /*
     * メーカー
     */
    public function getMakers()
    {
        return $this->maker->all();
    }

    /*
     * 薬効
     */
    public function getMedicineEffects()
    {
        return $this->medicineEffect->all();
    }
}
