<?php

// namespace App\Services;

// use App\User;
// use App\Model\Medicine;
// use App\Model\MedicinePrice;
// use App\Model\PackUnit;
// use App\Model\PriceAdoption;
// use App\Model\PriceAdoptionLog;
// use App\Model\UserGroup;
// use App\Model\Task;
// use App\Model\Valiation;
// use App\Model\Facility;
// use App\Model\FacilityMedicine;
// use App\Model\FacilityPrice;
// use App\Model\Maker;
// use App\Model\Actor;
// use App\Model\GroupTraderRelation;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Request;
// use App\Mail\AdoptionMail;
// use Illuminate\Support\Facades\Mail;
// use App\Model\MailQue;
// use App\Model\Concerns\UserGroup as UserGroupTrait;
// use App\Model\PriceAdoptionActionNameFoward;

// class ApplyService
// {
//     use UserGroupTrait;

//     const DEFAULT_PAGE_COUNT = 20;
//     const MAIL_STR = [
//         Task::STATUS_APPLYING => '申請',
//         Task::STATUS_NEGOTIATING => '交渉許可',
//         Task::STATUS_APPROVAL_WAITING => '価格登録',
//         Task::STATUS_UNCONFIRMED => '採用承認',
//     ];
//     const STATUS_UNAPPLIED_LABEL = "採用申請";
//     const STATUS_APPLYING_LABEL = "交渉許可";
//     const STATUS_NEGOTIATING_LABEL = "価格登録";
//     const STATUS_APPROVAL_WAITING_LABEL = "採用承認";
//     const STATUS_UNCONFIRMED_LABEL = "採用";
//     const STATUS_ADOPTABLE_LABEL = "採用";
//     const REVERT_TYPE_INITIAL = 0; // 取り下げでも差し戻しでもない
//     const REVERT_TYPE_WITHDRAW = 1; // 取り下げ
//     const REVERT_TYPE_REMAND = 2; // 差し戻し
//     const REVERT_TYPE_REJECT = 3;   // 見送り

    /*
     * 採用一覧のボタンの情報を返す(削除予定)
     */
//     public function button($apply)
//     {
//         $user = Auth::user();
//         $property = [
//             'url' => null,
//             'label' => '',
//             'style' => ''
//             ];

//         // ステータスとログインしているユーザの施設により処理が変わる
//         // 病院の場合
//         if (isHospital()) {
//             // 未申請
//             if ( ( $apply->status === TASK::STATUS_UNAPPLIED ||  is_null($apply->status) ) && empty($apply->fm_parent) && empty($apply->fm_own) ) {
//                 if (!is_null($apply->facility_id) && $apply->facility_id != app('UserContainer')->getFacility()->id) {
//                     return ;
//                 }
//                 $url = ($apply->medicine_id) ?
//                     route('apply.regist', ['id' => $apply->medicine_id ]) : route('apply.reentry', ['id' => $apply->price_adoption_id]);
//                 $property = [
//                     'url' => $url,
//                     'label' => '採用申請',
//                     'style' => 'btn-primary'
//                     ];
//             }

//             // 採用可
//             if (  ($apply->status === TASK::STATUS_ADOPTABLE || ( $apply->status === TASK::STATUS_UNAPPLIED ||  is_null($apply->status) ) ) && !empty($apply->fm_parent) && empty($apply->fm_own) ){
//                 $property = [
//                     'url' => route('apply.adopt2', ['id' => $apply->medicine_id ]),
//                     'label' => '採用',
//                     'style' => 'btn-danger'
//                     ];
//             }

//             if ($apply->status === TASK::STATUS_ADOPTABLE){
//                 $property = [
//                         'url' => route('apply.adopt2', ['id' => $apply->medicine_id ]),
//                         'label' => '採用',
//                         'style' => 'btn-danger'
//                 ];
//             }


//             // 申請中は「取り下げ」ボタン
//             if ($apply->status === TASK::STATUS_APPLYING && $apply->facility_id == $user->facility->id) {
//                 $property = [
//                     'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
//                         'label' => '取り下げ',
//                         'style' => 'btn-secondary'

//                         ];
//             }

//             // 採用可は「採用」ボタン
//             if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $user->facility->id) {
//                 $property = [
//                     'url' => route('apply.adopt',['id' => $apply->price_adoption_id ]),
//                         'label' => '採用',
//                         'style' => 'btn-danger'
//                         ];
//             }
//             // 交渉中は催促
//             // if ($apply->status === TASK::STATUS_NEGOTIATING) {
//             //     $property = [
//             //         'url' => route('apply.regist',['id' => $apply->price_adoption_id ]),
//             //         'label' => '催促'
//             //     ];
//             // }
//         }
//         // 本部の場合
//         if (isHeadQuqrters()) {
//             // 未申請
//             if ( ( $apply->status === TASK::STATUS_UNAPPLIED ||  is_null($apply->status) ) && empty($apply->fm_parent) && empty($apply->fm_own) ) {
//                 if (!is_null($apply->facility_id) && $apply->facility_id != app('UserContainer')->getFacility()->id) {
//                     return ;
//                 }
//                 $url = ($apply->medicine_id) ?
//                 route('apply.regist', ['id' => $apply->medicine_id ]) : route('apply.reentry', ['id' => $apply->price_adoption_id]);
//                 $property = [
//                         'url' => $url,
//                         'label' => '採用申請',
//                         'style' => 'btn-primary'
//                 ];
//             }

//             // 申請中は「許可」ボタン
//             if ($apply->status === TASK::STATUS_APPLYING) {
//                 $property = [
//                     'url' => route('apply.allow',['id' => $apply->price_adoption_id ]),
//                         'label' => '交渉許可',
//                         'style' => 'btn-primary'
//                         ];
//             }
//             // 承認待は「採用承認」
//             if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
//                 $property = [
//                     'url' => route('apply.approval',['id' => $apply->price_adoption_id ]),
//                         'label' => '採用承認',
//                         'style' => 'btn-danger'
//                         ];
//             }
//             // 交渉中は「取り下げ」ボタン
//             if ($apply->status === TASK::STATUS_NEGOTIATING) {
//                 $property = [
//                     'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
//                         'label' => '取り下げ',
//                         'style' => 'btn-secondary'
//                         ];
//             }
//             // 承認済みは「取り下げ」ボタン
//             if ($apply->status === TASK::STATUS_UNCONFIRMED) {
//                 $property = [
//                     'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
//                         'label' => '取り下げ',
//                         'style' => 'btn-secondary'
//                         ];
//             }
//         }

//         // 文化連の場合
//         if (isBunkaren()) {
//             // 交渉中は「価格登録」
//             if ($apply->status === TASK::STATUS_NEGOTIATING) {
//                 $property = [
//                     'url' => route('apply.confirm',['id' => $apply->price_adoption_id ]),
//                         'label' => '価格登録',
//                         'style' => 'btn-danger'
//                         ];
//             }
//             // 承認待は「取り下げ」ボタン
//             if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
//                 $property = [
//                     'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
//                         'label' => '取り下げ',
//                         'style' => 'btn-secondary'
//                         ];
//             }
//         }
//         return $property;
//     }


    /*
     * 採用申請詳細の差し戻しボタンの情報を返す（削除予定）
     */
//     public function buttonRemand($apply)
//     {
//         $user = Auth::user();
//         $property = [
//             'url' => null,
//             'label' => ''
//             ];

//         // ステータスとログインしているユーザの施設により処理が変わる
//         // 病院の場合
//         if (isHospital()) {
//             // 承認済は「差し戻し」ボタン
//             if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $user->facility->id) {
//                 $property = [
//                     'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
//                         'label' => '差し戻し'
//                         ];
//             }
//         }
//         // 本部の場合
//         if (isHeadQuqrters()) {
//             // 申請中は「差し戻し」ボタン
//             if ($apply->status === TASK::STATUS_APPLYING) {
//                 $property = [
//                     'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
//                         'label' => '差し戻し'
//                         ];
//             }
//             // 承認待は「差し戻し」ボタン
//             if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
//                 $property = [
//                     'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
//                         'label' => '差し戻し'
//                         ];
//             }
//         }

//         // 文化連の場合
//         if (isBunkaren()) {
//             // 交渉中は「差し戻し」ボタン
//             if ($apply->status === TASK::STATUS_NEGOTIATING) {
//                 $property = [
//                     'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
//                         'label' => '差し戻し'
//                         ];
//             }
//         }
//         return $property;
//     }


    /*
     * 採用申請詳細取得
     */
//     public function getApplyDetail_old(Request $request)
//     {
//         $user = Auth::user();

//         if($request->flg === "1"){
//             $detail = $this->priceAdoption->find($request->id);

//             $detail->price_adoption_id="";
//             $detail->key_facility_medicine_id="";
//             $detail->key_facility_price_id="";



//             $detail->edit_sales_price_kengen=-1;
//             $detail->edit_purchase_price_kengen=-1;
//             if (!is_null($detail->medicine_id)) {
//                 $detail->jan_code = $detail->medicine->packUnit->jan_code;
//                 $detail->name = $detail->medicine->name;
//                 $detail->popular_name = $detail->medicine->popular_name;
//                 $detail->phonetic = $detail->medicine->phonetic;
//                 $detail->standard_unit = $detail->medicine->standard_unit;
//                 $detail->unit = $detail->medicine->unit;
//                 $detail->maker_name = (empty($detail->medicine->maker)) ? $detail->maker_name : $detail->medicine->maker->name;
//                 $detail->medicine_price = $detail->medicine->medicinePrice->price * $detail->medicine->packUnit->coefficient;
//                 $detail->medicine_price2 = $detail->medicine->medicinePrice->price;
//                 $detail->pack_unit = $detail->medicine->packUnit->pack_unit;

//                 if (!empty($detail->start_date)) {
//                     $detail->start_date = date('Y-m-d',  strtotime($detail->start_date));
//                 } else {
//                     $detail->start_date = date('Y-m-d');
//                 }

//                 $detail->end_date = $detail->medicine->medicinePrice->end_date;
//                 $detail->selling_maker_name = (empty($detail->medicine->sellingMaker)) ? $detail->selling_maker_name : $detail->medicine->sellingMaker->name;
//                 $detail->coefficient = $detail->medicine->packUnit->coefficient;
//                 $detail->pack_count = $detail->medicine->packUnit->pack_count;
//                 $detail->code = $detail->medicine->code;
//                 $detail->pack_unit_price = $detail->medicine->packUnit->price;
//                 $detail->danger_poison = $detail->medicine->getDangerPoison();
//                 $detail->transitional_deadline = $detail->medicine->transitional_deadline;
//                 $detail->discontinuation_date = $detail->medicine->discontinuation_date;
//                 $detail->dosage_type_division = $detail->medicine->dosage_type_division;
//                 $detail->owner_classification = $detail->medicine->owner_classification;
//                 $detail->production_stop_date = $detail->medicine->production_stop_date;
//                 $detail->standard_unit_name = $detail->medicine->standard_unit_name;
//                 //薬価履歴
//                 $detail->medicine_price_list = $detail->medicine->medicinePrice->getData($detail->medicine_id);
//                 $detail->facility_price_list = array();
//                 //権限取得
//                 $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$detail->sales_user_group_id,$detail->status);
//                 $detail->status_forward = $kengen_rec[0]->status_forward;
//                 $detail->status_back = $kengen_rec[0]->status_back;
//                 $detail->status_back_text = $kengen_rec[0]->status_back_text;

