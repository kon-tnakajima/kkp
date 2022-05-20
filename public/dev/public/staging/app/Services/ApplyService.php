<?php

namespace App\Services;

use App\Model\Medicine;
use App\Model\MedicinePrice;
use App\Model\PackUnit;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Model\Valiation;
use App\Model\Facility;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use App\Model\Maker;
use App\Model\Actor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Mail\AdoptionMail;
use Illuminate\Support\Facades\Mail;
use App\Model\MailQue;

class ApplyService 
{
    const DEFAULT_PAGE_COUNT = 20;
    const MAIL_STR = [
        Task::STATUS_APPLYING => '申請',
        Task::STATUS_NEGOTIATING => '交渉許可',
        Task::STATUS_APPROVAL_WAITING => '価格登録',
        Task::STATUS_UNCONFIRMED => '採用承認',
        ];

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->medicine = new Medicine();
        $this->priceAdoption = new PriceAdoption();
        $this->valiation = new Valiation();
        $this->facilityMedicine = new FacilityMedicine();
        $this->facility = new Facility();
        $this->maker = new Maker();
        $this->tasks = new Task();
        $this->facilityPrice = new FacilityPrice();
        $this->medicinePrice = new MedicinePrice();
        $this->packUnit = new PackUnit();
        $this->mailQue = new MailQue();
    }
    /*
     * 採用申請一覧取得
     */
    public function getApplyList(Request $request, $user)
    {

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->medicine->listWithApply($request, $user->facility, $count);

        // リストにボタン情報付加
        if (!is_null($list)) {
            foreach($list as $key => $apply) {
                $list[$key]->button = $this->button($apply);
            }
        }
        return $list;
    }

    /*
     * 採用申請詳細取得
     */
    public function getApplyDetail(Request $request)
    {
        $user = Auth::user();
        if($request->flg === "1"){
            $detail = $this->priceAdoption->find($request->id);
            if (!is_null($detail->medicine_id)) {
                $detail->jan_code = $detail->medicine->packUnit->jan_code;
                $detail->name = $detail->medicine->name;
                $detail->popular_name = $detail->medicine->popular_name;
                $detail->phonetic = $detail->medicine->phonetic;
                $detail->standard_unit = $detail->medicine->standard_unit;
                $detail->unit = $detail->medicine->unit;
                $detail->maker_name = (empty($detail->medicine->maker)) ? $detail->maker_name : $detail->medicine->maker->name;
                $detail->medicine_price = $detail->medicine->medicinePrice->price * $detail->medicine->packUnit->coefficient;
                $detail->pack_unit = $detail->medicine->packUnit->pack_unit;
                $detail->start_date = $detail->medicine->medicinePrice->start_date;
                $detail->end_date = $detail->medicine->medicinePrice->end_date;
                $detail->selling_maker_name = (empty($detail->medicine->sellingMaker)) ? $detail->selling_maker_name : $detail->medicine->sellingMaker->name;
                $detail->coefficient = $detail->medicine->packUnit->coefficient;
                $detail->pack_count = $detail->medicine->packUnit->pack_count;
                $detail->code = $detail->medicine->code;
                $detail->pack_unit_price = $detail->medicine->packUnit->price;
                $detail->danger_poison = $detail->medicine->getDangerPoison();
                $detail->transitional_deadline = $detail->medicine->transitional_deadline;
                $detail->discontinuation_date = $detail->medicine->discontinuation_date;
                $detail->dosage_type_division = $detail->medicine->dosage_type_division;
                $detail->owner_classification = $detail->medicine->owner_classification;
                $detail->production_stop_date = $detail->medicine->production_stop_date;
                //薬価履歴
                $detail->medicine_price_list = $detail->medicine->medicinePrice->getData($detail->medicine_id);

                //採用品のチェック
                $detail->fm_parent = $this->facilityMedicine->where('facility_id', $detail->facility->parent()->id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
                $detail->fm_own = $this->facilityMedicine->where('facility_id', $detail->facility->id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();

            } else {
                $detail->maker_name = $detail->maker_name;
                $detail->selling_maker_name = "";
                $detail->pack_count = "";
                $detail->code = "";
                $detail->danger_poison = "";
                //薬価履歴
                $detail->medicine_price_list = array();
            }
            $detail->create_date = $detail->created_at;
            $detail->update_date = $detail->updated_at;

            $detail->price_adoption_id = $detail->id;
            $detail->facility_name = $detail->facility->name;
            if (!is_null($detail->user)) {
                $detail->user_name = $detail->user->name;
            }
            $trader = $this->getTrader($detail->trader_id);
            if(!empty($trader)){
                $detail->trader_name = $trader->name;
            }else{
                $detail->trader_name = "";
            }

            //更新対象か
            //病院かつ自分の申請
            if( isHospital() && $detail->facility->id == $user->facility->id ){
                $detail->edit = true;
            }else if(isHeadQuqrters()){
                //本部配下の病院の申請か
                foreach($user->facility->children as $child) {
                    if( $child->id == $detail->facility->id ){
                        $detail->edit = true;
                        break;
                    }
                }
            }else if(isBunkaren()){
                $detail->edit = true;
            }else{
                $detail->edit = false;
            }

        } else {
            $detail = $this->medicine->find($request->id);
            $detail->medicine_id = $detail->id;
//            $detail->maker_name = $detail->maker->name;
            $detail->maker_name = (empty($detail->maker->name)) ? $detail->maker_name : $detail->maker->name;
//            $detail->selling_maker_name = $detail->sellingMaker->name;
            $detail->selling_maker_name = (empty($detail->sellingMaker->name)) ? $detail->selling_maker_name : $detail->sellingMaker->name;
            $detail->coefficient = $detail->packUnit->coefficient;
            $detail->pack_count = $detail->packUnit->pack_count;
            $detail->code = $detail->code;
            $detail->pack_unit_price = $detail->packUnit->price;
            $detail->jan_code = $detail->packUnit->jan_code;
            $detail->medicine_price = $detail->medicinePrice->price * $detail->packUnit->coefficient;
            $detail->start_date = $detail->medicinePrice->start_date;
            $detail->danger_poison = $detail->getDangerPoison();
            $detail->create_date = "";
            $detail->update_date = "";
            $detail->status = null;
            //薬価履歴
            $detail->medicine_price_list = $detail->medicinePrice->getData($request->id);
            $detail->trader_name = "";

            //採用品のチェック
            //$detail->fm_parent = $this->facilityMedicine->where('facility_id', $user->facility->parent()->id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
            $detail->fm_own = $this->facilityMedicine->where('facility_id', $user->facility->id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
        }
        // リストにボタン情報付加
        $detail->button = $this->button($detail);
        $detail->buttonRemand = $this->buttonRemand($detail);
        $detail->flg = $request->flg;
        if(!empty($request->page)){
            $detail->page = $request->page;
        }

        return $detail;
    }

    /*
     * 標準薬品情報取得
     */
    public function getMedicine($id)
    {
        return $this->medicine->find($id);
    }

    /*
     * 検索フォームの施設チェックボックスに使用する施設一覧
     */
    public function getFacilities($user)
    {
        return $this->facility->getFacility($user->facility, 'id')->hospital()->orderBy('name', 'asc')->get();
    }

    /*
     * 業者一覧
     */
    public function getTraders()
    {
        return $this->facility->where('actor_id', Actor::ACTOR_TRADER)->get();
    }

    /*
     * 業者を取得
     */
    public function getTrader($trader_id)
    {
        return $this->facility->where('id', $trader_id)->first();
    }

    /*
     * 採用申請実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $medicine = $this->medicine->find($request->id);
            $user = Auth::user();
            $data = array();
            $facility_id = $user->facility->id;

            // price_adoptions取得
            $priceAdoption = $this->priceAdoption->getData($facility_id, $medicine->id);
            $data['status'] = Task::STATUS_APPLYING; // 申請中からスタート
            $data['user_id'] = $user->id;
            $data['application_date'] = date('Y-m-d H:i:s');
            //$data['purchase_price'] = $medicine->medicinePrice->price;
            //$data['sales_price'] = $medicine->medicinePrice->price;

            $data['search'] = mb_convert_kana($medicine->packUnit->jan_code, 'KVAS').mb_convert_kana($medicine->name, 'KVAS').mb_convert_kana($medicine->maker->name, 'KVAS').mb_convert_kana($medicine->popular_name, 'KVAS').mb_convert_kana($medicine->standard_unit, 'KVAS').mb_convert_kana($medicine->medicine_code, 'KVAS');

            if(is_null($priceAdoption))
            {
                $data['facility_id'] = $facility_id;
                $data['medicine_id'] = $request->id;
                $priceAdoption = $this->priceAdoption->create($data);
            } else {
                if( !empty($request->comment) ){
                    $priceAdoption->comment = $request->comment;
                }
                $priceAdoption->update($data);
            }

            $this->makeValiation($priceAdoption);

            // 送信先は本部へ送信
            $mail_users = $user->facility->parent()->users()->isAdoptionMail()->get();
            $mq = new MailQueService($this->mailQue->getMailQueID());
			foreach($mail_users as $mail_user) {
                //$this->sendMail($mail_user->email, $priceAdoption);
                $mq->send_mail_add($mail_user->email, $priceAdoption);
            }

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            return false;
        }
    }

    /*
     * バリエーション作成
     */
    protected function makeValiation($priceAdoption)
    {
        // valiationデータ作成
        $data = array();
        $valiation = $this->valiation->getByPriceAdoptionID($priceAdoption->id);

        $data['price_adoption_id'] = $priceAdoption->id;
        $data['current_task_id'] = Task::getByStatus($priceAdoption->status)->id;
        $data['next_task_id'] = Task::next($data['current_task_id'])->id;

        if (is_null($valiation)) {
            $this->valiation->create($data);
        } else {
            $valiation->update($data);
        }

    }

    /*
     * 採用許可
     */
    public function allow(Request $request)
    {
        try {
            \DB::beginTransaction();
            $this->next($request);
            $pa = $this->priceAdoption->find($request->id);
            $mail_users = $pa->facility->bunkaren()->first()->users()->isAdoptionMail()->get();
            // 送信先は文化連
            $mq = new MailQueService($this->mailQue->getMailQueID());
            foreach($mail_users as $mail_user) {
                //$this->sendMail($mail_user->email, $pa);
                $mq->send_mail_add($mail_user->email, $pa);
            }
            \DB::commit();

        } catch (\PDOException $e){
            \DB::rollBack();
            return false;
        }
        return true;
    }

    /*
     * 価格確定
     */
    public function confirm(Request $request)
    {
        try {
            \DB::beginTransaction();

            $priceAdoption = $this->priceAdoption->find($request->id);
            if (is_null($priceAdoption)) {
                return false;
            }
            // 下記いずれかがnullの場合、詳細にリダイレクト
            if ((is_null($priceAdoption->sales_price) ||
                is_null($priceAdoption->purchase_price) ||
                is_null($priceAdoption->trader_id)) &&
                (empty($request->purchase_price) &&
                empty($request->sales_price) &&
                empty($request->trader_id) )
            ) {
                \Redirect::to(route('apply.detail', ['id' => $priceAdoption->id, 'flg' => 1]))->send();
                exit;
            }

            if(!empty($request->sales_price)){

                $priceAdoption->purchase_price = $request->purchase_price;
                $priceAdoption->sales_price = $request->sales_price;
                $priceAdoption->comment = $request->comment;
                $priceAdoption->trader_id = $request->trader_id;

                if( empty($priceAdoption->medicine_id) ){
                    //標準薬品の作成
                    $this->medicine->search = $priceAdoption->search;
                    $this->medicine->name = $priceAdoption->name;
                    $this->medicine->standard_unit = $priceAdoption->standard_unit;
                    $this->medicine->unit = '';
                    $this->medicine->owner_classification = $priceAdoption->owner_classification;
                    //$this->medicine->maker_id = $this->maker->find(1)->id;
                    //$this->medicine->selling_agency_code = $this->maker->find(1)->id;
                    $this->medicine->discontinuation_date = date('2999-12-31');
                    $this->medicine->save();
    
                    //包装単位
                    $this->packUnit->medicine_id = $this->medicine->id;
                    $this->packUnit->jan_code = $priceAdoption->jan_code;
                    $this->packUnit->price = $priceAdoption->pack_unit_price;
                    $this->packUnit->coefficient = $priceAdoption->coefficient;
                    $this->packUnit->save();

                    //薬価
                    $this->medicinePrice->medicine_id = $this->medicine->id;
                    $this->medicinePrice->price = $priceAdoption->medicine_price;
                    $this->medicinePrice->start_date = date('Y-m-d');
                    $this->medicinePrice->end_date = date('2100-12-31');
                    $this->medicinePrice->save();

                    $priceAdoption->medicine_id = $this->medicine->id;
                }
                $priceAdoption->save();
            }

            $this->next($request);
            // 送信先は本部
            $priceAdoption = $this->priceAdoption->find($request->id);
            $mail_users = $priceAdoption->facility->parent()->users()->isAdoptionMail()->get();
            $mq = new MailQueService($this->mailQue->getMailQueID());
            foreach($mail_users as $mail_user) {
                //$this->sendMail($mail_user->email, $priceAdoption);
                $mq->send_mail_add($mail_user->email, $priceAdoption);
            }

            \DB::commit();
        } catch (\PDOException $e){
            \DB::rollBack();
            echo $e->getMessage();
            return false;
        }
        return true;
    }

    /*
     * 採用承認
     */
    public function approval(Request $request)
    {
        try {
            \DB::beginTransaction();
            $this->next($request);

            $hq_user = Auth::user();

            // メール送信用にpriceAdoptionを保存する
            $mails = [];

            // 申請詳細取得
            $pa = $this->priceAdoption->find($request->id);
            // 本部薬品取得
            $facility_medicine = $this->facilityMedicine->getData($hq_user->facility->id, $pa->medicine_id);

            if(is_null($facility_medicine))
            {
                $this->facilityMedicine->facility_id = $hq_user->facility->id;
                $this->facilityMedicine->medicine_id = $pa->medicine_id;
                $this->facilityMedicine->user_id = $hq_user->id;
                $this->facilityMedicine->adoption_date = date('Y-m-d');
                $this->facilityMedicine->maker_name = $pa->maker_name;
                //$this->facilityMedicine->price_adoption_id = $pa->id;
    
                // 標準薬品情報取得
                if (is_null($pa->medicine_id)) {
                    $this->facilityMedicine->internal_name = $pa->name;
                    $this->facilityMedicine->internal_name_kana = $pa->phonetic;
                    $this->facilityMedicine->unit = $pa->unit;
                } else {
                    $medicine = $this->medicine->find($pa->medicine_id);
                    $this->facilityMedicine->internal_name = $medicine->name;
                    $this->facilityMedicine->internal_name_kana = $medicine->phonetic;
                    $this->facilityMedicine->unit = $medicine->unit;
                }
                $this->facilityMedicine->save();

                //納入価
                $this->facilityPrice->facility_medicine_id = $this->facilityMedicine->id;
                $this->facilityPrice->trader_id = $pa->trader_id;
                $this->facilityPrice->purchase_price = $pa->purchase_price;
                $this->facilityPrice->sales_price = $pa->sales_price;
                $this->facilityPrice->start_date = date('Y-m-d');
                $this->facilityPrice->end_date = date('2100-12-31');

                $this->facilityPrice->save();
        
            } else {
                if( !empty($request->comment) ){
                    $facility_medicine->comment = $request->comment;
                }
                $facility_medicine->save();

                //納入価
                $facility_price = $this->facilityPrice->getData($facility_medicine->id);
                $facility_price->purchase_price = $pa->purchase_price;
                $facility_price->sales_price = $pa->sales_price;

                $facility_price->save();
            }

            // 同じ本部配下で未申請の病院の申請データを採用可で作る
            $pa = $this->priceAdoption->find($request->id);
            $mails[] = $pa;
            $children = app('UserContainer')->getFacility()->children;
            foreach($children as $child) {
                if ($child->id === $pa->facility_id) {
                    // 元データと同じ場合はスルー
                    continue;
                }
                $child_pa = null;
                // 標準薬品の場合
                if ($pa->medicine_id > 0) {
                    $child_pa = $this->priceAdoption->getData($child->id, $pa->medicine_id);
                } else {
                    $child_pa = $this->priceAdoption
                        ->where('facility_id', $child->id)
                        ->where('jan_code', $pa->jan_code)->first();
                }
                if (is_null($child_pa)) {
                    $child_pa = new PriceAdoption();
                    $child_pa->facility_id = $child->id;
                    $child_pa->medicine_id = $pa->medicine_id;
                    $child_pa->jan_code = $pa->jan_code;
                    $child_pa->name = $pa->name;
                    $child_pa->status = Task::STATUS_UNCONFIRMED; // 採用可
                    $child_pa->maker_name = $pa->maker_name;
                    $child_pa->maker_id = $pa->maker_id;
                    $child_pa->standard_unit = $pa->standard_unit;
                    $child_pa->pack_unit_price = $pa->pack_unit_price;
                    $child_pa->owner_classification = $pa->owner_classification;
                    $child_pa->comment = $pa->comment;
                    $child_pa->user_id = 0;
                    $child_pa->application_date = $pa->application_date;
    
                } else {
                    // すでに承認済み、採用済みになっている場合は処理しない
                    if ($child_pa->status === Task::STATUS_UNCONFIRMED ||
                        $child_pa->status === Task::STATUS_DONE
                    ) {
                        continue;
                    }
                    $child_pa->purchase_price = $pa->purchase_price;
                    $child_pa->sales_price = $pa->sales_price;
                    $child_pa->status = Task::STATUS_UNCONFIRMED; // 採用可
                    $child_pa->trader_id = $pa->trader_id;
                    //$child_pa->search = $pa->search;
    
                    $child_pa->save();
                    $this->makeValiation($child_pa);
                }
                $mails[] = $child_pa;
            }
            
            // 送信先は病院
            $mq = new MailQueService($this->mailQue->getMailQueID());
            foreach($mails as $mail_pa) {
                $users = $mail_pa->facility->users()->isAdoptionMail()->get();
                foreach($users as $user) {
                    //担当者以外はCCで送信する
                    if($user->id == $pa->user_id){
                        $is_target = 1;
                    }else{
                        $is_target = 0;
                    }
                    //$this->sendMail($user->email, $pa);
                    $mq->send_mail_add($user->email, $pa, $is_target);
                }
            }

            \DB::commit();

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            return false;
        }
        return true;
    }

    /*
     * 採用
     */
    public function adopt(Request $request)
    {
        try {
            \DB::beginTransaction();
            $this->next($request);

            // 申請詳細取得
            $pa = $this->priceAdoption->find($request->id);
            $pa->status = Task::STATUS_DONE;
            $pa->save();
            $this->facilityMedicine->facility_id = $pa->facility_id;
            $this->facilityMedicine->medicine_id = $pa->medicine_id;
            $this->facilityMedicine->user_id = $pa->user_id;
            $this->facilityMedicine->adoption_date = date('Y-m-d');
            $this->facilityMedicine->price_adoption_id = $pa->id;
            $this->facilityMedicine->maker_name = $pa->maker_name;

            // 標準薬品情報取得
            if (is_null($pa->medicine_id)) {
                $this->facilityMedicine->internal_name = $pa->name;
                $this->facilityMedicine->internal_name_kana = $pa->phonetic;
                $this->facilityMedicine->unit = $pa->unit;
            } else {
                $medicine = $this->medicine->find($pa->medicine_id);
                $this->facilityMedicine->internal_name = $medicine->name;
                $this->facilityMedicine->internal_name_kana = $medicine->phonetic;
                $this->facilityMedicine->unit = $medicine->unit;
            }
            $this->facilityMedicine->save();

            //納入価
            $this->facilityPrice->facility_medicine_id = $this->facilityMedicine->id;
            $this->facilityPrice->trader_id = $pa->trader_id;
            $this->facilityPrice->purchase_price = $pa->purchase_price;
            $this->facilityPrice->sales_price = $pa->sales_price;
            $this->facilityPrice->start_date = date('Y-m-d');
            $this->facilityPrice->end_date = date('2100-12-31');

            $this->facilityPrice->save();

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            return false;
        }

    }

    /*
     * 採用可
     */
    public function adopt2(Request $request)
    {
        try {
            \DB::beginTransaction();

            // 申請詳細取得
            $user = Auth::user();

            //本部薬品
            $facility_medicine = $this->facilityMedicine->getData($user->facility->parent()->id, $request->id);
            $facility_price = $this->facilityPrice->getData($facility_medicine->id);

            $this->facilityMedicine->facility_id = $user->facility->id;
            $this->facilityMedicine->medicine_id = $request->id;
            $this->facilityMedicine->user_id = $user->id;
            $this->facilityMedicine->adoption_date = date('Y-m-d');
            $this->facilityMedicine->unit = $facility_medicine->unit;
            $this->facilityMedicine->save();

            //納入価
            $this->facilityPrice->facility_medicine_id = $this->facilityMedicine->id;
            $this->facilityPrice->trader_id = $facility_price->trader_id;
            $this->facilityPrice->purchase_price = $facility_price->purchase_price;
            $this->facilityPrice->sales_price = $facility_price->sales_price;
            $this->facilityPrice->start_date = date('Y-m-d');
            $this->facilityPrice->end_date = date('2100-12-31');
            $this->facilityPrice->save();

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            return false;
        }

    }

    /*
     * next
     */
    public function next(Request $request)
    {
        try {
            // valation取得
            $valiation = $this->valiation->where('price_adoption_id', $request->id)->first();

            // price_adoptions取得
            $pa = $this->priceAdoption->find($request->id);
            $next = Task::next($valiation->current_task_id);

            $user = Auth::user();
            $pa->approval_user_id = $user->id;
            $pa->status = $next->status; // 現在のステータスをセット
            if( !empty($request->comment) ){
                $pa->comment = $request->comment;
            }
            $pa->save();

            // valiationデータ更新
            $valiation->current_task_id = $valiation->next_task_id;
            $next = Task::next($valiation->current_task_id);
            if (is_null($next)) {
                $valiation->next_task_id = 0;
            } else {
                $valiation->next_task_id = $next->id;
            }
            $valiation->save();

            return true;

        } catch (\PDOException $e){
            throw $e;
        }
    }
    /*
     * prev
     */
    public function prev(Request $request)
    {
        \DB::beginTransaction();
        try {
            // valation取得
            $valiation = $this->valiation->where('price_adoption_id', $request->id)->first();

            // price_adoptions取得
            $pa = $this->priceAdoption->find($request->id);
            $prev = Task::prev($valiation->current_task_id);

            $user = Auth::user();
            $pa->approval_user_id = $user->id;
            $pa->status = $prev->status; // 現在のステータスをセット
            if( !empty($request->comment) ){
                $pa->comment = $request->comment;
            }else if( !empty($request->comment2) ){
                $pa->comment = $request->comment2;
            }
            $pa->save();

            // valiationデータ更新
            $valiation->next_task_id = $valiation->current_task_id;
            $prev = Task::prev($valiation->next_task_id);
            if (is_null($prev)) {
                $valiation->current_task_id = 0;
            } else {
                $valiation->current_task_id = $prev->id;
            }
            $valiation->save();

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 差し戻し処理
     */
    public function remand(Request $request)
    {
        $pa = $this->priceAdoption->find($request->id);

        //メール送信To対象者を取得
        $mail_target_id = $pa->approval_user_id;

        \DB::beginTransaction();
        try {
            if (!$this->prev($request)) {
                return false;
            }
            //承認済み
            if( $pa->status == Task::STATUS_UNCONFIRMED ){
                //本部薬品を取得
                $hq_fm = $this->facilityMedicine->getData($pa->facility->parent()->id, $pa->medicine_id);
                $isDelete = true;
                foreach($pa->facility->parent()->children as $child) {
                    //配下の病院薬品があるか確認
                    $own_fm = $this->facilityMedicine->getData($child->id, $pa->medicine_id);
                    if( !empty($own_fm) ){
                        $isDelete = false;
                        break;
                    }
                }
                //本部薬品あり、病院薬品なし
                if( !empty($hq_fm) && $isDelete ){
                    $hq_fp = $this->facilityPrice->getData($hq_fm->id);
                    $hq_fp->delete();
                    $hq_fm->delete();
                }
            //承認待ちかつ新規登録
            }else if( $pa->status == Task::STATUS_APPROVAL_WAITING && !empty($pa->name) ){
                $pa->medicine_id = null;//標準薬品は空にする
                $pa->save();
            }

            \DB::commit();
            $request->session()->flash('message', '更新しました');

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }

        // 差し戻し先にメール
        switch($pa->status)
        {
        case Task::STATUS_APPLYING:
            // 病院にメール
            $mail_users = $pa->facility->users()->isAdoptionMail()->get();

            //病院への差し戻しは、申請者をToにする
            $mail_target_id = $pa->user_id;

            break;
        case Task::STATUS_NEGOTIATING:
        case Task::STATUS_UNCONFIRMED:
            // 本部にメール
            $mail_users = $pa->facility->parent()->users()->isAdoptionMail()->get();
            break;
        case Task::STATUS_APPROVAL_WAITING:
            // 文化連にメール
            $mail_users = $pa->facility->bunkaren()->first()->users()->isAdoptionMail()->get();
            break;
        }

        $mq = new MailQueService($this->mailQue->getMailQueID());
        foreach($mail_users as $mail_user) {
            //担当者以外はCCで送信する
            // if($mail_user->id == $mail_target_id){
            //     $is_target = 1;
            // }else{
            //     $is_target = 0;
            // }
            $is_target = 1;
            //$this->sendMail($mail_user->email, $pa, true);
            $mq->send_mail_add($mail_user->email, $pa, $is_target, true);
        }
        return true;
    }

    /* 
     * 更新
     */ 
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $priceAdoption = $this->priceAdoption->find($request->id);
            if (is_null($priceAdoption)) {
                return false;
            }
            if (isBunkaren()) {
                $priceAdoption->purchase_price = $request->purchase_price;
                $priceAdoption->sales_price = $request->sales_price;
                $priceAdoption->trader_id = $request->trader_id;
            }
            $priceAdoption->comment = $request->comment;
            $priceAdoption->save();
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
     * 薬品を登録して申請
     */
    public function add(Request $request, $user)
    {
        if (!$this->existsJanCode($request->jan_code, $request)) {
            return false;
        }
        \DB::beginTransaction();
        try {
            $this->priceAdoption->jan_code = mb_convert_kana($request->jan_code, 'a');
            $this->priceAdoption->name = mb_convert_kana($request->name, 'KVAS');
            $this->priceAdoption->maker_name = mb_convert_kana($request->maker_name, 'KVAS');
            //$this->priceAdoption->maker_id = 1;
            $this->priceAdoption->standard_unit = mb_convert_kana($request->standard_unit, 'KVAS');
            $this->priceAdoption->unit = '';
//            $this->priceAdoption->pack_unit_price = $request->pack_unit_price;
            $this->priceAdoption->pack_unit_price = (empty($request->pack_unit_price)) ? 0 : $request->pack_unit_price;
            $this->priceAdoption->owner_classification = $request->owner_classification;
            $this->priceAdoption->comment = $request->comment;
            $this->priceAdoption->purchase_price = 0;
            $this->priceAdoption->sales_price = 0;
            $this->priceAdoption->facility_id = $user->facility->id;
            $this->priceAdoption->coefficient = 1;
            $this->priceAdoption->status = Task::STATUS_APPLYING;
            $this->priceAdoption->user_id = $user->id;
            $this->priceAdoption->application_date = date('Y-m-d H:i:s');

            // 全文検索はあえてjancodeも全角にする
            $this->priceAdoption->search = mb_convert_kana($this->priceAdoption->jan_code, 'KVAS').$this->priceAdoption->name.$this->priceAdoption->maker_name.$this->priceAdoption->standard_unit;

            $this->priceAdoption->save();

            $this->makeValiation($this->priceAdoption);

            // 送信先は本部へ送信
            $mail_users = $user->facility->parent()->users()->isAdoptionMail()->get();
            $mq = new MailQueService($this->mailQue->getMailQueID());
            foreach($mail_users as $mail_user) {
                //$this->sendMail($mail_user->email, $this->priceAdoption);
                $mq->send_mail_add($mail_user->email, $this->priceAdoption);
            }
            \DB::commit();
            $request->session()->flash('message', '登録して申請しました');
            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            exit;
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 薬品登録再申請
     */
    public function reentry(Request $request, $user)
    {
        \DB::beginTransaction();
        try {
            $pa = $this->priceAdoption->find($request->id);
            if (!$this->existsJanCode($pa->jan_code, $request)) {
                return false;
            }
            $pa->status = Task::STATUS_APPLYING;
            $pa->user_id = $user->id;
            $pa->application_date = date('Y-m-d H:i:s');

            $pa->save();

            $this->makeValiation($pa);

            // 送信先は本部へ送信
            $mail_users = $user->facility->parent()->users()->isAdoptionMail()->get();
            $mq = new MailQueService($this->mailQue->getMailQueID());
            foreach($mail_users as $mail_user) {
                //$this->sendMail($mail_user->email, $pa);
                $mq->send_mail_add($mail_user->email, $pa);
            }
            \DB::commit();
            $request->session()->flash('message', '申請しました');
            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            exit;
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 採用一覧のボタンの情報を返す
     */
    public function button($apply) 
    {
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => '',
            'style' => ''
            ];

        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        if (isHospital()) {
            // 未申請
            if ( ( $apply->status === TASK::STATUS_UNAPPLIED ||  is_null($apply->status) ) && empty($apply->fm_parent) && empty($apply->fm_own) ) {
                if (!is_null($apply->facility_id) && $apply->facility_id != app('UserContainer')->getFacility()->id) {
                    return ;
                }
                $url = ($apply->medicine_id) ? 
                    route('apply.regist', ['id' => $apply->medicine_id ]) : route('apply.reentry', ['id' => $apply->price_adoption_id]);
                $property = [
                    'url' => $url,
                    'label' => '採用申請',
                    'style' => 'btn-primary'
                    ];
            }

            // 採用可
            if (  ($apply->status === TASK::STATUS_ADOPTABLE || ( $apply->status === TASK::STATUS_UNAPPLIED ||  is_null($apply->status) ) ) && !empty($apply->fm_parent) && empty($apply->fm_own) ){
                $property = [
                    'url' => route('apply.adopt2', ['id' => $apply->medicine_id ]),
                    'label' => '採用',
                    'style' => 'btn-danger'
                    ];
            }

            // 申請中は「取り下げ」ボタン
            if ($apply->status === TASK::STATUS_APPLYING && $apply->facility_id == $user->facility->id) {
                $property = [
                    'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'

                        ];
            }

            // 採用可は「採用」ボタン
            if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $user->facility->id) {
                $property = [
                    'url' => route('apply.adopt',['id' => $apply->price_adoption_id ]),
                        'label' => '採用',
                        'style' => 'btn-danger'
                        ];
            }
            // 交渉中は催促
            // if ($apply->status === TASK::STATUS_NEGOTIATING) {
            //     $property = [
            //         'url' => route('apply.regist',['id' => $apply->price_adoption_id ]),
            //         'label' => '催促'
            //     ];
            // }
        }
        // 本部の場合
        if (isHeadQuqrters()) {
            // 申請中は「許可」ボタン
            if ($apply->status === TASK::STATUS_APPLYING) {
                $property = [
                    'url' => route('apply.allow',['id' => $apply->price_adoption_id ]),
                        'label' => '交渉許可',
                        'style' => 'btn-primary'
                        ];
            }
            // 承認待は「採用承認」
            if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
                $property = [
                    'url' => route('apply.approval',['id' => $apply->price_adoption_id ]),
                        'label' => '採用承認',
                        'style' => 'btn-danger'
                        ];
            }
            // 交渉中は「取り下げ」ボタン
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $property = [
                    'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'
                        ];
            }
            // 承認済みは「取り下げ」ボタン
            if ($apply->status === TASK::STATUS_UNCONFIRMED) {
                $property = [
                    'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'
                        ];
            }
        }

        // 文化連の場合
        if (isBunkaren()) {
            // 交渉中は「価格登録」
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $property = [
                    'url' => route('apply.confirm',['id' => $apply->price_adoption_id ]),
                        'label' => '価格登録',
                        'style' => 'btn-danger'
                        ];
            }
            // 承認待は「取り下げ」ボタン
            if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
                $property = [
                    'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'
                        ];
            }
        }
        return $property;
    }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     */
    public function buttonRemand($apply) 
    {
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => ''
            ];

        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        if (isHospital()) {
            // 承認済は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $user->facility->id) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }
        // 本部の場合
        if (isHeadQuqrters()) {
            // 申請中は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_APPLYING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
            // 承認待は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }

        // 文化連の場合
        if (isBunkaren()) {
            // 交渉中は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }
        return $property;
    }

    /*
     * ステータス一覧
     */
    public function getTasks()
    {
        return Task::STATUS_STR;
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function getConditions(Request $request)
    {
        // 検索条件
        $conditions = array();
        $conditions['status'] = array();
        $conditions['facility'] = array();
        $conditions['code'] = '';
        $conditions['popular_name'] = '';
        $conditions['coefficient'] = '';
        $conditions['search'] = '';
        $conditions['discontinuation_date'] = 0;
        $is_cond = false;

        //検索実行の場合
        if (!empty($request->is_search)) {
            // 状態
            if (!empty($request->status)) {
                $conditions['status'] = $request->status;
                $is_cond = true;
            }else{
                $conditions['status'] = array();
            }
            // 施設
            if (!empty($request->facility)) {
                $conditions['facility'] = $request->facility;
                $is_cond = true;
            }else{
                $conditions['facility'] = array();
            }
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

            // 販売中止日
            if (!empty($request->discontinuation_date)) {
                $conditions['discontinuation_date'] = 1;
                $is_cond = true;
            }else{
                $conditions['discontinuation_date'] = 0;
                $is_cond = true;
            }
            // 全文検索
            if (!empty($request->search)) {
                $conditions['search'] = $request->search;
            }
            $conditions['is_condition'] = $is_cond;
            session()->put(['apply_conditions' => $conditions]);
        }else if(!empty($request->initial)){
            $request->status = $this->isInitial($request);
            // 状態
            if (!empty($request->status)) {
                $conditions['status'] = $request->status;
                $is_cond = true;
            }

            // 自施設
            $user = Auth::user();
            if ($user->facility->isHospital()) {
                $request->facility = array( $user->facility->id );
                if(!empty($request->facility)){
                    $conditions['facility'] = $request->facility;
                    $is_cond = true;
                }
            }
                        
            $request->discontinuation_date = $conditions['discontinuation_date'];

            $conditions['is_condition'] = $is_cond;
            session()->put(['apply_conditions' => $conditions]);
        }else{
            if( !empty(session()->get('apply_conditions')) ){
                $conditions = session()->get('apply_conditions');

                //リクエストに値を設定する。
                // 状態
                if (!empty( $conditions['status'] )) {
                    $request->status = $conditions['status'];
                }else{
                    $request->status = array();
                }
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
                // 販売中止日
                if (!empty($conditions['discontinuation_date'])) {
                    $request->discontinuation_date = $conditions['discontinuation_date'];
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
     * 登録・詳細の施設一覧
     */
    public function isInitial(Request $request)
    {
        if(isBunkaren()){
            $status = array(
                Task::STATUS_NEGOTIATING,
                Task::STATUS_APPROVAL_WAITING
            );
        }elseif(isHeadQuqrters()){
            $status = array(
                Task::STATUS_APPLYING,
                Task::STATUS_NEGOTIATING,
                Task::STATUS_APPROVAL_WAITING
            );
        }elseif(isHospital()){
            $status = array(
                Task::STATUS_APPLYING,
                Task::STATUS_UNCONFIRMED
            );
        }
        return $status;
    }

    /*
     * メール送信
     */
    public function sendMail($email, PriceAdoption $pa, $remand = false)
    {
        $data['facility'] = $pa->facility;
        $data['jan_code'] = ($pa->isRegisted()) ? $pa->jan_code : $pa->medicine->packUnit->jan_code;
        $data['name'] = ($pa->isRegisted()) ? $pa->name : $pa->medicine->name;
//        $data['maker_name'] = ($pa->isRegisted()) ? $pa->maker_name : $pa->medicine->maker->name;
        if(empty($pa->maker_name)){
            $data['maker_name'] = $pa->medicine->maker->name;
        }else{
            $data['maker_name'] = $pa->maker_name;
        }

        $data['mail_str'] = self::MAIL_STR[$pa->status];
        $data['subject'] = sprintf("%s%s%sされました(%s)"
            , \Config::get('mail.subject.prefix')
            , self::MAIL_STR[$pa->status] 
            , ($remand) ? "が差し戻し" : ""
            , $pa->facility->name);
        $data['url'] = asset(route('apply.detail', ['id' => $pa->id, 'flg' => 1]));

        try {
            Mail::to($email)->send(new AdoptionMail($data));
        } catch (\Exception $e){
            return false;
        }
    }


    /*
     * 採用申請一覧取得
     */
    public function getApplyList2(Request $request, $user)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;

        $medicine_id = "";
        if($request->flg === "1"){
            $detail = $this->priceAdoption->find($request->id);
            $medicine_id = $detail->medicine_id;
        } else {
            $detail = $this->medicine->find($request->id);
            $medicine_id = $detail->id;
        }
        if (is_null($detail)) {
            return null;
        }

        if (!empty($medicine_id)) {
            $list = $this->medicine->listWithApply2($medicine_id, $user, $count);
        }else{
            return null;
        }

        return $list;
    }

    /* 
     * JANコードチェック
     */
    public function existsJanCode($jan_code, Request $request) {
        $user = app('UserContainer');
        if ($this->packUnit->where('jan_code', $jan_code)->exists()) {
            $request->session()->flash('errorMessage', '既存マスタに存在するのでそちらから採用申請を挙げてください。');
            return false;
        }
        if ($this->priceAdoption->where('jan_code', $jan_code)
                ->where('status', '!=', Task::STATUS_UNAPPLIED) 
                ->getFacility($user->getFacility(), 'facility_id')
                ->exists()) {
                    $request->session()->flash('errorMessage', '入力されたJANコードは既に申請されています。');
                    return false;
        }
        return true;
    }

}
