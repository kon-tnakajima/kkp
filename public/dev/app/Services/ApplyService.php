<?php

namespace App\Services;

// use App\Model\ActionNameStatusForward;
use App\Model\Concerns\UserGroup as UserGroupTrait;
// use App\Model\Facility;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use App\Model\GroupTraderRelation;
use App\Model\MailQue;
// use App\Model\Maker;
use App\Model\Medicine;
use App\Model\MedicinePrice;
use App\Model\PackUnit;
use App\Model\PriceAdoption;
// use App\Model\PriceAdoptionLog;
use App\Model\Task;
use App\Model\UserGroup;
// use App\Model\Valiation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplyService extends BaseService {
    use UserGroupTrait;

    const DEFAULT_PAGE_COUNT = 20;

    const MAIL_STR = array(
        Task::STATUS_APPLYING         => '申請',
        Task::STATUS_NEGOTIATING      => '交渉許可',
        Task::STATUS_APPROVAL_WAITING => '価格登録',
        Task::STATUS_UNCONFIRMED      => '採用承認',
    );

    const STATUS_UNAPPLIED_LABEL = '採用申請';

    const STATUS_APPLYING_LABEL = '交渉許可';

    const STATUS_NEGOTIATING_LABEL = '価格登録';

    const STATUS_APPROVAL_WAITING_LABEL = '採用承認';

    const STATUS_UNCONFIRMED_LABEL = '採用';

    const STATUS_ADOPTABLE_LABEL = '採用';

    const REVERT_TYPE_INITIAL = 0; // 取り下げでも差し戻しでもない

    const REVERT_TYPE_WITHDRAW = 1; // 取り下げ

    const REVERT_TYPE_REMAND = 2; // 差し戻し

    const REVERT_TYPE_REJECT = 3;   // 見送り

    /**
     * コンストラクタ
     */
    public function __construct() {
        $this->medicine                = new Medicine();
        $this->priceAdoption           = new PriceAdoption();
        $this->facilityMedicine        = new FacilityMedicine();
        // $this->valiation               = new Valiation();
        $this->facilityMedicine        = new FacilityMedicine();
        // $this->facility                = new Facility();
        // $this->maker                   = new Maker();
        $this->tasks                   = new Task();
        $this->facilityPrice           = new FacilityPrice();
        $this->medicinePrice           = new MedicinePrice();
        $this->packUnit                = new PackUnit();
        $this->mailQue                 = new MailQue();
        $this->user                    = new User();
        $this->userGroup               = new UserGroup();
        $this->groupTraderRelation     = new GroupTraderRelation();
        // $this->priceAdoptionLog        = new PriceAdoptionLog();
        // $this->actionNameStatusForward = new ActionNameStatusForward();
    }

    /**
     * 採用申請一覧取得.
     * @param Request $request
     * @param mixed   $user
     * @param mixed   $is_dashboard
     */
    public function getApplyIndexList(Request $request, $user, $is_dashboard = false) {
        $this->start_log();

        $count = $is_dashboard
            ? -1
            : ($request->page_count)
                ? $request->page_count
                : self::DEFAULT_PAGE_COUNT;

        $list = $this->medicine->listWithApplyIndex($request, $count, $user);

        // リストにボタン情報付加
        foreach ($list as $key => $apply) {
            $list[$key]->button = $this->button_new($apply);
        }

        $this->end_log();

        return $list;
    }

    /**
     * 採用申請詳細取得.
     * @param Request $request
     * @param mixed   $user
     */
    public function getApplyDetailList(Request $request, $user) {
        $this->start_log();

        $list = $this->medicine->listWithApplyDetail($request, $user);

        $this->end_log();

        return $list;
    }

    /**
     * 採用申請詳細取得.
     * @param Request $request
     */
    public function getApplyDetail(Request $request) {
        $this->start_log();

        $user   = Auth::user();
        $kengen = $user->getUserKengen($user->id);

        // 詳細データ取得
        $m_id         = $request->id ? $request->id : 0;
        $fm_id        = $request->fm_id ? $request->fm_id : 0;
        $fp_id        = $request->fp_id ? $request->fp_id : 0;
        $pa_id        = $request->pa_id ? $request->pa_id : 0;
        $fp_honbu_id  = $request->fp_honbu_id ? $request->fp_honbu_id : 0;
        if ($fm_id != 0) {$sales_user_group_id = $this->facilityMedicine->find($fm_id)->sales_user_group_id;}

        // 詳細表示項目取得
        $detail = $this->medicine->getApplyDetailData($user, $m_id, $fm_id, $fp_id, $pa_id, $fp_honbu_id);

        // 個別設定項目
        // 薬価履歴
        $detail->medicine_price_list = $this->medicinePrice->getData($request->id);
        // 単価履歴
        $detail->facility_price_list =
            // $request->fm_id ? $this->facilityPrice->getDataList($request->fm_id) : array();
            $request->fm_id ? $this->facilityPrice->getDataList($m_id,$sales_user_group_id,$user) : array();

        // 申請履歴
        $detail->price_adoption_list =
            $request->fm_id ? $this->priceAdoption->getDataList($m_id,$sales_user_group_id,$user) : array();

        $detail->id = $request->pa_id ? $request->pa_id : $request->id;
        // 権限設定
        $detail->kengen = $kengen;
        // ボタン情報
        if ($detail->status == '91') {
            $detail->url_forward = route('apply.adopt2', array('id' => $detail->m_id));
        } else {
            $detail->url_forward = $detail->status_forward_url
                ? route($detail->status_forward_url, array('id' => $detail->m_id))
                : '';
        }
        $detail->url_remand = $detail->status_remand_url
            ? route($detail->status_remand_url, array('id' => $detail->m_id))
            : '';
        $detail->url_reject = $detail->status_reject_url
            ? route($detail->status_reject_url, array('id' => $detail->m_id))
            : '';
        $detail->url_withdraw = $detail->status_withdraw_url
            ? route($detail->status_withdraw_url, array('id' => $detail->m_id))
            : '';
        $detail->primary_user_group_id = $user->primary_user_group_id;
        $detail->page                  = $request->page ? $request->page : '';

        $detail->traders = $this->groupTraderRelation->getGroupTraderList(isset($sales_user_group_id) ? $sales_user_group_id : $user->primary_user_group_id);

        $this->end_log();

        return $detail;
    }

    // 検索フォームの施設チェックボックスに使用する施設一覧
    public function getFacilities($user) {
        $this->start_log();

        $list = $this->userGroup->getFacilityList($user);

        $this->end_log();

        return $list;
    }

    /**
     * 検索フォームの施設チェックボックスに使用する施設一覧.
     * @param User $user ユーザ情報
     * @return UserGroup情報(id,name,disp_orderの3カラム1組の複数)
     */
    public function getUserGroups($user) {
        $this->start_log();

        $list = $this->userGroup->getUserGroups($user);

        $this->end_log();

        return $list;
    }

        /**
     * 検索フォームの施設チェックボックスに使用する施設一覧.
     * @param User $user ユーザ情報
     * @return UserGroup情報(id,name,disp_orderの3カラム1組の複数)
     */
    public function getUserGroupsFacility($user) {
        $this->start_log();

        $list = $this->userGroup->getUserGroupsFacility($user);

        $this->end_log();

        return $list;
    }


    /**
     * 業者一覧.
     *
     * @param Request $request
     */
    public function getTraders(Request $request,$user) {
        $this->start_log();

        if (!empty($request->fm_id)) {
            $fm            = $this->facilityMedicine->find($request->fm_id);
            $user_group_id = $fm->sales_user_group_id;
        } else {
            $user_group_id = $user->primary_user_group_id; //1;
        }

        $list = $this->groupTraderRelation->getGroupTraderList($user_group_id);

        $this->end_log();

        return $list;
    }

     /**
     * basis_mediicine_price_dateとbasis_mediicine_priceの計算
     * @param Medicine $medicine 薬品情報
     * @param string $inputDate 薬価基準日の入力値
     * @return basis_mediicine_price_date 薬価基準日
     * @return basis_mediicine_price 基準日薬価
     */
    public function calcBasisMediicinePrice($medicine,$inputDate) {
        $basis = array();
        $medicinePriceCurrent = $medicine->medicinePrice;

        if (!empty($inputDate)) {
            $basis_date = $inputDate;//薬価基準日の入力値
        } elseif (!empty($medicinePriceCurrent)) {
            $basis_date = $medicinePriceCurrent->start_date;//現在有効な薬価の改定日
        } else{
            $basis_date = date('Y-m-d');
        }

        // 申請日の日付でmedicine_priceを取得する
        $basisMedicinePrice = $this->medicinePrice
            ->where('medicine_id', $medicine->id)
            ->where('start_date', '<=', $basis_date)
            ->where('end_date', '>=', $basis_date)
            ->first();

        $basis['basis_mediicine_price_date'] = $basis_date;
        $basis['basis_mediicine_price'] = (!empty($basisMedicinePrice)) ? $basisMedicinePrice->price * $medicine->packUnit->coefficient : 0;
        return $basis;
    }



    //registとarrowをまとめたもの
    public function registArrow(Request $request) {
        $this->start_log();

        try {
            \DB::beginTransaction();
            // price_adoptions取得
            $medicine = $this->medicine->find($request->id);
            $user     = Auth::user();
            $data     = array();

            $requestPA = (!empty($request->pa_id)) ? $this->priceAdoption->find($request->pa_id) : null;

            if (!empty($request->fm_id)){
                $sales_user_group_id = $this->facilityMedicine->find($request->fm_id)->sales_user_group_id;
            }
            elseif(!empty($request->apply_group)){
                $sales_user_group_id = $request->apply_group;
            }
            else{
                $sales_user_group_id = $user->primary_user_group_id;
            }

            /* appryGroupPAは使用してない 20220405
            if (!empty($request->apply_group)){
                $appryGroupPA        = $this->priceAdoption->getData($request->apply_group, $request->id);
            }
            */ 

            $facility_medicine = $this->facilityMedicine->getData($sales_user_group_id, $request->id);

            $insert_status = [10,13,23,33,43,53,63,73,83,93,100,110,120,130];

            //PA Update・Insert処理
            ///PAUpdate
            if (!empty($requestPA) && !in_array($requestPA->status, $insert_status) && $requestPA->status < $request->status_forward){
                logger('registArrow PA_UPDATE');
                $this->next($request, $requestPA, $facility_medicine);
                if (!empty($request->pa_purchase_requested_price)) {
                    $priceAdoption->purchase_requested_price = $this->removeComma($request->pa_purchase_requested_price); //要望仕入単価
                }
            }
            ///PAInsert
            elseif (
                (!empty($requestPA) && in_array($requestPA->status, $insert_status))   ||
                (!empty($requestPA) && $requestPA->status >= $request->status_forward) ||
                ( empty($requestPA))//&& (!empty($appryGroupPA) || !empty($primaryPA))
            ) {
                logger('registArrow PA_INSERT');
                $data['status']           = $request->status_forward;
                $data['user_id']          = $user->id;
                $data['application_date'] = date('Y-m-d H:i:s');
                $data['purchase_user_group_id'] = 0;
                $data['sales_user_group_id'] = $sales_user_group_id;
                $data['medicine_id']         = $request->id;
                $data['revert_type']         = self::REVERT_TYPE_INITIAL;

                $basis =  $this->calcBasisMediicinePrice($medicine,$request->pa_basis_mediicine_price_date);
                $data['basis_mediicine_price_date'] = $basis['basis_mediicine_price_date'];
                $data['basis_mediicine_price']      = $basis['basis_mediicine_price'];

                if (!empty($request->pa_purchase_estimate_comment)) {$data['purchase_estimate_comment'] = $request->pa_purchase_estimate_comment;}
                if (!empty($request->pa_sales_estimate_comment))    {$data['sales_estimate_comment'] = $request->pa_sales_estimate_comment;}
                if (!empty($request->pa_comment))                   {$data['comment'] = $request->pa_comment;}
                if (!empty($request->pa_priority))                  {$data['priority'] = $request->pa_priority;}
                if (!empty($request->pa_purchase_requested_price))  {$data['purchase_requested_price'] = $this->removeComma($request->pa_purchase_requested_price);}

                $priceAdoption = $this->priceAdoption->create($data);
                $request->pa_id = $priceAdoption->id;

                // facility_medicineを作成する処理
                if ($facility_medicine === null) {
                    $this->facilityMedicine->medicine_id         = $request->id;
                    $this->facilityMedicine->sales_user_group_id = $sales_user_group_id;
                    $this->facilityMedicine->save();
                } else {
                    if (!empty($request->fm_comment)) {
                        $facility_medicine->comment = $request->fm_comment;
                        $facility_medicine->save();
                    }
                }
            }
            else {
                    $request->session()->flash(
                        'errorMessage',
                        '想定外の採用申請・交渉依頼の条件、システム管理者に確認してください。'
                    );
                    \DB::rollBack();

                    return false;
            }


            $pa = $this->priceAdoption->find($request->pa_id);
            $mail_users = $this->medicine->getEmailApply(
                $pa->status,
                $pa->sales_user_group_id,
                null,
                $pa->purchase_user_group_id
            );

            $mq = new MailQueService($this->mailQue->getMailQueID());
            $mq->send_mail_add($mail_users, $pa, $request->status_forward);

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'を'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            logger('ALLOWWWWWWWWWW');
            logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 見積依頼・見積回答・受諾依頼・受諾・見積提出
    public function confirm(Request $request) {
        $this->start_log();

        if (!empty($request->pa_id)) {
            $priceAdoption = $this->priceAdoption->find($request->pa_id);
        } else {
            return false;
        }

        // nullチェック
        if ($request->isCheck == '1') {
            if ($this->checkIndexApplyKomoku($request, $priceAdoption)) {
                $request->session()->flash(
                    'errorMessage',
                    '必須項目が未入力です。詳細画面で必須項目を入力してください。'
                ); //一覧画面
                \Redirect::to(route('apply.index', array('page' => $request->page)))->send();

                return false;
            }
        }

        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $priceAdoption)) {
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route(
                    'apply.detail',
                    array(
                        'id'          => $request->m_id,
                        'pa_id'       => $request->pa_id,
                        'fm_id'       => $request->fm_id,
                        'fp_id'       => $request->fp_id,
                        'fp_honbu_id' => $request->fp_honbu_id,
                        'page'        => $request->page,
                    )
                ))->send();

                return false;
            }
        }

        //新規登録
        if (empty($priceAdoption->medicine_id)) {
            //標準薬品にJANコードが存在するかチェック
            if (!$this->existsJanCode($priceAdoption->jan_code, $request, false)) {
                return false;
            }
        }

        try {
            \DB::beginTransaction();

            if (!empty($request->pa_purchase_price)) {
                $priceAdoption->purchase_price = $this->removeComma($request->pa_purchase_price); //仕入
            }

            if (!empty($request->pa_sales_price)) {
                $priceAdoption->sales_price = $this->removeComma($request->pa_sales_price); // 売上
            }

            if (!empty($request->pa_start_date)) {
                $priceAdoption->start_date = $request->pa_start_date;
            }

            if (!empty($request->pa_end_date)) {
                $priceAdoption->end_date = $request->pa_end_date;
            }

            if (!empty($request->pa_purchase_user_group_id)) {
                $priceAdoption->purchase_user_group_id = $request->pa_purchase_user_group_id;
            }
            $priceAdoption->revert_type = self::REVERT_TYPE_INITIAL;

            // 業者用追加項目
            if (!empty($request->pa_purchase_requested_price)) {
                $priceAdoption->purchase_requested_price = $this->removeComma($request->pa_purchase_requested_price); //要望仕入単価
            }

            $user = Auth::user();
            //見積仕入単価更新したときだけ更新
            if (!empty($request->pa_purchase_estimated_price)) {
                $priceAdoption->purchase_estimated_price     = $this->removeComma($request->pa_purchase_estimated_price); //見積仕入単価
                $priceAdoption->purchase_estimate_updater    = $user->id; //見積仕入単価更新者
                $priceAdoption->purchase_estimate_updated_at = date('Y-m-d H:i:s'); //見積仕入単価更新日時
            }

            $medicine = $this->medicine->find($request->id);
            $basis =  $this->calcBasisMediicinePrice($medicine,$request->pa_basis_mediicine_price_date);
            $data['basis_mediicine_price_date'] = $basis['basis_mediicine_price_date'];
            $data['basis_mediicine_price']      = $basis['basis_mediicine_price'];

            if (!empty($request->pa_basis_mediicine_price_date)) {
                $medicine = $this->medicine->find($request->id);
                $basis =  $this->calcBasisMediicinePrice($medicine,$request->pa_basis_mediicine_price_date);
                $priceAdoption->basis_mediicine_price_date = $basis['basis_mediicine_price_date'];
                $priceAdoption->basis_mediicine_price      = $basis['basis_mediicine_price'];
            } 
            $priceAdoption->save();

            $facilityMedicine = $this->facilityMedicine->find($request->fm_id);
            $this->next($request, $priceAdoption, $facilityMedicine);

            // 送信先は本部
            $priceAdoption = $this->priceAdoption->find($request->pa_id);
            $mail_users    = $this->medicine->getEmailApply(
                $priceAdoption->status,
                $priceAdoption->sales_user_group_id,
                null,
                $priceAdoption->purchase_user_group_id
            );

            $mq = new MailQueService($this->mailQue->getMailQueID());

            $mq->send_mail_add($mail_users, $priceAdoption, $request->status_forward);

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'を'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            logger('ERRORORORKAKAKU');
            logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            echo $e->getMessage();
            // TODO exit?
            exit;

            return false;
        }

        $this->end_log();

        return true;
    }

    // 採用承認
    public function approval(Request $request) {
        $this->start_log();

        // 申請詳細取得
        $pa = $this->priceAdoption->find($request->pa_id);

        // nullチェック
        if ($request->isCheck == '1') {
            if ($this->checkIndexApplyKomoku($request, $pa)) {
                $request->session()->flash(
                    'errorMessage',
                    '必須項目が未入力です。詳細画面で必須項目を入力してください。'
                ); //一覧画面
                \Redirect::to(route('apply.index', array('page' => $request->page)))->send();

                return false;
            }
        }

        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $pa)) {
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route(
                    'apply.detail',
                    array(
                        'id'          => $request->m_id,
                        'pa_id'       => $request->pa_id,
                        'fm_id'       => $request->fm_id,
                        'fp_id'       => $request->fp_id,
                        'fp_honbu_id' => $request->fp_honbu_id,
                        'page'        => $request->page,
                    )
                ))->send();

                return false;
            }
        }

        try {
            \DB::beginTransaction();
            $fm = $this->facilityMedicine->find($request->fm_id);
            $this->next($request, $pa, $fm);

            $user = Auth::user();
            $detail = $this->getApplyDetail($request);

            // 申請施設の親施設のFM取得
            $pa_parentGroup = $this->userGroup->parent($pa->sales_user_group_id);
            if ($pa_parentGroup == null) { return false; } //親がいなければaprrovalではなく、adoptになるはず
            $parent_facility_medicine = $this->facilityMedicine->getData(
                $pa_parentGroup[0]->id,
                $pa->medicine_id
            );

            // 申請施設の親施設のFMをinsert or update
            if ($parent_facility_medicine === null) {
                $this->facilityMedicine->medicine_id   = $pa->medicine_id;
                $this->facilityMedicine->user_id       = $user->id;
                $this->facilityMedicine->adoption_date = date('Y-m-d');
                $this->facilityMedicine->sales_user_group_id = $pa_parentGroup[0]->id;
                $this->facilityMedicine->is_approval = true;
                $this->facilityMedicine->comment = !empty($request->pa_sales_estimate_comment) ? $request->pa_sales_estimate_comment : null;
                $this->facilityMedicine->save();
            }
            elseif (!$parent_facility_medicine->is_approval){
                $parent_facility_medicine->comment = !empty($request->pa_sales_estimate_comment) ? $request->pa_sales_estimate_comment : null;
                $parent_facility_medicine->is_approval = true;
                $parent_facility_medicine->adoption_date = date('Y-m-d');
                $parent_facility_medicine->user_id       = $user->id;
                $parent_facility_medicine->save();
            }
            else{
                //採用中止日チェック
                $fa_adoption_stop_date = $parent_facility_medicine->adoption_stop_date;
                if (isset($fa_adoption_stop_date) && $fa_adoption_stop_date <= date('Y-m-d')){
                    \DB::rollBack();
                    $request->session()->flash('errorMessage', '自施設の採用マスタに採用中止日が入っているので、処理できません。採用中止日を削除後、再度実行してください。');
                    return false;
                }

                //optional_keyの設定
                for ($count = 1; $count <= 8; $count++) {
                    $key_name                      = 'optional_key' . $count;
                    $parent_facility_medicine->{$key_name} = $request->{$key_name} !== null ? $request->{$key_name} : null;
                    }
                //コメント
                // $parent_facility_medicine->comment = !empty($request->fm_comment) ? $request->fm_comment : null;

                $parent_facility_medicine->save();
            }
            // PAの親施設・業者・薬品・単価開始日をkeyとするFPを取得
            $parent_facility_price = $this->facilityPrice->getDataByKey(
                $pa_parentGroup[0]->id,
                $pa->purchase_user_group_id,
                $pa->medicine_id,
                $pa->start_date
            );
            // facility_medicine_id取得用に再度facility_medicineを取得
            $parent_facility_medicine = $this->facilityMedicine->getData(
                $pa_parentGroup[0]->id,
                $pa->medicine_id
            );

            // 同一keyのfacility_pricesがなければinsert、あれば単価と終了日をupdate
            if ($parent_facility_price === null) {
                $this->facilityPrice->facility_medicine_id   = $parent_facility_medicine->id;
                $this->facilityPrice->purchase_price         = $pa->purchase_price;
                $this->facilityPrice->sales_price            = $pa->sales_price;
                $this->facilityPrice->purchase_user_group_id = $pa->purchase_user_group_id;
                $this->facilityPrice->sales_user_group_id    = $parent_facility_medicine->sales_user_group_id;
                $this->facilityPrice->medicine_id            = $pa->medicine_id;
                $this->facilityPrice->start_date             = (!empty($pa->start_date)) ? $pa->start_date : date('Y-m-d');
                $this->facilityPrice->end_date               = (!empty($pa->end_date)) ? $pa->end_date : date('2100-12-31');
                $this->facilityPrice->save();
            } else {
                $parent_facility_price->purchase_price = $pa->purchase_price;
                $parent_facility_price->sales_price= $pa->sales_price;
                if (!empty($pa->end_date)){
                    $parent_facility_price->end_date= $pa->end_date;
                }
                $parent_facility_price->save();
            }

            $mail_users = $this->medicine->getEmailApply($pa->status, $pa->sales_user_group_id, null, $pa->purchase_user_group_id);
            // $toUser     = $this->user->where('id', $pa->user_id)->first();

            $mq = new MailQueService($this->mailQue->getMailQueID());

            // foreach ($mail_users as $mail_user) {
            //     if ($mail_user->email == $toUser->email) {
            //         $is_target = 1;
            //     } else {
            //         $is_target = 0;
            //     }
            //     $mq->send_mail_add($mail_user->email, $pa, $is_target);
            // }
            $mq->send_mail_add($mail_users, $pa, $request->status_forward);

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'を'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            logger('SYOUNINERROR');
            logger($e->getMessage());
            echo $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 採用
    public function adopt(Request $request) {
        $this->start_log();

        $pa = $this->priceAdoption->find($request->pa_id);

        // nullチェック
        if ($request->isCheck == '1') {
            if ($this->checkIndexApplyKomoku($request, $pa)) {
                $request->session()->flash(
                    'errorMessage',
                    '必須項目が未入力です。詳細画面で必須項目を入力してください。'
                ); //一覧画面
                \Redirect::to(route('apply.index', array('page' => $request->page)))->send();

                return false;
            }
        }

        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $pa)) {
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route(
                    'apply.detail',
                    array(
                        'id'          => $request->m_id,
                        'pa_id'       => $request->pa_id,
                        'fm_id'       => $request->fm_id,
                        'fp_id'       => $request->fp_id,
                        'fp_honbu_id' => $request->fp_honbu_id,
                        'page'        => $request->page,
                    )
                ))->send();

                return false;
            }
        }

        try {
            \DB::beginTransaction();
            $fa = $this->facilityMedicine->getData($pa->sales_user_group_id, $pa->medicine_id);

            $this->next($request, $pa, $fa);

            $user = Auth::user();


            //採用中止日チェック
            $fa_adoption_stop_date = $fa->adoption_stop_date;
            logger($fa_adoption_stop_date);
            if (isset($fa_adoption_stop_date) && $fa_adoption_stop_date <= date('Y-m-d')){
                \DB::rollBack();
                $request->session()->flash('errorMessage', '自施設の採用マスタに採用中止日が入っているので、処理できません。採用中止日を削除後、再度実行してください。');
                return false;
            }

            // $faは絶対に取得できるはずで、もし取得出来なかったとしたら、後続でキャッチされるべき
            $fa->user_id             = $user->id;
            $fa->adoption_date       = date('Y-m-d');
            $fa->price_adoption_id   = $pa->id;

            // optional_keyの設定
            for ($count = 1; $count <= 8; $count++) {
                $key_name = 'optional_key' . $count;

                if (!empty($request->{$key_name})) {
                    $fa->{$key_name} = $request->{$key_name};
                }
            }

            if ($fa->is_approval){
                if (!empty($request->fm_comment)) {
                    $fa->comment = $request->fm_comment;
                }
            }
            else {
                $fa->comment = $request->pa_sales_estimate_comment !== null ? $request->pa_sales_estimate_comment : null;
                $fa->is_approval = true;
            }

            $fa->save();

            // 施設・業者・薬品・単価開始日をkeyとする既存のFPを取得
            $fp_own = $this->facilityPrice->getDataByKey(
                $pa->sales_user_group_id,
                $pa->purchase_user_group_id,
                $pa->medicine_id,
                $pa->start_date
            );

            // $fp_ownがなければinsert、あれば単価と終了日をupdate
            if ($fp_own === null) {
                $this->facilityPrice->facility_medicine_id   = $fa->id;
                $this->facilityPrice->purchase_price         = (!empty($request->pa_purchase_price)) ? $this->removeComma($request->pa_purchase_price) : $pa->purchase_price;//$pa->purchase_price;
                $this->facilityPrice->sales_price            = (!empty($request->pa_sales_price)) ? $this->removeComma($request->pa_sales_price) : $pa->sales_price;//$pa->sales_price;
                $this->facilityPrice->purchase_user_group_id = $pa->purchase_user_group_id;
                $this->facilityPrice->sales_user_group_id    = $pa->sales_user_group_id;
                $this->facilityPrice->medicine_id            = $pa->medicine_id;
                $this->facilityPrice->start_date             = (!empty($pa->start_date)) ? $pa->start_date : date('Y-m-d');
                $this->facilityPrice->end_date               = (!empty($pa->end_date)) ? $pa->end_date : date('2100-12-31');
                $this->facilityPrice->save();
            } else {
                $fp_own->purchase_price = (!empty($request->pa_purchase_price)) ? $this->removeComma($request->pa_purchase_price) : $pa->purchase_price;//$pa->purchase_price;
                $fp_own->sales_price    = (!empty($request->pa_sales_price)) ? $this->removeComma($request->pa_sales_price) : $pa->sales_price;//$pa->sales_price;
                if (!empty($pa->end_date)){
                    $fp_own->end_date= $pa->end_date;
                }
                $fp_own->save();
            }
            
            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'を'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            logger('SAIYOUERRRRRR');
            logger($e->getMessage());
            echo $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 採用可
    public function adopt2(Request $request) {
        $this->start_log();

        try {
            \DB::beginTransaction();
            // 本部FPと自身のFMを取得
            $user      = Auth::user();
            $fp_parent = $this->facilityPrice->where('id', $request->fp_honbu_id)->first();
            $fm_own = $this->facilityMedicine->getData($user->primary_user_group_id, $request->id);

            // $fm_ownがなければinsert、あればupdate
            if ($fm_own === null) {
                $this->facilityMedicine->medicine_id         = $request->id;
                $this->facilityMedicine->user_id             = $user->id;
                $this->facilityMedicine->adoption_date       = date('Y-m-d');
                $this->facilityMedicine->sales_user_group_id = $user->primary_user_group_id;
                $this->facilityMedicine->is_approval         = true;
                $this->facilityMedicine->save();
            } else {
                //採用中止日チェック
                $fa_adoption_stop_date = $fm_own->adoption_stop_date;
                if (isset($fa_adoption_stop_date) && $fa_adoption_stop_date <= date('Y-m-d')){
                    logger('SAIYOUKAAAAAAAA_SAIYOUCHUSHI');
                    \DB::rollBack();
                    $request->session()->flash('errorMessage', '自施設の採用マスタに採用中止日が入っているので、処理できません。採用中止日を削除後、再度実行してください。');
                    return false;
                }

                $fm_own->user_id             = $user->id;
                $fm_own->adoption_date       = date('Y-m-d');
                $fm_own->is_approval = true;

                // optional_keyの設定
                for ($count = 1; $count <= 8; $count++) {
                    $key_name = 'optional_key' . $count;

                    if (!empty($request->{$key_name})) {
                        $fm_own->{$key_name} = $request->{$key_name};
                    }
                }

                if (!empty($request->fm_comment)) {
                    $fm_own->comment = $request->fm_comment;
                }

                $fm_own->save();
            }

            //納入価　採用可の場合はFPは常にinsertになる　updateは考慮しなくてよい
            $this->facilityPrice->facility_medicine_id   = ($fm_own === null) ? $this->facilityMedicine->id : $fm_own->id;
            $this->facilityPrice->purchase_price         = $fp_parent->purchase_price;
            $this->facilityPrice->sales_price            = $fp_parent->sales_price;
            $this->facilityPrice->start_date             = $fp_parent->start_date;
            $this->facilityPrice->end_date               = $fp_parent->end_date;
            $this->facilityPrice->purchase_user_group_id = $fp_parent->purchase_user_group_id;
            $this->facilityPrice->sales_user_group_id    = $user->primary_user_group_id;
            $this->facilityPrice->medicine_id            = $fp_parent->medicine_id;
            $this->facilityPrice->save();

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'を'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            logger('SAIYOUKAAAAAAAA');
            logger($e->getMessage());
            echo $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // next　ステータスを進める際にステータス、3つのコメント、優先度を更新する
    public function next(Request $request, $priceAdoption, $facilityMedicine) {
        $this->start_log();

        try {

            $user = Auth::user();

            //現行ｽﾃｰﾀｽ：80以下 かつ 更新後のｽﾃｰﾀｽ：90 or 100　であれば、承認ユーザーを設定する
            if ($priceAdoption->status <= 80 && ($request->status_forward == 90 || $request->status_forward == 100)) {
                $priceAdoption->approval_user_id = $user->id;
            }
            $priceAdoption->status      = $request->status_forward;
            $priceAdoption->revert_type = self::REVERT_TYPE_INITIAL;

            // コメントの保存
            $this->setRequestComment($request, $priceAdoption, $facilityMedicine);

            if (!empty($request->pa_priority)) {
                $priceAdoption->priority = $request->pa_priority;
            }

            $priceAdoption->updater = $user->id;

            $priceAdoption->save();
            $facilityMedicine->save();
        } catch (\PDOException $e) {
            throw $e;
        }

        $this->end_log();

        return true;
    }

    // prev 取り下げ
    public function prev(Request $request) {
        $this->start_log();

        $pa = $this->priceAdoption->find($request->pa_id);

        // nullチェック
        if ($request->isCheck == '1') {
            if ($this->checkIndexApplyKomoku($request, $pa)) {
                $request->session()->flash('errorMessage', '必須項目が未入力です。詳細画面で必須項目を入力してください。'); //一覧画面
                \Redirect::to(route('apply.index', array('page' => $request->page)))->send();

                return false;
            }
        }

        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $pa)) {
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route('apply.detail', array('id' => $request->m_id, 'pa_id' => $request->pa_id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'fp_honbu_id' =>$request->fp_honbu_id, 'page' => $request->page)))->send();

                return false;
            }
        }

        \DB::beginTransaction();

        try {
            // facirity_medicine取得
            $fm = $this->facilityMedicine->find($request->fm_id);
            $user = Auth::user();
            $status = $request->status_withdraw;
            //$pa->approval_user_id = $user->id; // 承認時のみ更新するように変更
            $pa->status      = $status; // 現在のステータスをセット
            $pa->revert_type = self::REVERT_TYPE_WITHDRAW; // 取り下げ

            // 編集済みコメントの反映
            $this->setRequestComment($request, $pa, $fm);

            $pa->save();
            $fm->save();

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'について'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 差し戻し処理
    public function remand(Request $request) {
        $this->start_log();
        // price_adoptions取得
        $pa = $this->priceAdoption->find($request->pa_id);

        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $pa)) {
                //exit;
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route('apply.detail', array('id' => $request->m_id, 'pa_id' => $request->pa_id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'fp_honbu_id' => $request->fp_honbu_id, 'page' => $request->page)))->send();

                return false;
            }
        }

        \DB::beginTransaction();

        try {
            // facirity_medicine取得
            $fm = $this->facilityMedicine->find($request->fm_id);

            //メール送信To対象者を取得
            $mail_target_id = $pa->approval_user_id;

            $user = Auth::user();
            $pa->status      = $request->status_remand; // 現在のステータスをセット
            $pa->revert_type = self::REVERT_TYPE_REMAND; // 差し戻し
            $this->setRequestComment($request, $pa, $fm); // 編集済みコメントの反映

            $mail_users = $this->medicine->getEmailApply($request->status_remand, $pa->sales_user_group_id, $user->primary_user_group_id, $pa->purchase_user_group_id);
            $mq         = new MailQueService($this->mailQue->getMailQueID());
            $mq->send_mail_add($mail_users, $pa, $request->status_remand, 1, true);

            $pa->save();
            $fm->save();

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'について'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 見送り処理
    public function reject(Request $request) {
        $this->start_log();
        // price_adoptions取得
        $pa = $this->priceAdoption->find($request->pa_id);

        // nullチェック
        if ($request->isCheck == '2') {
            if ($this->checkDetailApplyKomoku($request, $pa)) {
                //exit;
                $request->session()->flash('errorMessage', '必須項目を入力してください'); //詳細画面
                \Redirect::to(route('apply.detail', array('id' => $request->m_id, 'pa_id' => $request->pa_id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'fp_honbu_id' => $request->fp_honbu_id, 'page' => $request->page)))->send();

                return false;
            }
            // exit;
        }

        \DB::beginTransaction();

        try {
            // facirity_medicine取得
            $fm = $this->facilityMedicine->find($request->fm_id);

            //メール送信To対象者を取得
            $mail_target_id = $pa->approval_user_id;

            $user = Auth::user();
            //$kengen = $this->medicine->getPrivilegesApply($user->id,$pa->sales_user_group_id,$pa->status);

            $mail_users = $this->medicine->getEmailApply($request->status_reject, $pa->sales_user_group_id, $user->primary_user_group_id, $pa->purchase_user_group_id);
            $mq         = new MailQueService($this->mailQue->getMailQueID());

            //$pa->approval_user_id = $user->id; // 承認時のみ更新するように変更
            $pa->status      = $request->status_reject; // 現在のステータスをセット
            $pa->revert_type = self::REVERT_TYPE_REJECT; // 見送り

            $mq->send_mail_add($mail_users, $pa, $request->status_reject);

            // 編集済みコメントの反映
            $this->setRequestComment($request, $pa, $fm);

            $pa->save();
            $fm->save();

            \DB::commit();
            $request->session()->flash('message', $request->medicine_name.'について'.$request->button_control . 'しました');

        } catch (\PDOException $e) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 更新
    public function edit(Request $request) {
        $this->start_log();
        \DB::beginTransaction();

        try {
            $priceAdoption    = (empty($request->pa_id)) ? null : $this->priceAdoption->find($request->pa_id);
            $fp               = (empty($request->fp_id)) ? null : $this->facilityPrice->find($request->fp_id);
            $facilityMedicine = (empty($request->fm_id)) ? null : $this->facilityMedicine->find($request->fm_id);
            $user             = Auth::user();

            $detail = $this->getApplyDetail($request);

            //facilityPriceの更新
            if (!empty($fp) && $detail->edit_facility_price_kengen == 1) {
                //仕入単価
                if (!empty($request->fp_purchase_price)) {
                    $fp->purchase_price = $this->removeComma($request->fp_purchase_price);
                }
                //納入単価
                if (!empty($request->fp_sales_price)) {
                    $fp->sales_price = $this->removeComma($request->fp_sales_price);
                }
                //業者
                if (!empty($request->fp_purchase_user_group_id)) {
                    $fp->purchase_user_group_id = $request->fp_purchase_user_group_id;
                }
                //更新ユーザー
                $fp->updater = $user->id;

                $fp->save();
            }

            //facilityMedicineの更新
            if (!empty($facilityMedicine) && $facilityMedicine->is_approval) {
                //optional_keyの設定
                if ($detail->edit_fm_optional_key == 1) {
                    for ($count = 1; $count <= 8; $count++) {
                        $key_name                      = 'optional_key' . $count;
                        $facilityMedicine->{$key_name} = $request->{$key_name} !== null ? $request->{$key_name} : null;
                    }
                }
                //採用中止日
                if ($detail->edit_fm_adoption_stop_date == 1 && !empty($request->fm_adoption_stop_date)) {
                    $req_adoption_stop_date = preg_replace('/(^\s+)|(\s+$)/u', '', $request->fm_adoption_stop_date);

                    if (!empty($req_adoption_stop_date)) {
                        $facilityMedicine->adoption_stop_date = $request->fm_adoption_stop_date;
                    } else {
                        $facilityMedicine->adoption_stop_date = null;
                    }
                } 
                elseif($detail->edit_fm_adoption_stop_date == 1 && empty($request->fm_adoption_stop_date)) { 
                    $facilityMedicine->adoption_stop_date = null;
                }
                //コメント
                if ($detail->edit_fm_comment == 1) {
                    $facilityMedicine->comment = $request->fm_comment !== null ? $request->fm_comment : null;
                }
                //更新ユーザー
                $facilityMedicine->updater = $user->id;

                $facilityMedicine->save();
            }

            //priceAdoptionの更新
            if (!empty($priceAdoption)) {
                // 仕入価
                if ($detail->edit_pa_purchase_price == 1) {
                    $priceAdoption->purchase_price = $request->pa_purchase_price !== null ? $this->removeComma($request->pa_purchase_price) : null;
                }
                // 納入価
                if ($detail->edit_pa_sales_price == 1) {
                    $priceAdoption->sales_price = $request->pa_sales_price !== null ? $this->removeComma($request->pa_sales_price) : null;
                }
                // 業者
                if ($detail->edit_pa_purchase_user_group_id == 1) {
                    $priceAdoption->purchase_user_group_id = $request->pa_purchase_user_group_id !== null ? $request->pa_purchase_user_group_id : 0;
                }
                // 有効開始日
                if ($detail->edit_price_adoption_period == 1 && !empty($request->pa_start_date)) {
                    $req_start_date = preg_replace('/(^\s+)|(\s+$)/u', '', $request->pa_start_date);

                    if (!empty($req_start_date)) {
                        $priceAdoption->start_date = $request->pa_start_date;
                    } else {
                        $priceAdoption->start_date = null;
                    }
                } 
                // else {
                //     $priceAdoption->start_date = null;
                // }

                // // 有効終了日
                // if ($detail->edit_price_adoption_period == 1) {
                //     $priceAdoption->end_date = $request->pa_end_date !== null ? $request->pa_end_date : null;
                // }
                // 要望仕入
                if ($detail->edit_pa_purchase_requested_price == 1) {
                    $priceAdoption->purchase_requested_price = $request->pa_purchase_requested_price !== null ? $this->removeComma($request->pa_purchase_requested_price) : null;
                }
                // 見積仕入
                if ($detail->edit_pa_purchase_estimated_price == 1) {
                    $priceAdoption->purchase_estimated_price = $request->pa_purchase_estimated_price !== null ? $this->removeComma($request->pa_purchase_estimated_price) : null;
                }
                // 申請備考(業者－文化連)
                if ($detail->edit_pa_purchase_estimate_comment == 1) {
                    $priceAdoption->purchase_estimate_comment = $request->pa_purchase_estimate_comment !== null ? $request->pa_purchase_estimate_comment : null;
                }
                // 申請備考(厚生連－文化連)
                if ($detail->edit_pa_sales_estimate_comment == 1) {
                    $priceAdoption->sales_estimate_comment = $request->pa_sales_estimate_comment !== null ? $request->pa_sales_estimate_comment : null;
                }
                // 申請備考(文化連)
                if ($detail->edit_pa_comment == 1) {
                    $priceAdoption->comment = $request->pa_comment !== null ? $request->pa_comment : null;
                }
                // 優先度
                if ($detail->edit_priority == 1 && $detail->status_forward >0) {
                    $priceAdoption->priority = $request->pa_priority !== null ? $request->pa_priority : null;
                }


                if ($detail->edit_pa_basis_medicine_price == 1 && !empty($request->pa_basis_mediicine_price_date)) {
                    $medicine = $this->medicine->find($request->id);
                    $basis =  $this->calcBasisMediicinePrice($medicine,$request->pa_basis_mediicine_price_date);
                    $priceAdoption->basis_mediicine_price_date = $basis['basis_mediicine_price_date'];
                    $priceAdoption->basis_mediicine_price      = $basis['basis_mediicine_price'];
                } 



                //更新ユーザー
                $priceAdoption->updater = $user->id;
                $priceAdoption->save();
            }

            \DB::commit();
            $request->session()->flash('message', '更新しました');
        } catch (\PDOException $e) {
            logger('EDITERROR');
            logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 薬品を登録して申請
    public function add(Request $request, $user) {
        $this->start_log();

        $jan_code = $this->getJanCheckDegit($request->sales_packaging_code);

        if (!$this->existsJanCode($jan_code, $request)) {
            return false;
        }
        \DB::beginTransaction();

        try {
            $this->priceAdoption->maker_name = mb_convert_kana($request->maker_name, 'KVAS');
            $this->priceAdoption->pack_unit_price = (empty($request->pack_unit_price)) ? 0 : $request->pack_unit_price;
            $this->priceAdoption->sales_estimate_comment = $request->comment;
            $this->priceAdoption->coefficient = 1;
            $this->priceAdoption->user_id          = $user->id;
            $this->priceAdoption->application_date = date('Y-m-d H:i:s');
            $this->priceAdoption->purchase_user_group_id = 0;

            if (empty($request->apply_group)) {
                $this->priceAdoption->sales_user_group_id = $user->primary_user_group_id;
            } else {
                $this->priceAdoption->sales_user_group_id = $request->apply_group;
            }

            // 新規登録だけは対象の権限を取得する
            $kengen_rec                  = $this->medicine->getPrivilegesApply($user->id, $this->priceAdoption->sales_user_group_id, Task::STATUS_UNAPPLIED, $this->priceAdoption->purchase_user_group_id);
            $this->priceAdoption->status = $kengen_rec[0]->status_forward;

            $this->priceAdoption->save();

            //標準薬品の作成
            $this->medicine->sales_packaging_code =  $request->sales_packaging_code;
            $this->medicine->search               = mb_convert_kana($request->sales_packaging_code, 'KVAS') . mb_convert_kana($jan_code, 'KVAS') . mb_convert_kana($request->name, 'KVAS') . $this->priceAdoption->maker_name . mb_convert_kana($request->standard_unit, 'KVAS');
            $this->medicine->name                 = mb_convert_kana($request->name, 'KVAS');
            $this->medicine->standard_unit        = mb_convert_kana($request->standard_unit, 'KVAS');
            $this->medicine->unit                 = '';
            // $this->medicine->owner_classification =  $this->priceAdoption->owner_classification;
            $this->medicine->discontinuation_date = date('2999-12-31');
            $this->medicine->creater              = $user->id;
            $this->medicine->updater              = $user->id;
            $this->medicine->created_at           = date('Y-m-d H:i:s');
            $this->medicine->updated_at           = date('Y-m-d H:i:s');
            $this->medicine->save();

            //包装単位
            $this->packUnit->medicine_id = $this->medicine->id;
            $this->packUnit->jan_code    =  $jan_code;
            $this->packUnit->price       =  $this->priceAdoption->pack_unit_price;
            $this->packUnit->coefficient =  $this->priceAdoption->coefficient;
            $this->packUnit->creater     = $user->id;
            $this->packUnit->updater     = $user->id;
            $this->packUnit->created_at  = date('Y-m-d H:i:s');
            $this->packUnit->updated_at  = date('Y-m-d H:i:s');
            $this->packUnit->save();

            //薬価
            $this->medicinePrice->medicine_id = $this->medicine->id;
            $this->medicinePrice->price      =  (empty($request->pack_unit_price)) ? 0 : $request->pack_unit_price;
            $this->medicinePrice->start_date = date('Y-m-d');
            $this->medicinePrice->end_date   = date('2100-12-31');
            $this->medicinePrice->creater    = $user->id;
            $this->medicinePrice->updater    = $user->id;
            $this->medicinePrice->created_at = date('Y-m-d H:i:s');
            $this->medicinePrice->updated_at = date('Y-m-d H:i:s');
            $this->medicinePrice->save();

            $this->priceAdoption->medicine_id = $this->medicine->id;
            // TODO 基準薬価 初回の為、基準日は現在日付
            $basis_date                                      =  date('Y-m-d');
            $this->priceAdoption->basis_mediicine_price_date = $basis_date;
            // 申請日の日付でmedicine_priceを取得する
            $basisMedicinePrice = $this->medicinePrice->where('medicine_id', $this->medicine->id)->where('start_date', '<=', $basis_date)->where('end_date', '>=', $basis_date)->first();

            if (!empty($basisMedicinePrice)) {
                $medicine_price = $basisMedicinePrice->price * $this->medicine->packUnit->coefficient;
            } else {
                $medicine_price = 0;
            }
            $this->priceAdoption->basis_mediicine_price = $medicine_price;
            $this->priceAdoption->save();

            $this->facilityMedicine->medicine_id = $this->medicine->id;
            $this->facilityMedicine->sales_user_group_id = $this->priceAdoption->sales_user_group_id;
            $this->facilityMedicine->save();

            $mail_users = $this->medicine->getEmailApply($this->priceAdoption->status, $this->priceAdoption->sales_user_group_id, null, $this->priceAdoption->purchase_user_group_id);
            $mq         = new MailQueService($this->mailQue->getMailQueID());

            $mq->send_mail_add($mail_users, $this->priceAdoption, $kengen_rec[0]->status_forward);

            \DB::commit();
            $request->session()->flash('message', $request->name .'を登録して申請しました');

        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // 薬品登録再申請
    public function reentry(Request $request, $user) {
        $this->start_log();

        \DB::beginTransaction();

        try {
            // price_adoptions取得
            $pa = $this->priceAdoption->find($request->pa_id);
            // facirity_medicine取得
            $fm = $this->facilityMedicine->find($request->fm_id);

            if (!$this->existsJanCode($pa->jan_code, $request)) {
                return false;
            }

            $pa->status           = Task::STATUS_APPLYING;
            $pa->user_id          = $user->id;
            $pa->application_date = date('Y-m-d H:i:s');
            $pa->revert_type      = self::REVERT_TYPE_INITIAL;

            $pa->save();

            $mail_users = $this->medicine->getEmailApply($pa->status, $pa->sales_user_group_id);
            $mq         = new MailQueService($this->mailQue->getMailQueID());

            $mq->send_mail_add($mail_users, $pa, Task::STATUS_APPLYING);

            \DB::commit();
            $request->session()->flash('message', '申請しました');
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();

            return false;
        }

        $this->end_log();

        return true;
    }

    // ステータス一覧　簡易検索用
    public function getTasksMin() {
        return Task::STATUS_STR_MIN;
    }

    // ステータス一覧　詳細な検索条件用
    public function getTasks() {
        return Task::STATUS_STR;
    }

    // 登録・詳細の施設一覧
    public function getConditions(Request $request) {
        $this->start_log();

        // 検索条件
        $conditionSettings = array(
            new ApplyConditionSetting('search', '全文検索', ''),
            // new ApplyConditionSetting('my_charge', '私が担当', 0, 1),
            // new ApplyConditionSetting('my_apply', '私が申請', 0, 1),
            // new ApplyConditionSetting('is_applying', '申請進行中(交渉依頼待ち～採用待ち)', 0, 1),
            new ApplyConditionSetting('status', '状態', array()),
            // new ApplyConditionSetting('need_request', '要受諾依頼', array()),
            // new ApplyConditionSetting('need_estimate', '要見積提出', array()),
            // new ApplyConditionSetting('status_min', '状態_初期表示', array()),
            new ApplyConditionSetting('simple_search', '簡易検索', ''),
            new ApplyConditionSetting('user_group', '施設', array()),
            new ApplyConditionSetting('code', '医薬品CD', ''),
            new ApplyConditionSetting('popular_name', '一般名', ''),
            new ApplyConditionSetting('coefficient', '包装薬価係数', ''),
            new ApplyConditionSetting('discontinuation_date', '販売中止日', 0, 1),
            new ApplyConditionSetting('dosage_type_division', '剤型区分', ''),
            new ApplyConditionSetting('generic_product_detail_devision', '先発後発品区分', null),
            // new ApplyConditionSetting('stop_date', '採用中止日', 0, 1),
            new ApplyConditionSetting('priority', '優先度', ''),
            new ApplyConditionSetting('pa_application_date_from', '申請日from', ''),
            new ApplyConditionSetting('pa_application_date_to', '申請日to', ''),
            new ApplyConditionSetting('mode_radios', 'モード', ''),
            new ApplyConditionSetting('optional_key1', 'オプション項目1', ''),
            new ApplyConditionSetting('optional_key2', 'オプション項目2', ''),
            new ApplyConditionSetting('optional_key3', 'オプション項目3', ''),
            new ApplyConditionSetting('optional_key4', 'オプション項目4', ''),
            new ApplyConditionSetting('optional_key5', 'オプション項目5', ''),
            new ApplyConditionSetting('optional_key6', 'オプション項目6', ''),
            new ApplyConditionSetting('optional_key7', 'オプション項目7', ''),
            new ApplyConditionSetting('optional_key8', 'オプション項目8', ''),
            new ApplyConditionSetting('hq_optional_key1', '病院オプション項目1', ''),
            new ApplyConditionSetting('hq_optional_key2', '病院オプション項目2', ''),
            new ApplyConditionSetting('hq_optional_key3', '病院オプション項目3', ''),
            new ApplyConditionSetting('hq_optional_key4', '病院オプション項目4', ''),
            new ApplyConditionSetting('hq_optional_key5', '病院オプション項目5', ''),
            new ApplyConditionSetting('hq_optional_key6', '病院オプション項目6', ''),
            new ApplyConditionSetting('hq_optional_key7', '病院オプション項目7', ''),
            new ApplyConditionSetting('hq_optional_key8', '病院オプション項目8', ''),
        );

        if (!empty($request->is_search)) {
            // 戻り値
            $conditions = array();

            // 検索実行の場合
            logger('検索実行の場合');
            foreach ($conditionSettings as $conditionSetting) {
                $conditionSetting->setConditions($conditions, $request);
            }
            $conditions['is_condition_search'] = ($request->is_condition_search == 'true' || $request->is_condition_search == '1')? true : false;
            $conditions['is_condition_hospital'] = ($request->is_condition_hospital == 'true' || $request->is_condition_hospital == '1')? true : false;

            session()->put(array('apply_conditions' => $conditions));
            $this->end_log();

            return $conditions;
        }

        if (!empty($request->initial)) {
            // 戻り値
            $conditions = array();

            logger('初期表示の場合');

            foreach ($conditionSettings as $conditionSetting) {
                $conditionSetting->setConditionsDefault($conditions);
            }
            // $request->my_charge            = $conditions['my_charge'];
            // $request->my_apply             = $conditions['my_apply'];
            // $request->simple_search        = $conditions['simple_search'];
            $request->discontinuation_date = $conditions['discontinuation_date'];
            // $request->stop_date            = $conditions['stop_date'];
            // $request->fp_date            = $conditions['fp_date'];
            // $request->mp_date            = $conditions['mp_date'];
            $conditions['is_condition_search'] = false;
            $conditions['is_condition_hospital'] = false;
            $conditions['mode_radios'] = '1';
            session()->put(array('apply_conditions' => $conditions));

            $this->end_log();

            return $conditions;
        }

        logger('初期表示でも検索実行でもない場合');

        $conditions = session()->get('apply_conditions');
//         var_dump("\n");
//         var_dump($conditions);

        if (!empty(session()->get('apply_conditions'))) {
            $conditions = session()->get('apply_conditions');

            //リクエストに値を設定する。
            // // 私が担当
            // if (!empty($conditions['my_charge'])) {
            //     $request->my_charge = $conditions['my_charge'];
            // }
            // // 私が申請
            // if (!empty($conditions['my_apply'])) {
            //     $request->my_apply = $conditions['my_apply'];
            // }
            // // 申請進行中(交渉依頼待ち～採用待ち)
            // if (!empty($conditions['is_applying'])) {
            //     $request->is_applying = $conditions['is_applying'];
            // }
            // // デフォルト表示のステータス
            // if (!empty($conditions['status_min'])) {
            //     $request->status_min = $conditions['status_min'];
            // } else {
            //     $request->status_min = array();
            // }
            // // 要受諾依頼
            // if (!empty($conditions['need_estimate'])) {
            //     $request->status_min = $conditions['need_estimate'];
            // } else {
            //     $request->status_min = array();
            // }
            // // 要見積提出
            // if (!empty($conditions['need_request'])) {
            //     $request->status_min = $conditions['need_request'];
            // } else {
            //     $request->status_min = array();
            // }
            // 簡易検索
            if (!empty($conditions['simple_search'])) {
                $request->simple_search = $conditions['simple_search'];
            }
            // 詳細なステータス
            if (!empty($conditions['status'])) {
                $request->status = $conditions['status'];
            } else {
                $request->status = array();
            }
            // 施設
            if (!empty($conditions['user_group'])) {
                $request->user_group = $conditions['user_group'];
            } else {
                $request->user_group = array();
            }
            // 医薬品CD
            if (!empty($conditions['code'])) {
                $request->code = $conditions['code'];
            }
            // 一般名
            if (!empty($conditions['popular_name'])) {
                $request->popular_name = $conditions['popular_name'];
            }
            // 包装薬価係数
            if (!empty($conditions['coefficient'])) {
                $request->coefficient = $conditions['coefficient'];
            }
            // 販売中止日
            if (!empty($conditions['discontinuation_date'])) {
                $request->discontinuation_date = $conditions['discontinuation_date'];
            }
            // 剤型区分
            if (!empty($conditions['dosage_type_division'])) {
                $request->dosage_type_division = $conditions['dosage_type_division'];
            }

            // 先発後発品区分
            if ($conditions['generic_product_detail_devision'] !== null && $conditions['generic_product_detail_devision'] != '') {
                $request->generic_product_detail_devision = $conditions['generic_product_detail_devision'];
            }
            // 採用中止日
            // if (!empty($conditions['stop_date'])) {
            //     $request->stop_date = $conditions['stop_date'];
            // }

            // 優先度
            if (!empty($conditions['priority'])) {
                $request->priority = $conditions['priority'];
            }

            // 申請日from
            if (!empty($conditions['pa_application_date_from'])) {
                $request->pa_application_date_from = $conditions['pa_application_date_from'];
            }

            // 申請日to
            if (!empty($conditions['pa_application_date_to'])) {
                $request->pa_application_date_to = $conditions['pa_application_date_to'];
            }

            // モード
            if (!empty($conditions['mode_radios'])) {
                $request->mode_radios = $conditions['mode_radios'];
            }

            // 全文検索
            if (!empty($conditions['search'])) {
                $request->search = $conditions['search'];
            }

            // optional_keyの設定
            for ($count = 1; $count <= 8; $count++) {
                $key_name = 'optional_key' . $count;

                if (!empty($conditions[$key_name])) {
                    $request->{$key_name} = $conditions[$key_name];
                }
            }

            // hq_optional_keyの設定
            for ($count = 1; $count <= 8; $count++) {
                $key_name = 'hq_optional_key' . $count;

                if (!empty($conditions[$key_name])) {
                    $request->{$key_name} = $conditions[$key_name];
                }
            }

            // もっと検索条件を見る
            if (!empty($conditions['is_condition_search'])) {
                $request->is_condition_search = $conditions['is_condition_search'];
            }
            if (!empty($conditions['is_condition_hospital'])) {
                $request->is_condition_hospital = $conditions['is_condition_hospital'];
            }

        }

        $this->end_log();

        return $conditions;
    }

    // 登録・詳細の施設一覧
    public function isInitial(Request $request) {
        $this->start_log();

        $status = array();
        /*
        if (isBunkaren()) {
            $status = array(
                Task::STATUS_NEGOTIATING,
                Task::STATUS_APPROVAL_WAITING
            );
        }elseif (isHeadQuqrters()) {
            $status = array(
                Task::STATUS_APPLYING,
                Task::STATUS_NEGOTIATING,
                Task::STATUS_APPROVAL_WAITING
            );
        }elseif (isHospital()) {
            $status = array(
                Task::STATUS_APPLYING,
                Task::STATUS_NEGOTIATING,
                Task::STATUS_APPROVAL_WAITING,
                Task::STATUS_UNCONFIRMED
            );
        }
        */

        $this->end_log();

        return $status;
    }

    // 新規登録申請が可能か
    public function getRegistPrivileges($user_id, $user_group_id, $status, $purchase_user_group_id) {
        $this->start_log();

        $rec = $this->medicine->getPrivilegesApply($user_id, $user_group_id, $status, $purchase_user_group_id);

        $this->end_log();

        return $rec[0]->status_forward >= 0;
    }

    // 採用申請一覧取得
    public function getApplayListForCSV(Request $request, $user) {
        $this->start_log();

        $list =  $this->medicine->listWithApplyIndex($request, -1, $user);

        $csv_data = array();
        $csv_row  = array();
        $option   = $this->getOptionalSearch($user->primary_user_group_id);

        $kengen          = $user->getUserKengen($user->id);
        $purchase_kengen = $kengen['医薬品採用申請_仕入価枠表示']->privilege_value == 'true';
        $sales_kengen    = $kengen['医薬品採用申請_納入価枠表示']->privilege_value == 'true';
        $bunkaren_waku_kengen = $kengen["医薬品採用申請_文化連枠表示"]->privilege_value == "true";

        try {
            foreach ($list as $key => $apply) {
                // $csv_row['facility_name']                   = $apply->facility_name; //施設名
                $csv_row['maker_name']                      = $apply->hanbai_maker; // 販売メーカー名
                $csv_row['production_maker_name']           = $apply->seizo_maker; // 製造メーカー名
                $csv_row['medicine_name']                   = $apply->medicine_name; // 商品名
                $csv_row['popular_name']                    = $apply->popular_name; // 一般名
                $csv_row['standard_unit']                   = $apply->standard_unit; // 規格容量
                $csv_row['sales_packaging_code']            = $apply->sales_packaging_code; // GS1販売
                $csv_row['dispensing_packaging_code']       = $apply->dispensing_packaging_code; // GS1調剤
                $csv_row['hot_code']                        = $apply->hot_code; // HOT番号
                $csv_row['jan_code']                        = $apply->jan_code; // JANコード
                $csv_row['code']                            = $apply->code; // 医薬品CD
                $csv_row['medicine_code']                   = $apply->medicine_code; // 医薬品CD
                $csv_row['generic_product_detail_devision'] = \App\Helpers\Apply\getGenericProductDetailDivision($apply->generic_product_detail_devision); //先後発区分
                $csv_row['dosage_type_division']            = \App\Helpers\Apply\getDosageTypeDivisionText($apply->dosage_type_division); // 剤型
                $csv_row['coefficient']                     = $apply->coefficient; //包装薬価係数
                $csv_row['medicine_price']                  = $apply->medicine_price; //単位薬価
                $csv_row['unit_medicine_price']             = ($apply->unit_medicine_price !== null) ? number_format($apply->unit_medicine_price, 2) : ""; //包装薬価

                if ($purchase_kengen) {
                    $csv_row['purchase_requested_price']        = $apply->purchase_requested_price; // 要望仕入価格
                    $csv_row['purchase_estimated_price']        = $apply->purchase_estimated_price; // 見積仕入価格
                    $csv_row['purchase_price']                  = $apply->purchase_price; // 仕入価格
                }

                if ($sales_kengen) {
                    $csv_row['sales_price']                     = $apply->sales_price; //包装納入単価
                }
                $csv_row['facility_name']                   = $apply->facility_name; //施設名
                $csv_row['trader_name']                     = $apply->trader_name; //業者
                $csv_row['status']                          = \App\Model\Task::getStatus($apply->status); //状態
                $csv_row['medicine_price_start_date']       = $apply->medicine_price_start_date; //薬価有効開始日
                $csv_row['fp_start_date']                   = $apply->fp_start_date; //納入価有効開始日
                $csv_row['pa_application_date']             = $apply->pa_application_date; //申請日
                $csv_row['adoption_date']                   = $apply->adoption_date; // 採用日
                $csv_row['adoption_stop_date']              = $apply->adoption_stop_date; // 採用中止日
                $csv_row['production_stop_date']            = $apply->production_stop_date; // 製造中止日
                $csv_row['transitional_deadline']           = $apply->transitional_deadline; // 経過措置期限
                $csv_row['discontinuation_date']            = $apply->discontinuation_date; //販売中止日
                $csv_row['pa_user_name']                    = $apply->pa_user_name; //申請者
                if ($sales_kengen) {
                    $csv_row['comment']                     = $apply->comment;   //採用備考
                }
                if ($purchase_kengen) {
                    $csv_row['pa_purchase_estimate_comment']= $apply->pa_purchase_estimate_comment;   //申請備考(業者－文化連)
                }
                if ($sales_kengen) {
                    $csv_row['pa_sales_estimate_comment']   = $apply->pa_sales_estimate_comment;   //申請備考(厚生連－文化連)
                }
                if ($bunkaren_waku_kengen) {
                    $csv_row['pa_comment']                      = $apply->pa_comment;   //申請備考(文化連)
                }
                $csv_row['optional_key1']                   = $apply->optional_key1; //施設設定項目1
                $csv_row['optional_key2']                   = $apply->optional_key2; //施設設定項目2
                $csv_row['optional_key3']                   = $apply->optional_key3; //施設設定項目3
                $csv_row['optional_key4']                   = $apply->optional_key4; //施設設定項目4
                $csv_row['optional_key5']                   = $apply->optional_key5; //施設設定項目5
                $csv_row['optional_key6']                   = $apply->optional_key6; //施設設定項目6
                $csv_row['optional_key7']                   = $apply->optional_key7; //施設設定項目7
                $csv_row['optional_key8']                   = $apply->optional_key8; //施設設定項目8
                $csv_row['hq_optional_key1']                = $apply->hq_optional_key1; //本部設定項目1
                $csv_row['hq_optional_key2']                = $apply->hq_optional_key2; //本部設定項目2
                $csv_row['hq_optional_key3']                = $apply->hq_optional_key3; //本部設定項目3
                $csv_row['hq_optional_key4']                = $apply->hq_optional_key4; //本部設定項目4
                $csv_row['hq_optional_key5']                = $apply->hq_optional_key5; //本部設定項目5
                $csv_row['hq_optional_key6']                = $apply->hq_optional_key6; //本部設定項目6
                $csv_row['hq_optional_key7']                = $apply->hq_optional_key7; //本部設定項目7
                $csv_row['hq_optional_key8']                = $apply->hq_optional_key8; //本部設定項目8
                $csv_row['price_adoption_id']               = $apply->pa_id; //price_adoption_id
                $csv_row['medicine_id']                     = $apply->medicine_id; //medicine_id
                $csv_row['facility_medicine_id']            = $apply->fm_id; //facility_medicine_id
                $csv_row['facility_price_id']               = $apply->fp_id; //facility_price_id

                $csv_data[] = $csv_row;
            }
        } catch (Exception $e) {
            $a = 1;
        }

        $csvHeader   = array();
        // $csvHeader[] = '施設名';
        $csvHeader[] = '販売メーカー名';
        $csvHeader[] = '製造メーカー名';
        $csvHeader[] = '商品名';
        $csvHeader[] = '一般名';
        $csvHeader[] = '規格容量';
        $csvHeader[] = 'GS1販売';
        $csvHeader[] = 'GS1調剤';
        $csvHeader[] = 'HOT番号';
        $csvHeader[] = 'JANコード';
        $csvHeader[] = '医薬品CD';
        $csvHeader[] = 'YJCD';
        $csvHeader[] = '先後発区分';
        $csvHeader[] = '剤型';
        $csvHeader[] = '包装薬価係数';
        $csvHeader[] = '単位薬価';
        $csvHeader[] = '包装薬価';

        if ($purchase_kengen) {
            $csvHeader[] = '要望仕入価格';
            $csvHeader[] = '見積仕入価格';
            $csvHeader[] = '仕入価格';
        }

        if ($sales_kengen) {
            $csvHeader[] = '包装納入単価';
        }
        $csvHeader[] = '施設名';
        $csvHeader[] = '業者';
        $csvHeader[] = 'ステータス';
        $csvHeader[] = '薬価有効開始日';
        $csvHeader[] = '単価有効開始日';
        $csvHeader[] = '申請日';
        $csvHeader[] = '採用日';
        $csvHeader[] = '採用中止日';
        $csvHeader[] = '製造中止日';
        $csvHeader[] = '経過措置期限';
        $csvHeader[] = '販売中止日';
        $csvHeader[] = '申請者';
        if ($sales_kengen) {
            $csvHeader[] = '採用備考';
        }
        if ($purchase_kengen) {
            $csvHeader[] = '申請備考(業者－文化連)';
        }
        if ($sales_kengen) {
            $csvHeader[] = '申請備考(厚生連－文化連)';
        }
        if ($bunkaren_waku_kengen) {
            $csvHeader[] = '申請備考(文化連)';
        }
        $csvHeader[] = '施設設定項目1';
        $csvHeader[] = '施設設定項目2';
        $csvHeader[] = '施設設定項目3';
        $csvHeader[] = '施設設定項目4';
        $csvHeader[] = '施設設定項目5';
        $csvHeader[] = '施設設定項目6';
        $csvHeader[] = '施設設定項目7';
        $csvHeader[] = '施設設定項目8';
        $csvHeader[] = '本部設定項目1';
        $csvHeader[] = '本部設定項目2';
        $csvHeader[] = '本部設定項目3';
        $csvHeader[] = '本部設定項目4';
        $csvHeader[] = '本部設定項目5';
        $csvHeader[] = '本部設定項目6';
        $csvHeader[] = '本部設定項目7';
        $csvHeader[] = '本部設定項目8';
        $csvHeader[] = 'price_adoption_id';
        $csvHeader[] = 'medicine_id';
        $csvHeader[] = 'facility_medicine_id';
        $csvHeader[] = 'facility_price_id';

        foreach ($option as $key => $d) {
            if (!empty($d->optional_key_label)) {
                $csvHeader[] = $d->optional_key_label;
            }
        }

        array_unshift($csv_data, $csvHeader);

        $this->end_log();

        return $csv_data;
    }

    // 採用申請一覧SQL取得
    public function getApplayListForSQL(Request $request, $user) {
        $this->start_log();

        //$list =  $this->medicine->listWithApplyTest($request, -1,$user);
        $sql =  $this->medicine->listWithApplySql($request, -1, $user);

        $csv_data = array();
        $csv_row  = array();

        $csv_row['SQL'] = $sql; //SQL文を格納
        $csv_data[]     = $csv_row;

        $csvHeader = array('--採用申請SQL');

        array_unshift($csv_data, $csvHeader);

        $this->end_log();

        return $csv_data;
    }

    // 申請可能なユーザグループの取得
    public function getApplyGroup($facility_id, $p_status, $user) {
        $this->start_log();

        $status = -1;
        $list = null;

        if (empty($facility_id)) {
            if ($p_status <= Task::STATUS_NEGOTIATING) {
                $status = $p_status;
                $list = $this->medicine->getApplyGroupList($user->id, $status);
            }
        }

        $this->end_log();

        return $list;
    }

    //追加項目検索条件表示
    public function getOptionalSearch($p_user_group_id) {
        $this->start_log();

        $list = $this->medicine->getOptionalSearch($p_user_group_id);

        $this->end_log();

        return $list;
    }

    //追加項目検索項目 プルダウン中身取得
    public function getOptionalSearchListBox($p_user_group_id) {
        $this->start_log();

        $list = $this->medicine->getOptionalSearchListBox($p_user_group_id);

        $this->end_log();

        return $list;
    }

    // 採用申請一覧項目nullチェック
    public function checkIndexApplyKomoku($request, $priceAdoption) {
        $this->start_log();

        $check_result = false;
        logger('button_control: ' . $request->button_control);
        // $priceAdoption = $this->priceAdoption->find($request->pa_id);

        if ($request->button_control == '取り下げ') {
            $checker      = new ApplyChecker($request, $priceAdoption, 'withdraw');
            $check_result = $checker
                ->indexTraderCheck() // 業者チェック
                ->indexPurchaseCheck() // 仕入チェック
                ->indexSalesCheck() // 納入チェック
                ->check_result;
        } else {
            $checker      = new ApplyChecker($request, $priceAdoption, 'forward');
            $check_result = $checker
                ->indexTraderCheck() // 業者チェック
                ->indexStartDateCheck() // 申請単価開始日チェック
                ->indexEstimatedCheck() // 見積仕入チェック
                ->indexPurchaseCheck() // 仕入チェック
                ->indexSalesCheck() // 納入チェック
                ->check_result;
        }

        $this->end_log();

        return $check_result;
    }

    // 採用申請詳細項目nullチェック
    public function checkDetailApplyKomoku($request, $priceAdoption) {
        $this->start_log();

        $check_result = false;
        logger('apply_control == ' . $request->apply_control . ', button_control: ' . $request->button_control);
        // $priceAdoption = $this->priceAdoption->find($request->pa_id);

        if ($request->apply_control == '1') {
            if ($request->button_control == '取り下げ') {
                $checker      = new ApplyChecker($request, $priceAdoption, 'withdraw');
                $check_result = $checker
                    ->detailTraderCheck() // 業者チェック
                    ->detailPurchaseCheck() // 仕入チェック
                    ->detailSalesCheck() // 納入チェック
                    ->check_result;
            } else {
                $checker      = new ApplyChecker($request, $priceAdoption, 'forward');
                $check_result = $checker
                    ->detailTraderCheck() // 業者チェック
                    ->detailStartDateCheck() //申請単価有効開始日
                    ->detailEstimatedCheck() // 見積仕入チェック
                    ->detailPurchaseCheck() // 仕入チェック
                    ->detailSalesCheck() // 納入チェック
                    ->check_result;
            }
        } elseif ($request->apply_control == '2') {
            $checker      = new ApplyChecker($request, $priceAdoption, 'remand');
            $check_result = $checker
                ->detailTraderCheck() // 業者チェック
                ->detailPurchaseCheck() // 仕入チェック
                ->detailSalesCheck() // 納入チェック
                ->check_result;
        } elseif ($request->apply_control == '3') {
            $checker      = new ApplyChecker($request, $priceAdoption, 'reject');
            $check_result = $checker
                ->detailTraderCheck() // 業者チェック
                ->detailPurchaseCheck() // 仕入チェック
                ->detailSalesCheck() // 納入チェック
                ->check_result;
        }

        $this->end_log();

        return $check_result;
    }

    /**
     * 採用一覧のボタンの情報を返す.
     * @param mixed $apply
     */
    private function button_new($apply) {
        $url   = '';
        $label = '';
        $style = '';

        if ($apply->status_forward > 0) {
            if ($apply->status == '91') {
                $url = route('apply.adopt2', array('id' => $apply->medicine_id));
            } else {
                $url = $apply->status_forward_url
                    ? route($apply->status_forward_url, array('id' => $apply->medicine_id))
                    : '';
            }
            $label = $apply->status_forward_label;
            $style = $apply->status_forward_color_code;
        } else {
            $url = $apply->status_withdraw_url
                ? route($apply->status_withdraw_url, array('id' => $apply->medicine_id))
                : '';
            $label = $apply->status_withdraw_label;
            $style = $apply->status_withdraw_color_code;
        }

        return array(
            'url'   => $url,
            'label' => $label,
            'style' => $style,
        );
    }

    /**
     * 画面で更新されたコメントをDBオブジェクトにセットする.
     * @param Request $request
     * @param mixed   $pa
     * @param mixed   $fm
     */
    private function setRequestComment(Request $request, $pa, $fm): void {
        // 申請備考(業者－文化連)
        if (!empty($request->pa_purchase_estimate_comment)) {
            $pa->purchase_estimate_comment = $request->pa_purchase_estimate_comment;
        }
        // 申請備考(厚生連－文化連)
        if (!empty($request->pa_sales_estimate_comment)) {
            $pa->sales_estimate_comment = $request->pa_sales_estimate_comment;
        }
        // 申請備考(文化連)
        if (!empty($request->pa_comment)) {
            $pa->comment = $request->pa_comment;
        }
        // 採用備考
        if (!empty($request->fm_comment)) {
            $fm->comment = $request->fm_comment;
        }
    }

    // JANコードチェック
    private function existsJanCode($jan_code, Request $request, $is_add = true) {
        // $user = app('UserContainer');

        //”不明”の場合は、チェックしない
        if ($jan_code == '不明') {
            return true;
        }

        if ($this->packUnit->where('jan_code', $jan_code)->exists()) {
            $request->session()->flash('errorMessage', '既にそのマスタは存在しますので、検索条件をクリアし、再度検索してください');

            return false;
        }

        if ($is_add) {
            // TODO jan_codeの項目削除に伴い、ここが修正必要
            //logger('JANCHECK');
            //logger($this->priceAdoption->chackJanCode($jan_code));
            if (!empty($this->priceAdoption->chackJanCode($jan_code))) {
                $request->session()->flash('errorMessage', '入力されたJANコードは既に申請されています。');

                return false;
            }

            /*
            if ($this->priceAdoption->where('jan_code', $jan_code)
                    ->where('status', '!=', Task::STATUS_UNAPPLIED)
                    ->getUserGroupScope($user->getUserGroup(), 'sales_user_group_id')
                    ->exists()) {
                        $request->session()->flash('errorMessage', '入力されたJANコードは既に申請されています。');
                        return false;
            }*/
        }

        return true;
    }

    /*
    * $gs1_code:販売包装単位コードの2桁目〜12桁分(14987136118058⇒498713611805)
    * 戻り値：チェックデジット付きのJANコード
    *
    */
    private function getJanCheckDegit($gs1_code) {
        //GS1販売コードが数字の場合
        if (is_numeric(mb_convert_kana($gs1_code, 'a'))) {
            $gs1_code = mb_convert_kana($gs1_code, 'a');

            if (strlen($gs1_code) == 13) {
                $cut      = 1;
                $replace  = substr($gs1_code, 0, strlen($gs1_code) - $cut); //終わりの1文字をカット
                $gs1_code = $replace;
            } elseif (strlen($gs1_code) == 14) {
                $cut      = 1;
                $replace  = substr($gs1_code, $cut, strlen($gs1_code) - $cut); //前後の1文字をカット
                $replace  = substr($replace, 0, strlen($replace) - $cut);
                $gs1_code = $replace;
            }

            $arr = str_split($gs1_code);
            $odd = 0;
            $mod = 0;

            for ($i = 0; $i < count($arr); $i++) {
                if (($i + 1) % 2 == 0) {
                    //偶数の総和
                    $mod += (int) ($arr[$i]);
                } else {
                    //奇数の総和
                    $odd += (int) ($arr[$i]);
                }
            }

            //偶数の和を3倍+奇数の総和を加算して、下1桁の数字を10から引く
            $cd = 10 - (int) (substr((string) ($mod * 3) + $odd, -1));

            //10なら1の位は0なので、0を返す。
            if ($cd === 10) {
                $jan_code = $gs1_code . '0';
            } else {
                $jan_code = $gs1_code . $cd;
            }
        } else {
            $jan_code = $gs1_code;
        }

        return $jan_code;
    }

    /*
    * $str:単価入力値
    * 戻り値：カンマを除去した入力値
    *
    */
    private function removeComma($str) {
        $removeCommaStr = str_replace(",", "",$str);
        $removeCommaStr = str_replace("，", "",$removeCommaStr);
        return $removeCommaStr;
    }

    public function ajaxStatusChangeBunkaren($request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            
            try {
                $result                 = $this->priceAdoption->ajaxStatusChangeBunkaren($request, $user);
            // } catch (\PDOException $e) {
            //     logger('PDOException');
            //     logger($e->getMessage());
            //     throw $e;
            } catch (\Exception $e) {
                logger('Exception');
                logger($e->getMessage());
                throw $e;
            }
            $err_comment            = $result[0]->err_comment;
            $pa_id                  = $result[0]->pa_id;
            $status                 = $result[0]->status;
            $sales_user_group_id    = $result[0]->sales_user_group_id;
            $purchase_user_group_id = $result[0]->purchase_user_group_id;

            if (empty($err_comment)){
                $priceAdoption = $this->priceAdoption->find($pa_id);
                try {
                    $mail_users    = $this->medicine->getEmailApply(
                        $status,
                        $sales_user_group_id,
                        $user->primary_user_group_id,
                        $purchase_user_group_id
                    );
                } catch (\Exception $e) {
                    throw $e;
                }
                $mq = new MailQueService($this->mailQue->getMailQueID());
                $mq->send_mail_add($mail_users, $priceAdoption, $status);
            }

            \DB::commit();
            $this->end_log();
            return $result;
        } 
        catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

}