//                 //仕入価格・売上価格
//                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$detail->sales_user_group_id,$detail->status);

//                 $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                 $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                 $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                 $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                 if ($claim_rec[0]->sales_price_kengen == 0) {
//                     $detail->sales_price=0;
//                 }

//                 if ($claim_rec[0]->purchase_price_kengen == 0) {
//                     $detail->purchase_price=0;
//                 }

//                 if ($detail->status_forward > 0) {
//                     $detail->optional_key_own_edit=1;
//                 } else {
//                     $detail->optional_key_own_edit=0;

//                 }

//                 $detail->optional_user_group_id=$detail->sales_user_group_id;
//                 if ($detail->status_forward > 0) {
//                     $ug=$this->userGroup->where('id', $detail->sales_user_group_id)->first();

//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                 }

//                 $detail->create_date = $detail->created_at;
//                 $detail->update_date = $detail->updated_at;
//                 if (!empty($detail->updater)) {
//                     $updater = $this->user->find($detail->updater);

//                     if (!empty($updater)) {
//                         $detail->updater_name = $updater->name;
//                     }
//                 }

//                 //採用品のチェック
//                 $parent_id = $this->userGroup->find($detail->sales_user_group_id)->parent()[0]->id;
//                 $detail->fm_parent = $this->facilityMedicine->where('sales_user_group_id', $parent_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
//                 $detail->fm_own = $this->facilityMedicine->where('sales_user_group_id', $detail->sales_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
//                 if(!empty($detail->fm_parent)){
//                     $detail->key_facility_medicine_id=$detail->fm_parent->id;

//                     if (!empty($detail->fm_parent->facilityPrice->start_date)){
//                         $detail->start_date = date('Y-m-d',  strtotime($detail->fm_parent->facilityPrice->start_date) );
//                         $detail->key_facility_price_id=$detail->fm_parent->facilityPrice->id;
//                     }

//                     $ug=$this->userGroup->where('id', $detail->fm_parent->sales_user_group_id)->first();
//                     if ($detail->fm_parent->sales_user_group_id == $user->primary_user_group_id) {
//                         $detail->optional_key_own_edit=1;
//                     }

//                     $detail->optional_key1_prt_label = $ug->optional_key1_label;
//                     $detail->optional_key2_prt_label = $ug->optional_key2_label;
//                     $detail->optional_key3_prt_label = $ug->optional_key3_label;
//                     $detail->optional_key4_prt_label = $ug->optional_key4_label;
//                     $detail->optional_key5_prt_label = $ug->optional_key5_label;
//                     $detail->optional_key6_prt_label = $ug->optional_key6_label;
//                     $detail->optional_key7_prt_label = $ug->optional_key7_label;
//                     $detail->optional_key8_prt_label = $ug->optional_key8_label;

//                     $detail->optional_key1_prt_value = $detail->fm_parent->optional_key1;
//                     $detail->optional_key2_prt_value = $detail->fm_parent->optional_key2;
//                     $detail->optional_key3_prt_value = $detail->fm_parent->optional_key3;
//                     $detail->optional_key4_prt_value = $detail->fm_parent->optional_key4;
//                     $detail->optional_key5_prt_value = $detail->fm_parent->optional_key5;
//                     $detail->optional_key6_prt_value = $detail->fm_parent->optional_key6;
//                     $detail->optional_key7_prt_value = $detail->fm_parent->optional_key7;
//                     $detail->optional_key8_prt_value = $detail->fm_parent->optional_key8;

//                 }
//                 if(!empty($detail->fm_own)){
//                     $detail->key_facility_medicine_id=$detail->fm_own->id;

//                     $detail->update_date = $detail->fm_own->updated_at;
//                     if (!empty($detail->fm_own->updater)) {
//                         $updater = $this->user->find($detail->fm_own->updater);

//                         if (!empty($updater)) {
//                             $detail->updater_name = $updater->name;
//                         }
//                     }

//                     if (!empty($detail->fm_own->facilityPrice->start_date)) {
//                         $detail->start_date = date('Y-m-d',  strtotime($detail->fm_own->facilityPrice->start_date) );
//                         $detail->purchase_price =$detail->fm_own->facilityPrice->purchase_price;
//                         $detail->sales_price =$detail->fm_own->facilityPrice->sales_price;
//                         $detail->purchase_user_group_id =$detail->fm_own->facilityPrice->purchase_user_group_id;
//                         $detail->facility_price_end_date = $detail->fm_own->facilityPrice->end_date;
//                         $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$detail->fm_own->id);
//                         $detail->key_facility_price_id=$detail->fm_own->facilityPrice->id;
//                     } else {
//                         $detail->start_date =null;
//                         $detail->purchase_price =null;
//                         $detail->sales_price =null;
//                         $detail->purchase_user_group_id =null;
//                     }

//                     $detail->fm_id=$detail->fm_own->id;
//                     if (!empty($detail->fm_own->adoption_stop_date)) {
//                         $detail->adoption_stop_date = date('Y/m/d',  strtotime($detail->fm_own->adoption_stop_date) );
//                     }

//                     $ug=$this->userGroup->where('id', $detail->sales_user_group_id)->first();
//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                     $detail->optional_key1_own_value = $detail->fm_own->optional_key1;
//                     $detail->optional_key2_own_value = $detail->fm_own->optional_key2;
//                     $detail->optional_key3_own_value = $detail->fm_own->optional_key3;
//                     $detail->optional_key4_own_value = $detail->fm_own->optional_key4;
//                     $detail->optional_key5_own_value = $detail->fm_own->optional_key5;
//                     $detail->optional_key6_own_value = $detail->fm_own->optional_key6;
//                     $detail->optional_key7_own_value = $detail->fm_own->optional_key7;
//                     $detail->optional_key8_own_value = $detail->fm_own->optional_key8;

//                 }
//                 //グランドマスタ追加
//                 $detail->sales_packaging_code = $detail->medicine->sales_packaging_code;
//                 $detail->dispensing_packaging_code = $detail->medicine->dispensing_packaging_code;
//                 $detail->hot_code = $detail->medicine->packUnit->hot_code;
//                 if(!empty($detail->medicine->medicinePrice->start_date)) {
//                     $detail->medicine_price_revision_date = date('Y/m/d',  strtotime($detail->medicine->medicinePrice->start_date) );
//                 } else {
//                     $detail->medicine_price_revision_date = "";
//                 }
//                 $detail->generic_product_detail_devision = $detail->medicine->generic_product_detail_devision;
//                 $detail->medicine_code = $detail->medicine->medicine_code;

//                 $text = [];
//                 if ( !empty($detail->medicine->room_temperature) ) {
//                     $text[] = $detail->medicine->room_temperature;
//                 }
//                 if ( !empty($detail->medicine->cold_place) ) {
//                     $text[] = $detail->medicine->cold_place;
//                 }
//                 if ( !empty($detail->medicine->refrigeration) ) {
//                     $text[] = $detail->medicine->refrigeration;
//                 }
//                 if ( !empty($detail->medicine->forzen) ) {
//                     $text[] = $detail->medicine->forzen;
//                 }
//                 $detail->storage_temperature = implode("・", $text);

//                 $text2 = [];
//                 if ( !empty($detail->medicine->dark_place) ) {
//                     $text2[] = $detail->medicine->dark_place;
//                 }
//                 if ( !empty($detail->medicine->shade) ) {
//                     $text2[] = $detail->medicine->shade;
//                 }
//                 $detail->storage_place = implode("・", $text2);

//             } else {
//                 $detail->maker_name = $detail->maker_name;
//                 $detail->selling_maker_name = "";
//                 $detail->pack_count = "";
//                 $detail->code = "";
//                 $detail->danger_poison = "";

//                 //権限取得
//                 $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$detail->sales_user_group_id,$detail->status);
//                 $detail->status_forward = $kengen_rec[0]->status_forward;
//                 $detail->status_back = $kengen_rec[0]->status_back;
//                 $detail->status_back_text = $kengen_rec[0]->status_back_text;

//                 //仕入価格・売上価格
//                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$detail->sales_user_group_id,$detail->status);
//                 $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                 $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                 $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                 $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                 //薬価履歴
//                 $detail->medicine_price_list = array();

//                 if( !isBunkaren() && !empty($detail->start_date) ){
//                     $detail->start_date = date('Y-m-d',  strtotime($detail->start_date));
//                 }else if(isBunkaren() && empty($detail->start_date)){
//                     $detail->start_date = date('Y-m-d');
//                 }
//             }

//             $detail->price_adoption_id = $detail->id;

//             $rec = $this->userGroup->getUserGroup($detail->sales_user_group_id);
//             $detail->facility_name = $rec[0]->name;
//             if (!is_null($detail->user)) {
//                 $detail->user_name = $detail->user->name;
//             }
//             $trader = $this->getTrader($detail->purchase_user_group_id);
//             if(!empty($trader)){
//                 $detail->trader_name = $trader->name;
//             }else{
//                 $detail->trader_name = "";
//             }
//             if (!empty($detail->updater)) {
//                 $updater = $this->user->find($detail->updater);

//                 if (!empty($updater)) {
//                     $detail->updater_name = $updater->name;
//                 }
//             }

//             $detail->delete=false;
//             //更新対象か
//             //病院かつ自分の申請
//             if( isHospital() && $detail->sales_user_group_id == $user->primary_user_group_id ){
//                 $detail->edit = true;
//                 if ($detail->status >= Task::STATUS_UNCONFIRMED) {
//                     $ug=$this->userGroup->where('id', $detail->sales_user_group_id)->first();
//                     $detail->optional_key_own_edit=1;

//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                 }

//                 //採用済みのみ削除可能
//                 if (Task::STATUS_DONE == $detail->status ) {
//                     $detail->delete=true;
//                 }
//             }else if(isHeadQuqrters()){
//                 if ($detail->sales_user_group_id == $user->primary_user_group_id){
//                     $detail->edit = true;
//                     $detail->optional_key_own_edit=1;

//                 }

//                 //本部配下の病院の申請か
//                 foreach($this->userGroup->find($detail->sales_user_group_id)->children() as $child) {
//                     if ($detail->status == Task::STATUS_APPROVAL_WAITING) {
//                         $ug=$this->userGroup->where('id', $user->primary_user_group_id)->first();
//                         $detail->optional_key_own_edit=1;
//                         $detail->optional_user_group_id = $user->primary_user_group_id;
//                         $detail->optional_key1_own_label = $ug->optional_key1_label;
//                         $detail->optional_key2_own_label = $ug->optional_key2_label;
//                         $detail->optional_key3_own_label = $ug->optional_key3_label;
//                         $detail->optional_key4_own_label = $ug->optional_key4_label;
//                         $detail->optional_key5_own_label = $ug->optional_key5_label;
//                         $detail->optional_key6_own_label = $ug->optional_key6_label;
//                         $detail->optional_key7_own_label = $ug->optional_key7_label;
//                         $detail->optional_key8_own_label = $ug->optional_key8_label;
//                     }

//                     if( $child->id == $detail->sales_user_group_id ){
//                         $detail->edit = true;
//                         break;
//                     }
//                 }
//             }else if(isBunkaren()){
//                 $detail->edit = true;
//             }else{
//                 $detail->edit = false;
//             }

//         } else {

//             $detail = $this->medicine->find($request->id);

//             $detail->price_adoption_id="";
//             $detail->key_facility_medicine_id="";
//             $detail->key_facility_price_id="";
//             $detail->medicine_id = $detail->id;
// //            $detail->maker_name = $detail->maker->name;
//             $detail->maker_name = (empty($detail->maker->name)) ? $detail->maker_name : $detail->maker->name;
// //            $detail->selling_maker_name = $detail->sellingMaker->name;
//             $detail->selling_maker_name = (empty($detail->sellingMaker->name)) ? $detail->selling_maker_name : $detail->sellingMaker->name;
//             $detail->coefficient = $detail->packUnit->coefficient;
//             $detail->pack_count = $detail->packUnit->pack_count;
//             $detail->code = $detail->code;
//             $detail->pack_unit_price = $detail->packUnit->price;
//             $detail->jan_code = $detail->packUnit->jan_code;

//             if (!empty($detail->medicinePrice->price)) {
//                 $detail->medicine_price = $detail->medicinePrice->price * $detail->packUnit->coefficient;
//             } else {
//                 $detail->medicine_price ="0";
//             }

//             if (!empty($detail->medicinePrice->price)) {
//                 $detail->medicine_price2 = $detail->medicinePrice->price;
//             } else {
//                 $detail->medicine_price2 ="0";

//             }

//             //$detail->start_date = $detail->medicinePrice->start_date;
//             /*
//             if( !isBunkaren() && !empty($detail->start_date) ){
//                 $detail->start_date = date('Y-m-d',  strtotime($detail->start_date));
//             }else if(isBunkaren() && empty($detail->start_date)){
//                 $detail->start_date = date('Y-m-d');
//             }
//             */
//             if (!empty($detail->start_date) ){
//                 $detail->start_date = date('Y-m-d',  strtotime($detail->start_date));
//             } else {
//                 $detail->start_date = date('Y-m-d');
//             }

//             $detail->danger_poison = $detail->getDangerPoison();
//             $detail->create_date = "";
//             $detail->update_date = "";
//             $detail->status = null;
//             //薬価履歴
//             $detail->medicine_price_list = $detail->medicinePrice->getData($request->id);
//             $detail->trader_name = "";
//             $detail->facility_price_list=array();

//             //グランドマスタ追加
//             if(!empty($detail->medicinePrice->start_date)) {
//                 $detail->medicine_price_revision_date = date('Y/m/d',  strtotime($detail->medicinePrice->start_date) );
//             } else {
//                 $detail->medicine_price_revision_date = "";
//             }
//             $detail->hot_code = $detail->packUnit->hot_code;

//             $text = [];
//             if ( !empty($detail->room_temperature) ) {
//                 $text[] = $detail->room_temperature;
//             }
//             if ( !empty($detail->cold_place) ) {
//                 $text[] = $detail->cold_place;
//             }
//             if ( !empty($detail->refrigeration) ) {
//                 $text[] = $detail->refrigeration;
//             }
//             if ( !empty($detail->forzen) ) {
//                 $text[] = $detail->forzen;
//             }
//             $detail->storage_temperature = implode("・", $text);

//             //権限取得
//             $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$user->primary_user_group_id,Task::STATUS_UNAPPLIED);
//             $detail->status_forward = $kengen_rec[0]->status_forward;
//             $detail->status_back = $kengen_rec[0]->status_back;
//             $detail->status_back_text = $kengen_rec[0]->status_back_text;

//             $text2 = [];
//             if ( !empty($detail->dark_place) ) {
//                 $text2[] = $detail->dark_place;
//             }
//             if ( !empty($detail->shade) ) {
//                 $text2[] = $detail->shade;
//             }
//             $detail->storage_place = implode("・", $text2);
//             $detail->sales_price_kengen=-1;
//             $detail->purchase_price_kengen=-1;

//             if ($detail->status_forward > 0) {
//                 $detail->optional_key_own_edit=1;
//             } else {
//                 $detail->optional_key_own_edit=0;

//             }

//             //採用品のチェック
//             if (isHospital()) {
//                 if (!empty($request->fa_id)) {
//                     $us_gr = $this->userGroup->getUserGroup($request->fa_id);
//                     $us_gr_type = $us_gr[0]->group_type;
//                     if ($us_gr_type == '本部' && $request->fa_id != $user->primary_honbu_user_group_id) {
//                         $parent_user_group_id = $request->fa_id;
//                         $detail->fm_parent = $this->facilityMedicine->where('sales_user_group_id', $parent_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
//                     } else {
//                         $parent_user_group_id = $this->userGroup->find($user->primary_user_group_id)->parent()[0]->id;
//                         $detail->fm_parent = $this->facilityMedicine->where('sales_user_group_id', $parent_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();
//                     }
//                 } else {
//                     $parent_user_group_id = $this->userGroup->find($user->primary_user_group_id)->parent()[0]->id;
//                     $detail->fm_parent = $this->facilityMedicine->where('sales_user_group_id', $parent_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();

//                 }

//                 if(!empty($detail->fm_parent)){

//                     if (!empty($request->fa_id)) {
//                         if($request->fa_id == $user->primary_honbu_user_group_id) {
//                             $detail->status=TASK::STATUS_DONE;
//                             $rec = $this->userGroup->getUserGroup($detail->fm_parent->sales_user_group_id);
//                             $detail->facility_name = $rec[0]->name;
//                         } else if ($request->fa_id == $user->primary_user_group_id){
//                                 $detail->status=TASK::STATUS_ADOPTABLE;
//                                 $rec = $this->userGroup->getUserGroup($request->fa_id);
//                                 $detail->facility_name = $rec[0]->name;
//                         } else {
//                             $detail->status=TASK::STATUS_DONE;
//                             $rec = $this->userGroup->getUserGroup($request->fa_id);
//                             $detail->facility_name = $rec[0]->name;
//                         }
//                     } else {
//                         $detail->status=TASK::STATUS_ADOPTABLE;
//                     }



//                     /*
//                     $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$user->primary_user_group_id,$detail->status);
//                     $detail->status_forward = $kengen_rec[0]->status_forward;
//                     $detail->status_back = $kengen_rec[0]->status_back;
//                     $detail->status_back_text = $kengen_rec[0]->status_back_text;
//                     if ($detail->status_forward > 0) {
//                         $detail->optional_key_own_edit=1;
//                     } else {
//                         $detail->optional_key_own_edit=0;

//                     }
// */

//                     $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$user->primary_user_group_id,$detail->status);
//                     $detail->status_forward = $kengen_rec[0]->status_forward;
//                     $detail->status_back = $kengen_rec[0]->status_back;
//                     $detail->status_back_text = $kengen_rec[0]->status_back_text;

//                     $detail->update_date = $detail->fm_parent->updated_at;
//                     $updater = $this->user->find($detail->fm_parent->updater);
//                     if (!empty($updater)) {
//                         $detail->updater_name = $updater->name;
//                     }
//                     $detail->comment = $detail->fm_parent->comment;
//                     $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$detail->fm_parent->id);
//                     $detail->optional_user_group_id=$user->primary_user_group_id;

//                     $detail->key_facility_medicine_id=$detail->fm_parent->id;

//                     $ug=$this->userGroup->where('id', $detail->fm_parent->sales_user_group_id)->first();
//                     $detail->optional_key1_prt_label = $ug->optional_key1_label;
//                     $detail->optional_key2_prt_label = $ug->optional_key2_label;
//                     $detail->optional_key3_prt_label = $ug->optional_key3_label;
//                     $detail->optional_key4_prt_label = $ug->optional_key4_label;
//                     $detail->optional_key5_prt_label = $ug->optional_key5_label;
//                     $detail->optional_key6_prt_label = $ug->optional_key6_label;
//                     $detail->optional_key7_prt_label =

//                     $detail->optional_key8_prt_label = $ug->optional_key8_label;

//                     $detail->optional_key1_prt_value = $detail->fm_parent->optional_key1;
//                     $detail->optional_key2_prt_value = $detail->fm_parent->optional_key2;
//                     $detail->optional_key3_prt_value = $detail->fm_parent->optional_key3;
//                     $detail->optional_key4_prt_value = $detail->fm_parent->optional_key4;
//                     $detail->optional_key5_prt_value = $detail->fm_parent->optional_key5;
//                     $detail->optional_key6_prt_value = $detail->fm_parent->optional_key6;
//                     $detail->optional_key7_prt_value = $detail->fm_parent->optional_key7;
//                     $detail->optional_key8_prt_value = $detail->fm_parent->optional_key8;

//                     if(!empty($request->fp_id)){
//                         $fp = $this->facilityPrice->find($request->fp_id);
//                       if ( $fp->start_date <= date('Y-m-d H:i:s') && $fp->end_date >= date('Y-m-d H:i:s')) {
//                         $detail->sales_price = (float)$fp->sales_price;
//                         $detail->start_date = date('Y-m-d',  strtotime($fp->start_date));
//                         $detail->facility_price_end_date = $fp->end_date;

//                         //業者
//                         $trader = $this->getTrader($fp->purchase_user_group_id);
//                         $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$fp->facility_medicine_id);
//                         $detail->key_facility_price_id=$fp->id;

//                         //仕入価格・売上価格
//                         $claim_rec=$this->medicine->getShowPriceApply($user->id,$detail->fm_parent->sales_user_group_id,Task::STATUS_ADOPTABLE);
//                         $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                         $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                         $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                         $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;
//                         //$detail->next_status = -1;
//                         //$detail->reject_status = -1;
//                         //$detail->withdraw_status = -1;

//                         //$detail->status_forward =-1;
//                         //$detail->status_back=-1;
//                         //$detail->optional_key_own_edit=0;

//                       }else{
//                         if (!empty($detail->fm_parent->facilityPrice->sales_price)) {
//                             $detail->sales_price = (float)$detail->fm_parent->facilityPrice->sales_price;
//                             $detail->start_date = date('Y-m-d',  strtotime($detail->fm_parent->facilityPrice->start_date));
//                             //    業者
//                             $trader = $this->getTrader($detail->fm_parent->facilityPrice->purchase_user_group_id);
//                             $detail->key_facility_price_id=$detail->fm_parent->facilityPrice->id;
//                             $detail->facility_price_end_date = $detail->fm_parent->facilityPrice->end_date;

//                         } else {
//                             $trader=null;
//                             $detail->sales_price =null;
//                             $detail->start_date =null;
//                             $detail->facility_price_end_date = null;
//                         }
//                         $detail->facility_price_list = array();

//                         //仕入価格・売上価格
//                         $claim_rec=$this->medicine->getShowPriceApply($user->id,$detail->fm_parent->sales_user_group_id,Task::STATUS_ADOPTABLE);
//                         $detail->optional_user_group_id=$detail->fm_parent->sales_user_group_id;
//                         $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                         $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                         $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                         $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                       }

//                     }

//                     if(!empty($trader)){
//                         $detail->trader_name = $trader->name;
//                     }else{
//                         $detail->trader_name = "";
//                     }
//                 }

//                 if (!empty($request->fa_id) && $request->fa_id != $user->primary_honbu_user_group_id) {
//                 $us_gr = $this->userGroup->getUserGroup($request->fa_id);
//                 $us_gr_type = $us_gr[0]->group_type;
//                 if ($us_gr_type != '本部' ) {

//                 $detail->fm_own = $this->facilityMedicine->where('sales_user_group_id', $user->primary_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();

//                 if(!empty($detail->fm_own)){
//                   $fp_own = $this->facilityPrice->getData($detail->fm_own->id);
//                   if(!empty($fp_own)){
//                     //$detail->status=TASK::STATUS_DONE;
//                     //仕入価格・売上価格
//                     $detail->optional_key_own_edit=1;
//                     $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$detail->fm_own->id);
//                     $detail->key_facility_medicine_id=$detail->fm_own->id;
//                     if (!empty($detail->fm_own->adoption_stop_date)) {
//                         $detail->adoption_stop_date = date('Y/m/d',  strtotime($detail->fm_own->adoption_stop_date) );
//                     }

//                     $t=$detail->sales_user_group_id;
//                     if (!empty($detail->sales_user_group_id)) {
//                         if ($detail->sales_user_group_id == $user->primary_user_group_id) {
//                             $detail->update_date = $detail->fm_own->updated_at;
//                             $updater = $this->user->find($detail->fm_own->updater);
//                             if (!empty($updater)) {
//                                 $detail->updater_name = $updater->name;
//                             }
//                             $detail->comment = $detail->fm_own->comment;
//                         }

//                     } else {
//                         $detail->update_date = $detail->fm_own->updated_at;
//                         $updater = $this->user->find($detail->fm_own->updater);
//                         if (!empty($updater)) {
//                             $detail->updater_name = $updater->name;
//                         }
//                         $detail->comment = $detail->fm_own->comment;

//                     }


//                     if(!empty($request->fp_id)){
//                         $fp = $this->facilityPrice->find($request->fp_id);
//                       if ( $fp->start_date <= date('Y-m-d H:i:s') && $fp->end_date >= date('Y-m-d H:i:s')) {
//                         $detail->sales_price = (float)$fp->sales_price;
//                         $detail->start_date = date('Y-m-d',  strtotime($fp->start_date));
//                         $detail->facility_price_end_date = $fp->end_date;

//                         //業者
//                         $trader = $this->getTrader($fp->purchase_user_group_id);

//                         $fm=$this->facilityMedicine->find($fp->facility_medicine_id);
//                         $rec = $this->userGroup->getUserGroup($fm->sales_user_group_id);
//                         $detail->facility_name = $rec[0]->name;

//                         if ($fm->sales_user_group_id == $user->primary_user_group_id) {
//                             $detail->status=TASK::STATUS_DONE;

//                             $detail->edit = true;
//                             $detail->delete = true;

//                         } else {
//                             $detail->edit = false;
//                             $detail->delete = false;


//                             $detail->update_date = $fm->updated_at;
//                             $updater = $this->user->find($fm->updater);
//                             if (!empty($updater)) {
//                                 $detail->updater_name = $updater->name;
//                             }
//                             $detail->comment = $fm->comment;

//                         }

//                         $detail->key_facility_price_id=$request->fp_id;

//                         $detail->status=TASK::STATUS_DONE;
//                         $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$user->primary_user_group_id,$detail->status);
//                         $detail->status_forward = $kengen_rec[0]->status_forward;
//                         $detail->status_back = $kengen_rec[0]->status_back;
//                         $detail->status_back_text = $kengen_rec[0]->status_back_text;

//                         $claim_rec=$this->medicine->getShowPriceApply($user->id,$fm->sales_user_group_id,$detail->status);
//                         $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                         $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                         $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                         $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                         $ug=$this->userGroup->where('id', $fm->sales_user_group_id)->first();
//                         $detail->optional_key1_own_label = $ug->optional_key1_label;
//                         $detail->optional_key2_own_label = $ug->optional_key2_label;
//                         $detail->optional_key3_own_label = $ug->optional_key3_label;
//                         $detail->optional_key4_own_label = $ug->optional_key4_label;
//                         $detail->optional_key5_own_label = $ug->optional_key5_label;
//                         $detail->optional_key6_own_label = $ug->optional_key6_label;
//                         $detail->optional_key7_own_label = $ug->optional_key7_label;
//                         $detail->optional_key8_own_label = $ug->optional_key8_label;

//                         $detail->optional_key1_own_value = $fm->optional_key1;
//                         $detail->optional_key2_own_value = $fm->optional_key2;
//                         $detail->optional_key3_own_value = $fm->optional_key3;
//                         $detail->optional_key4_own_value = $fm->optional_key4;
//                         $detail->optional_key5_own_value = $fm->optional_key5;
//                         $detail->optional_key6_own_value = $fm->optional_key6;
//                         $detail->optional_key7_own_value = $fm->optional_key7;
//                         $detail->optional_key8_own_value = $fm->optional_key8;

//                         if ($fm->sales_user_group_id <> $user->primary_user_group_id) {
//                             $detail->optional_key_own_edit=0;
//                         }

//                       }else{
//                         if (!empty($detail->fm_own->facilityPrice->sales_price)) {
//                             $detail->sales_price = (float)$detail->fm_own->facilityPrice->sales_price;
//                             $detail->start_date = date('Y-m-d',  strtotime($detail->fm_own->facilityPrice->start_date));
//                             $detail->facility_price_end_date = $detail->fm_own->facilityPrice->end_date;

//                             //業者
//                             $trader = $this->getTrader($detail->fm_own->facilityPrice->purchase_user_group_id);
//                             $detail->key_facility_price_id=$request->fp_id;
//                         } else {
//                             $detail->sales_price =null;
//                             $detail->start_date =null;
//                             $detail->facility_price_end_date = null;
//                             $trader = null;
//                         }

//                         $claim_rec=$this->medicine->getShowPriceApply($user->id,$detail->fm_own->sales_user_group_id,$detail->status);
//                         $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                         $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                         $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                         $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                         $ug=$this->userGroup->where('id', $detail->fm_own->sales_user_group_id)->first();
//                         $detail->optional_key1_own_label = $ug->optional_key1_label;
//                         $detail->optional_key2_own_label = $ug->optional_key2_label;
//                         $detail->optional_key3_own_label = $ug->optional_key3_label;
//                         $detail->optional_key4_own_label = $ug->optional_key4_label;
//                         $detail->optional_key5_own_label = $ug->optional_key5_label;
//                         $detail->optional_key6_own_label = $ug->optional_key6_label;
//                         $detail->optional_key7_own_label = $ug->optional_key7_label;
//                         $detail->optional_key8_own_label = $ug->optional_key8_label;

//                         $detail->optional_key1_own_value = $detail->fm_own->optional_key1;
//                         $detail->optional_key2_own_value = $detail->fm_own->optional_key2;
//                         $detail->optional_key3_own_value = $detail->fm_own->optional_key3;
//                         $detail->optional_key4_own_value = $detail->fm_own->optional_key4;
//                         $detail->optional_key5_own_value = $detail->fm_own->optional_key5;
//                         $detail->optional_key6_own_value = $detail->fm_own->optional_key6;
//                         $detail->optional_key7_own_value = $detail->fm_own->optional_key7;
//                         $detail->optional_key8_own_value = $detail->fm_own->optional_key8;

//                       }

//                     }
//                     if(!empty($trader)){
//                         $detail->trader_name = $trader->name;
//                     }else{
//                         $detail->trader_name = "";
//                     }

//                   } else {
//                     if (empty($detail->status)) {
//                         $st = Task::STATUS_UNAPPLIED;
//                     } else {
//                         $st = $detail->status;
//                     }

//                     $ug=$this->userGroup->where('id', $user->primary_user_group_id)->first();
//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                     if ($detail->status_forward > 0) {
//                         $detail->optional_key_own_edit=1;
//                     } else {
//                         $detail->optional_key_own_edit=0;

//                     }
//                   }
//                 } else {
//                     if (empty($detail->status)) {
//                         $st = Task::STATUS_UNAPPLIED;
//                     } else {
//                         $st = $detail->status;
//                     }
// /*
//                     $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$user->primary_user_group_id,$st);
//                     $detail->status_forward = $kengen_rec[0]->status_forward;
//                     $detail->status_back = $kengen_rec[0]->status_back;
//                     $detail->status_back_text = $kengen_rec[0]->status_back_text;
// */
//                     $ug=$this->userGroup->where('id', $user->primary_user_group_id)->first();
//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                     if ($detail->status_forward > 0) {
//                         $detail->optional_key_own_edit=1;
//                     } else {
//                         $detail->optional_key_own_edit=0;

//                     }

//                   }
//                 }

//                 }
//             }else if (isHeadQuqrters()) {

//                 $detail->fm_own = $this->facilityMedicine->where('sales_user_group_id', $user->primary_user_group_id)->where('medicine_id', $detail->medicine_id)->where('deleted_at', null)->first();

//                 if(!empty($detail->fm_own)){
//                     $detail->fm_id=$detail->fm_own->id;
//                     if (!empty($detail->fm_own->adoption_stop_date)) {
//                         $detail->adoption_stop_date = date('Y/m/d',  strtotime($detail->fm_own->adoption_stop_date) );
//                     }
//                     $detail->optional_user_group_id=$detail->fm_own->sales_user_group_id;

//                     $detail->update_date = $detail->fm_own->updated_at;
//                     $updater = $this->user->find($detail->fm_own->updater);
//                     if (!empty($updater)) {
//                         $detail->updater_name = $updater->name;
//                     }
//                     $detail->comment = $detail->fm_own->comment;

//                     $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$detail->fm_own->id);
//                     $detail->optional_key_own_edit=1;
//                     $ug=$this->userGroup->where('id', $detail->fm_own->sales_user_group_id)->first();
//                     $detail->key_facility_medicine_id= $detail->fm_own->id;

//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                     $detail->optional_key1_own_value = $detail->fm_own->optional_key1;
//                     $detail->optional_key2_own_value = $detail->fm_own->optional_key2;
//                     $detail->optional_key3_own_value = $detail->fm_own->optional_key3;
//                     $detail->optional_key4_own_value = $detail->fm_own->optional_key4;
//                     $detail->optional_key5_own_value = $detail->fm_own->optional_key5;
//                     $detail->optional_key6_own_value = $detail->fm_own->optional_key6;
//                     $detail->optional_key7_own_value = $detail->fm_own->optional_key7;
//                     $detail->optional_key8_own_value = $detail->fm_own->optional_key8;
//                 }

//                 if(!empty($request->fp_id)){
//                     $fp = $this->facilityPrice->find($request->fp_id);
//                    if ( $fp->start_date <= date('Y-m-d H:i:s') && $fp->end_date >= date('Y-m-d H:i:s')) {
//                     $fm=$this->facilityMedicine->find($fp->facility_medicine_id);

//                     $detail->sales_price = (float)$fp->sales_price;
//                     $detail->start_date = date('Y-m-d',  strtotime($fp->start_date));
//                     $detail->facility_price_end_date = $fp->end_date;

//                     $rec = $this->userGroup->getUserGroup($fm->sales_user_group_id);
//                     $detail->facility_name = $rec[0]->name;
//                     $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$fm->id);
//                     $detail->key_facility_price_id= $request->fp_id;

//                     //$fac=$this->facility->find($fm->facility_id);
//                     //$detail->facility_name = $fac->name;

//                     //業者
//                     $trader = $this->getTrader($fp->purchase_user_group_id);
//                     if(!empty($trader)){
//                         $detail->trader_name = $trader->name;
//                     }else{
//                         $detail->trader_name = "";
//                     }

//                     $detail->status=TASK::STATUS_DONE;
//                     if ($fm->sales_user_group_id == $user->primary_user_group_id) {
//                         $detail->edit = true;
//                         $detail->delete = true;
//                     }else {
//                         $detail->update_date = $fm->updated_at;
//                         $updater = $this->user->find($fm->updater);
//                         if (!empty($updater)) {
//                             $detail->updater_name = $updater->name;
//                         }
//                         $detail->comment = $fm->comment;

//                     }

//                     $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$fm->sales_user_group_id,$detail->status);
//                     $detail->status_forward = $kengen_rec[0]->status_forward;
//                     $detail->status_back = $kengen_rec[0]->status_back;
//                     $detail->status_back_text = $kengen_rec[0]->status_back_text;


//                   } else {

//                     if(!empty($detail->fm_own)){
//                         if (!empty($detail->fm_own->facilityPrice->start_date)) {
//                             $detail->sales_price = (float)$detail->fm_own->facilityPrice->sales_price;
//                             $detail->start_date = date('Y-m-d',  strtotime($detail->fm_own->facilityPrice->start_date));
//                             $detail->key_facility_price_id= $detail->fm_own->facilityPrice->id;
//                             $detail->facility_price_end_date = $detail->fm_own->facilityPrice->end_date;

//                             //業者
//                             $trader = $this->getTrader($detail->fm_own->facilityPrice->purchase_user_group_id);
//                             if(!empty($trader)){
//                                 $detail->trader_name = $trader->name;
//                             }else{
//                                 $detail->trader_name = "";
//                             }
//                         } else {
//                             $detail->start_date =null;
//                             $detail->purchase_price =null;
//                             $detail->sales_price =null;
//                             $detail->purchase_user_group_id =null;
//                             $detail->facility_price_end_date = null;
//                         }
//                     }

//                   }
//                 }
//                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$user->primary_user_group_id,$detail->status);
//                 $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                 $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;
//                 $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                 $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;


//             } else {
//                 if(!empty($request->fp_id)){
//                     $fp = $this->facilityPrice->find($request->fp_id);
//                   if ( $fp->start_date <= date('Y-m-d H:i:s') && $fp->end_date >= date('Y-m-d H:i:s')) {
//                       $detail->status=TASK::STATUS_DONE;
//                     $fm=$this->facilityMedicine->find($fp->facility_medicine_id);
//                     $detail->facility_price_end_date = $fp->end_date;


//                     if (!empty($fm->adoption_stop_date)) {
//                         $detail->adoption_stop_date = date('Y/m/d',  strtotime($fm->adoption_stop_date) );
//                     }

//                     $detail->update_date = $fm->updated_at;
//                     $updater = $this->user->find($fm->updater);
//                     if (!empty($updater)) {
//                         $detail->updater_name = $updater->name;
//                     }
//                     $detail->comment = $fm->comment;


//                     $rec = $this->userGroup->getUserGroup($fm->sales_user_group_id);
//                     $detail->facility_name = $rec[0]->name;
//                     $detail->facility_price_list = $this->facilityPrice->getDataList($detail->medicine_id,$fm->id);

//                     $detail->key_facility_price_id= $fp->id;
//                     $detail->key_facility_medicine_id= $fm->id;

//                     //$detail->facility_name = $fac->name;
//                     $detail->sales_price = (float)$fp->sales_price;
//                     $detail->purchase_price = (float)$fp->purchase_price;
//                     $detail->start_date = date('Y-m-d',  strtotime($fp->start_date));
//                     $detail->trader_id = $fp->purchase_user_group_id;
//                     $trader = $this->getTrader($detail->trader_id);
//                     if(!empty($trader)){
//                         $detail->trader_name = $trader->name;
//                         $detail->purchase_user_group_id = $fp->purchase_user_group_id;
//                     }else{
//                         $detail->trader_name = "";
//                     }

//                     //$detail->trader_name = $fp->purchase_user_group_id

//                     $claim_rec=$this->medicine->getShowPriceApply($user->id,$fm->sales_user_group_id,$detail->status);
//                     $detail->sales_price_kengen=$claim_rec[0]->sales_price_kengen;
//                     $detail->purchase_price_kengen=$claim_rec[0]->purchase_price_kengen;

//                     $detail->edit_sales_price_kengen=$claim_rec[0]->edit_sales_price_kengen;
//                     $detail->edit_purchase_price_kengen=$claim_rec[0]->edit_purchase_price_kengen;

//                     if ($detail->edit_sales_price_kengen == 1 or $detail->edit_purchase_price_kengen == 1) {
//                         $detail->edit = true;

//                     }

//                     $ug=$this->userGroup->where('id', $fm->sales_user_group_id)->first();
//                     $detail->optional_key1_prt_label = $ug->optional_key1_label;
//                     $detail->optional_key2_prt_label = $ug->optional_key2_label;
//                     $detail->optional_key3_prt_label = $ug->optional_key3_label;
//                     $detail->optional_key4_prt_label = $ug->optional_key4_label;
//                     $detail->optional_key5_prt_label = $ug->optional_key5_label;
//                     $detail->optional_key6_prt_label = $ug->optional_key6_label;
//                     $detail->optional_key7_prt_label = $ug->optional_key7_label;
//                     $detail->optional_key8_prt_label = $ug->optional_key8_label;

//                     $detail->optional_key1_prt_value = $fm->optional_key1;
//                     $detail->optional_key2_prt_value = $fm->optional_key2;
//                     $detail->optional_key3_prt_value = $fm->optional_key3;
//                     $detail->optional_key4_prt_value = $fm->optional_key4;
//                     $detail->optional_key5_prt_value = $fm->optional_key5;
//                     $detail->optional_key6_prt_value = $fm->optional_key6;
//                     $detail->optional_key7_prt_value = $fm->optional_key7;
//                     $detail->optional_key8_prt_value = $fm->optional_key8;

//                 }

//               }

//             }

//             /*
//             if ($detail->edit == true) {
//                 $detail->optional_key_own_edit=1;
//             } else {
//                 $detail->optional_key_own_edit=0;

//             }

//             if ($detail->edit == true) {
//                 $ug=$this->userGroup->where('id', $detail->sales_user_group_id)->first();

//                 if (!empty($ug)) {
//                     $detail->optional_key1_own_label = $ug->optional_key1_label;
//                     $detail->optional_key2_own_label = $ug->optional_key2_label;
//                     $detail->optional_key3_own_label = $ug->optional_key3_label;
//                     $detail->optional_key4_own_label = $ug->optional_key4_label;
//                     $detail->optional_key5_own_label = $ug->optional_key5_label;
//                     $detail->optional_key6_own_label = $ug->optional_key6_label;
//                     $detail->optional_key7_own_label = $ug->optional_key7_label;
//                     $detail->optional_key8_own_label = $ug->optional_key8_label;

//                 }
//             }
//             */

//         }

//         $detail->fp_id=$request->fp_id;

//         if(isBunkaren()) {
//             if(!empty($detail->sales_user_group_id)) {
//                 $user_group_id = $detail->sales_user_group_id;
//                 $detail->facility_id = "";
//             } else {
//                 if(!empty($request->fp_id)){
//                     $fp = $this->facilityPrice->find($request->fp_id);
//                     $fm=$this->facilityMedicine->find($fp->facility_medicine_id);
//                     $user_group_id = $fm->sales_user_group_id;
//                     $detail->facility_id = "";
//                 } else {
//                     $user_group_id = $user->primary_user_group_id;
//                     $detail->facility_id = "";
//                 }
//             }

//         } else {
//              if(!empty($detail->sales_user_group_id)) {
//                 $user_group_id = $detail->sales_user_group_id;
//                 if($detail->sales_user_group_id == $user->primary_user_group_id) {
//                     $detail->facility_id = $detail->sales_user_group_id;
//                 }
//             } else {
//                 if(!empty($request->fp_id)){
//                     $fp = $this->facilityPrice->find($request->fp_id);
//                     $fm=$this->facilityMedicine->find($fp->facility_medicine_id);
//                     $user_group_id = $fm->sales_user_group_id;
//                     $detail->facility_id = $fm->sales_user_group_id;
//                 } else {
//                     $user_group_id = $user->primary_user_group_id;
//                     $detail->facility_id = "";
//                 }
//             }
//         }

//         if ($this->checkExpire($request, $user_group_id)) {

//             if( $request->flg === '0'){
//                 $detail->status = Task::STATUS_EXPIRE;
//                 $kengen_rec=$this->medicine->getPrivilegesApply($user->id, $user_group_id, $detail->status);
//                 $detail->status_forward = $kengen_rec[0]->status_forward;
//                 $detail->status_back = $kengen_rec[0]->status_back;
//                 $detail->status_back_text = $kengen_rec[0]->status_back_text;
//                 $detail->edit = false;
//             } else if( $request->flg === '1' and $detail->status >= Task::STATUS_ADOPTABLE) {
//                 $detail->status = Task::STATUS_EXPIRE;
//                 $kengen_rec=$this->medicine->getPrivilegesApply($user->id, $user_group_id, $detail->status);
//                 $detail->status_forward = $kengen_rec[0]->status_forward;
//                 $detail->status_back = $kengen_rec[0]->status_back;
//                 $detail->status_back_text = $kengen_rec[0]->status_back_text;
//                 $detail->edit = false;

//             }

//             $detail->sales_price = null;
//             $detail->purchase_price = null;
//             $detail->start_date = null;
//             $detail->trader_name = null; // 業者で使用
//             $detail->purchase_user_group_id = null; // 業者プルダウンのキー
//             if(empty($detail->facility_name)) {
//                 if(!empty($request->fa_id)) {
//                     $rec = $this->userGroup->getUserGroup($request->fa_id);
//                     $detail->facility_name = $rec[0]->name;
//                 }
//             }

//         }

//         // 単価履歴 自施設のものを表示
//             if($request->flg === "1"){
//                 $target_data = $this->priceAdoption->find($request->id);
//                 $medicine_id = $target_data->medicine_id;
//                 $user_group = $target_data->sales_user_group_id;
//             } else {
//                 $target_data = $this->medicine->find($request->id);
//                 $medicine_id = $target_data->id;
//                 $user_group = $user->primary_user_group_id;
//             }
//             $detail->facility_price_list = array();
//             if($detail->status == TASK::STATUS_ADOPTABLE){
//                 $detail->sales_price_rireki_kengen=-1;
//                 if(!empty($user_group)) {
//                     $facility_medicine = $this->facilityMedicine->getData($user_group, $medicine_id);
//                     logger($facility_medicine);
//                     if(!empty($facility_medicine)) {
//                         $detail->facility_price_list = $this->facilityPrice->getDataList($medicine_id, $facility_medicine->id);
//                         if (!empty($detail->facility_price_list)) {
//                             if($detail->status < Task::STATUS_UNCONFIRMED) {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,TASK::STATUS_ADOPTABLE);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             } else {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,$detail->status);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             }
//                         }
//                     }
//                 }
//             } else {
//               if(!empty($request->fp_id)){
//                 $detail->sales_price_rireki_kengen=-1;
//                 $facility_price = $this->facilityPrice->find($request->fp_id);
//                 if (!empty($facility_price)) {
//                     $facility_medicine = $this->facilityMedicine->find($facility_price->facility_medicine_id);
//                     if(!empty($facility_medicine)) {
//                         $detail->facility_price_list = $this->facilityPrice->getDataList($medicine_id, $facility_medicine->id);

//                         if (!empty($detail->facility_price_list)) {
//                             if($detail->status < Task::STATUS_UNCONFIRMED) {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,Task::STATUS_DONE);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             } else {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,$detail->status);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             }
//                         }
//                     }
//                 }
//               } else {

//                 $detail->sales_price_rireki_kengen=-1;
//                 if(!empty($user_group)) {
//                     $facility_medicine = $this->facilityMedicine->getData($user_group, $medicine_id);
//                     logger($facility_medicine);
//                     if(!empty($facility_medicine)) {
//                         $detail->facility_price_list = $this->facilityPrice->getDataList($medicine_id, $facility_medicine->id);
//                         if (!empty($detail->facility_price_list)) {
//                             if($detail->status < Task::STATUS_UNCONFIRMED) {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,Task::STATUS_DONE);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             } else {
//                                 $claim_rec=$this->medicine->getShowPriceApply($user->id,$facility_medicine->sales_user_group_id,$detail->status);
//                                 $detail->sales_price_rireki_kengen=$claim_rec[0]->sales_price_kengen;
//                             }
//                         }
//                     }
//                 }
//               }
//             }

//         // 期限切れの状態の場合、単価がとれない可能性がある為、再設定
//         if (empty($detail->sales_price)) {
//             // 申請中の状態のもの
//             if ($request->flg === '1') {
//                 // 承認待ち以下で単価がある場合は、本部と文化連は表示

//                 if($detail->status <= Task::STATUS_APPROVAL_WAITING ) { // 6以下の場合
//                     // price_adoptionから取得
//                     $target_data = $this->priceAdoption->find($request->id);
//                     logger($target_data);
//                     if (!empty($target_data)) {
//                         $detail->sales_price = (float)$target_data->sales_price;
//                         $detail->purchase_price = (float)$target_data->purchase_price;
//                         $detail->start_date = $target_data->start_date;
//                         $trader = $this->getTrader($target_data->purchase_user_group_id);
//                         if (!empty($trader)) {
//                             logger($trader);
//                             $detail->trader_name = $trader->name;
//                         }
//                         $detail->purchase_user_group_id = $target_data->purchase_user_group_id;
//                     }

//                 // 承認済み本部と文化連と病院は表示
//                 } else if ($detail->status == Task::STATUS_UNCONFIRMED) { //7の場合
//                     //病院はprice_adoption 本部はfacility_prices
//                     $target_data = $this->priceAdoption->find($request->id);
//                     logger($target_data);
//                     if (!empty($target_data)) {
//                         // price_adoptionのユーザーIDから病院か本部かを判定する
//                         $user_group_id = $target_data->sales_user_group_id;
//                         $user_group = $this->userGroup->getUserGroup($user_group_id);
//                         $user_group_type = $user_group[0]->group_type;
//                         if ($user_group_type == '病院') {
//                             // 本部のfacility_priceを取得
//                             $parent_user_group_id = $this->userGroup->find($user_group_id)->parent()[0]->id; // 本部
//                             $facility_medicine = $this->facilityMedicine->getData($parent_user_group_id, $target_data->medicine_id);
//                             if(!empty($facility_medicine)) {
//                                 $facility_price = $this->facilityPrice->getData($facility_medicine->id);
//                                 if (!empty($facility_price)) {
//                                     $detail->sales_price = (float)$facility_price->sales_price;
//                                     $detail->purchase_price = (float)$facility_price->purchase_price;
//                                     $detail->start_date = $facility_price->start_date;
//                                     $trader = $this->getTrader($facility_price->purchase_user_group_id);
//                                     if (!empty($trader)) {
//                                         $detail->trader_name = $trader->name;
//                                     }
//                                     $detail->purchase_user_group_id = $facility_price->purchase_user_group_id;

//                                 } else {
//                                     // 本部がない場合は、病院のprice_adoptionを使用
//                                     $detail->sales_price = (float)$target_data->sales_price;
//                                     $detail->purchase_price = (float)$target_data->purchase_price;
//                                     $detail->start_date = $target_data->start_date;
//                                     $trader = $this->getTrader($target_data->purchase_user_group_id);
//                                     if (!empty($trader)) {
//                                         $detail->trader_name = $trader->name;
//                                     }
//                                     $detail->purchase_user_group_id = $target_data->purchase_user_group_id;
//                                 }
//                             }

//                         } else {
//                             //自分のfacility_priceを取得
//                             $facility_medicine = $this->facilityMedicine->getData($user_group_id, $medicine_id);
//                             if(!empty($facility_medicine)) {
//                                 $facility_price = $this->facilityPrice->getData($facility_medicine->id);
//                                 if (!empty($facility_price)) {
//                                     $detail->sales_price = (float)$facility_price->sales_price;
//                                     $detail->purchase_price = (float)$facility_price->purchase_price;
//                                     $detail->start_date = $facility_price->start_date;
//                                     $trader = $this->getTrader($facility_price->purchase_user_group_id);
//                                     if (!empty($trader)) {
//                                         $detail->trader_name = $trader->name;
//                                     }
//                                     $detail->purchase_user_group_id = $facility_price->purchase_user_group_id;
//                                 }
//                             }
//                         }

//                     }

//                 }
//             }

//         }

//         // リストにボタン情報付加
//         //$detail->button = $this->button($detail);
//         $detail->button = $this->button_new($detail, $request);
//         //$detail->buttonRemand = $this->buttonRemand($detail);
//         $detail->buttonRemand = $this->buttonRemand_new($detail);
//         $detail->flg = $request->flg;
//         $detail->primary_user_group_id =$user->primary_user_group_id;

//         if(!empty($request->page)){
//             $detail->page = $request->page;
//         }

//         return $detail;
//     }

    /*
     * 業者一覧
     */
//     public function getTraders_old(Request $request)
//     {
//         $user_group_id;
//         if($request->flg === "1"){
//             $detail = $this->priceAdoption->find($request->id);
//             $user_group_id=$detail->sales_user_group_id;
//         } else {

//             $fp = $this->facilityPrice->find($request->fp_id);

//             if (!empty($fp)) {
//                 $fm=$this->facilityMedicine->find($fp->facility_medicine_id);
//                 $user_group_id=$fm->sales_user_group_id;
//             } else {
//                 $user_group_id=-1;
//             }
//         }

//         //return $this->facility->where('actor_id', Actor::ACTOR_TRADER)->get();
//         return $this->groupTraderRelation->getGroupTraderList($user_group_id);
//     }

    /*
     * next
     */
//     public function next_old(Request $request)
//     {
//         try {
//             // valation取得
//             /*
//              $valiation = $this->valiation->where('price_adoption_id', $request->id)->first();

//              // price_adoptions取得
//              $pa = $this->priceAdoption->find($request->id);
//              $next = Task::next($valiation->current_task_id);

//              $user = Auth::user();
//              $kengen = $this->medicine->getPrivilegesApply($user->id,$pa->sales_user_group_id,$pa->status);

//              $pa->approval_user_id = $user->id;
//              $pa->status = $next->status; // 現在のステータスをセット
//              if( !empty($request->comment) ){
//              $pa->comment = $request->comment;
//              }
//              $pa->save();

//              // valiationデータ更新
//              $valiation->current_task_id = $valiation->next_task_id;
//              $next = Task::next($valiation->current_task_id);
//              if (is_null($next)) {
//              $valiation->next_task_id = 0;
//              } else {
//              $valiation->next_task_id = $next->id;
//              }
//              $valiation->save();
//              */
//             $pa = $this->priceAdoption->find($request->id);
//             $user = Auth::user();
//             $kengen = $this->medicine->getPrivilegesApply($user->id,$pa->sales_user_group_id,$pa->status);
//             $pa->approval_user_id = $user->id;
//             $pa->status = $kengen[0]->status_forward; // 現在のステータスをセット
//             $pa->revert_type = self::REVERT_TYPE_INITIAL;
//             if( !empty($request->comment) ){
//                 $pa->comment = $request->comment;
//             }
//             $pa->updater = $user->id;
//             $pa->save();

//             return true;

//         } catch (\PDOException $e){
//             throw $e;
//         }
//     }


    /*
     * next
     */
//     public function next2($price_adoption_id)
//     {
//         try {
//             // valation取得
//             $valiation = $this->valiation->where('price_adoption_id', $price_adoption_id)->first();

//             // price_adoptions取得
//             $pa = $this->priceAdoption->find($price_adoption_id);
//             $next = Task::next($valiation->current_task_id);

//             $user = Auth::user();
//             $pa->approval_user_id = $user->id;
//             $pa->status = $next->status; // 現在のステータスをセット
//             $pa->updater = $user->id;

//             $pa->save();

//             // valiationデータ更新
//             $valiation->current_task_id = $valiation->next_task_id;
//             $next = Task::next($valiation->current_task_id);
//             if (is_null($next)) {
//                 $valiation->next_task_id = 0;
//             } else {
//                 $valiation->next_task_id = $next->id;
//             }
//             $valiation->save();

//             return true;

//         } catch (\PDOException $e){
//             throw $e;
//         }
//     }

    /*
     * prev
     */
//     public function prev_old(Request $request)
//     {
//         \DB::beginTransaction();
//         try {
//             // price_adoptions取得
//             $pa = $this->priceAdoption->find($request->id);

//             //承認済み
//             if( $pa->status == Task::STATUS_UNCONFIRMED ){
//                 $parent_id = $this->userGroup->find($pa->sales_user_group_id)->parent()[0]->id;

//                 //本部薬品を取得
//                 $hq_fm = $this->facilityMedicine->getData($parent_id, $pa->medicine_id);
//                 $isDelete = true;

//                 //foreach($pa->facility->parent()->children as $child) {
//                 foreach($this->userGroup->find($pa->sales_user_group_id)->children() as $child) {
//                     //配下の病院薬品があるか確認
//                     $own_fm = $this->facilityMedicine->getData($child->id, $pa->medicine_id);
//                     if( !empty($own_fm) ){
//                         $isDelete = false;
//                         break;
//                     }
//                 }
//                 //本部薬品あり、病院薬品なし
//                 if( !empty($hq_fm) && $isDelete ){
//                     $hq_fp = $this->facilityPrice->getData($hq_fm->id);
//                     $hq_fp->delete();
//                     $hq_fm->delete();
//                 }
//             }
//             //承認待ち
//             if( $pa->status == Task::STATUS_APPROVAL_WAITING ){
//                 //新規登録
//                 if( !empty($pa->name) ){
//                     $del_medicine = $this->medicine->find($pa->medicine_id);
//                     $del_medicine->delete();
//                     $pa->medicine_id = null;//標準薬品は空にする
//                     $pa->save();
//                 }
//             }
//             //TODO
//             //申請中は、申請データを論理削除
//             if( $pa->status == Task::STATUS_APPLYING ){
//                    $pa->delete();
//             }

//             if ($pa->status == Task::STATUS_NEGOTIATING) {
//                 $hq_user = Auth::user();
//                 //申請IDが本部(自身)と一緒なら採用済にする。
//                 if ($pa->sales_user_group_id == $hq_user->primary_user_group_id) {
//                     $pa->delete();
//                 }

//             }

//             $user = Auth::user();
//             // TODO 戻しのステータスを確認
//             $kengen = $this->medicine->getPrivilegesApply($user->id,$pa->sales_user_group_id,$pa->status);
//             $status=$kengen[0]->status_back;

//             $pa->approval_user_id = $user->id;
//             $pa->status = $status; // 現在のステータスをセット
//             $pa->revert_type = self::REVERT_TYPE_WITHDRAW; // 取り下げ
//             if( !empty($request->comment) ){
//                 $pa->comment = $request->comment;
//             }else if( !empty($request->comment2) ){
//                 $pa->comment = $request->comment2;
//             }
//             $pa->save();
// /*
//             // valation取得
//             $valiation = $this->valiation->where('price_adoption_id', $request->id)->first();

//             // price_adoptions取得
// //            $pa = $this->priceAdoption->find($request->id);
//             $prev = Task::prev($valiation->current_task_id);

//             $user = Auth::user();
//             $pa->approval_user_id = $user->id;
//             $pa->status = $prev->status; // 現在のステータスをセット
//             if( !empty($request->comment) ){
//                 $pa->comment = $request->comment;
//             }else if( !empty($request->comment2) ){
//                 $pa->comment = $request->comment2;
//             }
//             $pa->save();

//             // valiationデータ更新
//             $valiation->next_task_id = $valiation->current_task_id;
//             $prev = Task::prev($valiation->next_task_id);
//             if (is_null($prev)) {
//                 $valiation->current_task_id = 0;
//             } else {
//                 $valiation->current_task_id = $prev->id;
//             }
//             $valiation->save();
// */

//             \DB::commit();

//             return true;

//         } catch (\PDOException $e){
//             \DB::rollBack();
//             return false;
//         }
//     }

    /*
     * 差し戻し処理
     */
//     public function remand_old(Request $request)
//     {
//         $pa = $this->priceAdoption->find($request->id);

//         //メール送信To対象者を取得
//         $mail_target_id = $pa->approval_user_id;

//         \DB::beginTransaction();
//         try {
//             // 差し戻し先にメール
// /*
//             switch($pa->status)
//             {
//             case Task::STATUS_APPLYING:
//                 // 病院にメール
//                 $mail_users = $pa->facility->users()->isAdoptionMail()->get();

//                 //病院への差し戻しは、申請者をToにする
//                 $mail_target_id = $pa->user_id;

//                 break;
//             case Task::STATUS_NEGOTIATING:
//             case Task::STATUS_UNCONFIRMED:
//                 // 本部にメール
//                 $mail_users = $pa->facility->parent()->users()->isAdoptionMail()->get();
//                 break;
//             case Task::STATUS_APPROVAL_WAITING:
//                 // 文化連にメール
//                 $mail_users = $pa->facility->bunkaren()->first()->users()->isAdoptionMail()->get();
//                 break;
//             }

//             $mq = new MailQueService($this->mailQue->getMailQueID());
//             foreach($mail_users as $mail_user) {
//                 //担当者以外はCCで送信する
//                 // if($mail_user->id == $mail_target_id){
//                 //     $is_target = 1;
//                 // }else{
//                 //     $is_target = 0;
//                 // }
//                 $is_target = 1;
//                 //$this->sendMail($mail_user->email, $pa, true);
//                 $mq->send_mail_add($mail_user->email, $pa, $is_target, true);
//             }

//             if (!$this->prev($request)) {
//                 return false;
//             }
// */
//             $user = Auth::user();
//             $kengen = $this->medicine->getPrivilegesApply($user->id,$pa->sales_user_group_id,$pa->status);

//             $mail_users = $this->medicine->getEmailApply($kengen[0]->status_back,$pa->sales_user_group_id,$user->primary_user_group_id);
//             $mq = new MailQueService($this->mailQue->getMailQueID());
//             foreach($mail_users as $mail_user) {
//                 $mq->send_mail_add($mail_user->email, $pa,1,true);
//             }

//             $pa->approval_user_id = $user->id;
//             $pa->status = $kengen[0]->status_back; // 現在のステータスをセット
//             $pa->revert_type = self::REVERT_TYPE_REMAND; // 差し戻し
//             if( !empty($request->comment) ){
//                 $pa->comment = $request->comment;
//             }else if( !empty($request->comment2) ){
//                 $pa->comment = $request->comment2;
//             }

//             //未申請になったら、申請データを論理削除
//             if ($pa->status == Task::STATUS_UNAPPLIED) {
//                 $pa->revert_type = self::REVERT_TYPE_REMAND; // 差し戻し
//                 $pa->deleted_at=date("Y/m/d H:i:s");
//                 $pa->deleter=$user->id;
//             }


//             $pa->save();


//             \DB::commit();
//             $request->session()->flash('message', '更新しました');

//         } catch (\PDOException $e){
//             $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
//             \DB::rollBack();
//             return false;
//         }

//         return true;
//     }

    /*
     * 採用一覧のボタンの情報を返す
     */
//     public function button_new_old($apply, Request $request = null)
//     {
//         $user = Auth::user();
//         $property = [
//                 'url' => null,
//                 'label' => '',
//                 'style' => ''
//         ];

//         $view_route="";
//         $view_label="";
//         $view_style="";

//         if ($apply->status_forward > 0) {
//             switch ($apply->status_forward) {
//                 case TASK::STATUS_APPLYING:
//                     $url = ($apply->medicine_id) ?
//                     route('apply.regist', ['id' => $apply->medicine_id ]) : route('apply.reentry', ['id' => $apply->price_adoption_id]);
//                     $property = [
//                             'url' => $url,
//                             'label' => '採用申請',
//                             'style' => 'btn-primary'
//                     ];
//                     break;

//                 case TASK::STATUS_NEGOTIATING:

//                     if (empty($apply->price_adoption_id)) {
//                         $property = [
//                                 'url' => route('apply.allow',['id' =>"0"]),
//                                 'label' => '交渉許可',
//                                 'style' => 'btn-primary'
//                         ];

//                     } else {
//                         $property = [
//                                 'url' => route('apply.allow',['id' => $apply->price_adoption_id ]),
//                                 'label' => '交渉許可',
//                                 'style' => 'btn-primary'
//                         ];

//                     }

//                     break;

//                 case TASK::STATUS_APPROVAL_WAITING:
//                     $property = [
//                             'url' => route('apply.confirm',['id' => $apply->price_adoption_id ]),
//                             'label' => '価格登録',
//                             'style' => 'btn-danger'
//                     ];
//                     break;

//                 case TASK::STATUS_UNCONFIRMED:
//                     $property = [
//                             'url' => route('apply.approval',['id' => $apply->price_adoption_id ]),
//                             'label' => '採用承認',
//                             'style' => 'btn-danger'
//                     ];
//                     break;

//                 case TASK::STATUS_DONE:

//                     if ($apply->status === TASK::STATUS_ADOPTABLE){
//                            $property = [
//                            'url' => route('apply.adopt2', ['id' => $apply->medicine_id ]),
//                            'label' => '採用',
//                            'style' => 'btn-danger'
//                            ];
//                        } else if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->sales_user_group_id == $user->primary_user_group_id) {
//                            // 採用可は「採用」ボタン
//                            $property = [
//                                 'url' => route('apply.adopt',['id' => $apply->price_adoption_id ]),
//                                 'label' => '採用',
//                                 'style' => 'btn-danger'
//                         ];
//                     } else {
//                         // 採用可は「採用」ボタン
//                         $property = [
//                                 'url' => route('apply.adopt',['id' => $apply->price_adoption_id ]),
//                                 'label' => '採用',
//                                 'style' => 'btn-danger'
//                         ];
//                     }
//                     break;
//             }
//         } else if ($apply->status_back > 0) {
//             if ($apply->status_back_text == '取り下げ') {
//                 $property = [
//                         'url' => route('apply.withdraw',['id' => $apply->price_adoption_id ]),
//                         'label' => $apply->status_back_text,
//                         'style' => 'btn-secondary'
//                 ];
//             }


//         } else {

//             if ($apply->status === TASK::STATUS_EXPIRE) {
//                 $hq_fm = $this->facilityMedicine->getData($user->primary_honbu_user_group_id, $apply->medicine_id);

//                     if ($apply->facility_id === $user->primary_user_group_id
//                         || (empty($apply->facility_id) && !empty($hq_fm) && $hq_fm->sales_user_group_id == $user->primary_honbu_user_group_id && empty($request->fa_id))) {
//                             $kengen_rec=$this->medicine->getPrivilegesApply($user->id, $user->primary_user_group_id, Task::STATUS_UNAPPLIED);

//                         if ($kengen_rec[0]->status_forward > 0 ) {
//                             $property = [
//                                 'url' => route('apply.reapply', ['id' => $apply->medicine_id ]),
//                                 'label' => '再申請',
//                                 'style' => 'btn-primary'
//                             ];
//                             return $property;
//                         }
//                     }
//             }
//             return;
//         }
//         return $property;

//     }


    /*
     * 再申請
     */
//     public function reapply_old(Request $request)
//     {
//         \DB::beginTransaction();
//         try {
//             $medicine = $this->medicine->find($request->id);
//             $user = Auth::user();
//             $data = array();
//             $udata = array();

//             if (empty($request->apply_group)) {
//                 $sales_user_group_id = $user->primary_user_group_id;
//             } else {
//                 $sales_user_group_id = $request->apply_group;

//             }

//             // TODO 再申請の処理は変更が必要 price_adoption_logが入ることにより、
//             // 単純にprice_adoptionsだけ更新すればいいというわけにはいかなくなった。。。
//             //$priceAdoptionLogを消す処理が修正後はいる
//             $priceAdoption = $this->priceAdoption->getData($user->primary_honbu_user_group_id, $medicine->id);
//             if (is_null($priceAdoption)) {
//             }else{
//                 $udata['comment'] = '再申請削除';
//                 $udata['revert_type'] = self::REVERT_TYPE_INITIAL;
//                 $priceAdoption->update($udata);

//                 $priceAdoption->delete();
//             }

//             if ($user->primary_honbu_user_group_id == $user->primary_user_group_id) {
//                 $children = $this->user->select('primary_user_group_id as id')->distinct()
//                                        ->where('primary_honbu_user_group_id', $user->primary_honbu_user_group_id)
//                                        ->where('primary_user_group_id', '<>', $user->primary_honbu_user_group_id)
//                                        ->get();
//             } else {
//                 $children = app('UserContainer')->getUserGroup()->children();
//             }

//             foreach($children as $child) {

//                 $priceAdoption = $this->priceAdoption->getData($child->id, $medicine->id);
//                 if (is_null($priceAdoption)) {
//                 }else{
//                     $udata['comment'] = '再申請削除';
//                     $udata['revert_type'] = self::REVERT_TYPE_INITIAL;
//                     $priceAdoption->update($udata);

//                     $priceAdoption->delete();
//                 }

//             }

//             $kengen_rec=$this->medicine->getPrivilegesApply($user->id,$sales_user_group_id,Task::STATUS_UNAPPLIED);

//             $data['status'] =$kengen_rec[0]->status_forward;
//             $data['revert_type'] = self::REVERT_TYPE_INITIAL;
//             $data['user_id'] = $user->id;
//             $data['application_date'] = date('Y-m-d H:i:s');

//             // TODO 業者用項目追加
//             // 薬価基準日
//             $data['basis_mediicine_price_date'] = date('Y-m-d');
//             // 申請日の日付でmedicine_priceを取得する
//             $medicine_price = $medicine->medicinePrice->price * $medicine->packUnit->coefficient;
//             $data['basis_mediicine_price'] = $medicine_price;

//             $data['search'] = mb_convert_kana($medicine->packUnit->jan_code, 'KVAS').mb_convert_kana($medicine->name, 'KVAS').mb_convert_kana($medicine->maker->name, 'KVAS').mb_convert_kana($medicine->popular_name, 'KVAS').mb_convert_kana($medicine->standard_unit, 'KVAS').mb_convert_kana($medicine->medicine_code, 'KVAS');

//             $data['sales_user_group_id'] = $sales_user_group_id;

//             $data['medicine_id'] = $request->id;
//             $priceAdoption = $this->priceAdoption->create($data);

//             //TODO facility_medicinesの戻しがいる
//             $fa = $this->facilityMedicine->getData($sales_user_group_id, $request->id);
//             $fa->is_approval = false;
//             $fa->save();

//             $mail_users = $this->medicine->getEmailApply($priceAdoption->status,$sales_user_group_id);

//             $mq = new MailQueService($this->mailQue->getMailQueID());
//             foreach($mail_users as $mail_user) {
//                 $mq->send_mail_add($mail_user->email, $priceAdoption);
//             }

//             \DB::commit();

//             return true;

//         } catch (\PDOException $e){
//             echo $e->getMessage();
//             \DB::rollBack();
//             return false;
//         }
//     }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     */
//     public function buttonRemand_new_old($apply)
//     {
//         $user = Auth::user();
//         $property = [
//                 'url' => null,
//                 'label' => ''
//         ];

//         if ($apply->status_back > 0 && $apply->status_forward > 0) {

//             //採用可は差戻不可
//             if ($apply->status === TASK::STATUS_ADOPTABLE) {
//                 return;
//             }

//             $property = [
//                 'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
//                 'label' => $apply->status_back_text
//             ];
//         }
//         return $property;

//     }


    /*
     * 採用申請一覧取得（削除対象）
     */
//     public function getApplyList2(Request $request, $user)
//     {
//         $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;

//         $medicine_id = "";
//         if($request->flg === "1"){
//             $detail = $this->priceAdoption->find($request->id);
//             $medicine_id = $detail->medicine_id;
//         } else {
//             $detail = $this->medicine->find($request->id);
//             $medicine_id = $detail->id;
//         }
//         if (is_null($detail)) {
//             return null;
//         }

//         if (!empty($medicine_id)) {
//             $list = $this->medicine->listWithApply2($medicine_id, $user, $count, $request->fp_id);
//         }else{
//             return null;
//         }

//         return $list;
//     }

    /*
     * 採用申請一覧取得(削除対象)
     */
//     public function getApplyList_old(Request $request, $user, $is_dashboard = false)
//     {
//         $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
//         if($is_dashboard){
//             $count = -1;
//         }
//         $list =  $this->medicine->listWithApply($request, $user->facility, $count);

//         // リストにボタン情報付加
//         if (!is_null($list)) {
//             foreach($list as $key => $apply) {
//                 $list[$key]->button = $this->button($apply);
//             }
//         }
//         return $list;
//     }

    /*
     * 期限切れチェック
     */
//     public function checkExpire(Request $request, $user_group_id)
//     {
//         $expire = false;

//         if ($request->flg === "1") {
//             $target_data = $this->priceAdoption->find($request->id);
//             $m_id = $target_data->m_id;
//         } else {
//             $target_data = $this->medicine->find($request->id);
//             $m_id = $target_data->id;
//         }

//         if (!empty($request->fp_id)) {
//             $facility_price = $this->facilityPrice->find($request->fp_id);
//             if (!empty($facility_price)) {
//                 if ($facility_price->start_date <= date('Y-m-d H:i:s') && $facility_price->end_date >= date('Y-m-d H:i:s')) {
//                     $expire = false;
//                 } else {
//                     $expire = true;
//                 }
//             }
//         } else {
//             $user_group = $this->userGroup->getUserGroup($user_group_id);
//             logger($user_group);
//             $user_group_type = $user_group[0]->group_type;
//             if ($user_group_type == '病院') {
//                 $facility_medicine = $this->facilityMedicine->getData($user_group_id, $m_id); // 病院
//                 if (empty($facility_medicine)) {
//                     // 本部
//                     $parent_user_group_id = $this->userGroup->find($user_group_id)->parent()[0]->id; // 本部
//                     $facility_medicine = $this->facilityMedicine->getData($parent_user_group_id, $m_id);
//                     if (!empty($facility_medicine)) {
//                         $facility_price = $this->facilityPrice->getLastData($facility_medicine->id);
//                         if (!empty($facility_price)) {
//                             if ($facility_price->start_date <= date('Y-m-d H:i:s') && $facility_price->end_date >= date('Y-m-d H:i:s')) {
//                                 $expire = false;
//                             } else {
//                                 $expire = true;
//                             }
//                         }
//                     }

//                 } else {
//                     // 病院単体
//                     $facility_price = $this->facilityPrice->getLastData($facility_medicine->id);
//                     if (!empty($facility_price)) {
//                         if ($facility_price->start_date <= date('Y-m-d H:i:s') && $facility_price->end_date >= date('Y-m-d H:i:s')) {
//                             $expire = false;
//                         } else {
//                             // 採用可のチェック 病院がないけど本部があるか
//                             $parent_user_group_id = $this->userGroup->find($user_group_id)->parent()[0]->id; // 本部
//                             $facility_medicine = $this->facilityMedicine->getData($parent_user_group_id, $m_id);
//                             if (!empty($facility_medicine)) {
//                                 $facility_price = $this->facilityPrice->getLastData($facility_medicine->id);
//                                 if ($facility_price->start_date <= date('Y-m-d H:i:s') && $facility_price->end_date >= date('Y-m-d H:i:s')) {
//                                     $expire = false;
//                                 } else {
//                                     $expire = true;
//                                 }
//                             }
//                         }
//                     }
//                 }
//             } else {
//                 $facility_medicine = $this->facilityMedicine->getData($user_group_id, $m_id); // 本部

//                 if (!empty($facility_medicine)) {
//                     $facility_price = $this->facilityPrice->getLastData($facility_medicine->id);
//                     if (!empty($facility_price)) {
//                         if ($facility_price->start_date <= date('Y-m-d H:i:s') && $facility_price->end_date >= date('Y-m-d H:i:s')) {
//                             $expire = false;
//                         } else {
//                             $expire = true;
//                         }
//                     }
//                 }
//             }

//         }

//         return $expire;

//     }

    /*
     * 標準薬品情報取得
     */
//     public function getMedicine($id) {
//         return $this->medicine->find($id);
//     }

    /*
     * 業者を取得
     */
//     public function getTrader($trader_id) {
//         return $this->userGroup->where('id', $trader_id)->first();
//     }

    /*
     * バリエーション作成
     */
//     private function makeValiation($priceAdoption) {
//         // valiationデータ作成
//         $data = array();
//         $valiation = $this->valiation->getByPriceAdoptionID($priceAdoption->id);

//         $data['pa_id'] = $priceAdoption->id;
//         $data['current_task_id'] = Task::getByStatus($priceAdoption->status)->id;
//         $data['next_task_id'] = Task::next($data['current_task_id'])->id;
//         //$data['next_task_id'] = Task::getByStatus($priceAdoption->next_status)->id;

//         if (is_null($valiation)) {
//             $this->valiation->create($data);
//         } else {
//             $valiation->update($data);
//         }
//     }

    /*
     * logテーブルへの移行処理
     */
//     public function move_log(Request $request,$user_id){

//         $pa = $this->priceAdoption->where('id', $request->id)->where('status',100)->where('deleted_at', null)->first();

//         if (!empty($pa)) {
//             //移設
//             $data['medicine_id'] = $pa->medicine_id;
//             $data['user_id'] = $pa->user_id;
//             $data['application_date'] = $pa->application_date;
//             $data['status'] = $pa->status;
//             $data['approval_user_id'] = $pa->approval_user_id;
//             $data['purchase_price'] = $pa->purchase_price;
//             $data['sales_price'] = $pa->sales_price;
//             $data['creater'] = $user_id;
//             $data['updater'] = $user_id;
//             $data['created_at'] = date('Y-m-d H:i:s');
//             $data['updated_at'] = date('Y-m-d H:i:s');
//             $data['comment'] = $pa->comment;
//             $data['jan_code'] = $pa->jan_code;
//             $data['start_date'] = $pa->start_date;
//             $data['end_date'] = $pa->end_date;
//             $data['name'] = $pa->name;
//             $data['standard_unit'] = $pa->standard_unit;
//             $data['pack_unit_price'] = $pa->pack_unit_price;
//             $data['coefficient'] = $pa->coefficient;
//             $data['search'] = $pa->search;
//             $data['maker_name'] = $pa->maker_name;
//             $data['sales_packaging_code'] = $pa->sales_packaging_code;
//             $data['sales_user_group_id'] = $pa->sales_user_group_id;
//             $data['id_send'] = $pa->id_send;
//             $data['purchase_user_group_id'] = $pa->purchase_user_group_id;
//             $data['revert_type'] = $pa->revert_type;
//             $data['priority'] = $pa->priority;
//             $data['basis_mediicine_price'] = $pa->basis_mediicine_price;
//             $data['basis_mediicine_price_date'] = $pa->basis_mediicine_price_date;
//             $data['purchase_requested_price'] = $pa->purchase_requested_price;
//             $data['purchase_estimated_price'] = $pa->purchase_estimated_price;
//             $data['purchase_estimate_comment'] = $pa->purchase_estimate_comment;
//             $data['sale_estimate_comment'] = $pa->sale_estimate_comment;
//             $data['source_pa_id'] = $pa->source_pa_id;
//             $data['request_numbers'] = $pa->request_numbers;

//             logger('PRLOGGGGGGGGGGG');
//             logger($data);

//             $priceAdoptionLog = $this->priceAdoptionLog->create($data);

//             // 対象のpriceAdoptionがあれば物理削除
//             $pa->forceDelete();
//         }
//     }

    /*
     * メール送信
     */
//     public function sendMail($email, PriceAdoption $pa, $remand = false) {
//         $data['facility'] = $pa->facility;
//         $data['jan_code'] = ($pa->isRegisted()) ? $pa->jan_code : $pa->medicine->packUnit->jan_code;
//         $data['name'] = ($pa->isRegisted()) ? $pa->name : $pa->medicine->name;
//         $data['maker_name'] = empty($pa->maker_name) ? $pa->medicine->maker->name : $pa->maker_name;

//         $data['mail_str'] = self::MAIL_STR[$pa->status];
//         $data['subject'] = sprintf("%s%s%sされました(%s)", \Config::get('mail.subject.prefix'), self::MAIL_STR[$pa->status], ($remand) ? 'が差し戻し' : '', $pa->facility->name);
//         $data['url'] = asset(route('apply.detail', array('id' => $pa->id, 'flg' => 1)));

//         try {
//             Mail::to($email)->send(new AdoptionMail($data));
//         } catch (\Exception $e){
//             return false;
//         }
//     }
// }
