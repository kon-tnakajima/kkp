<?php

namespace App\Services;

use App\Model\Medicine;
use App\Model\MedicinePrice;
use App\Model\PackUnit;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Model\Valiation;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use App\Model\Maker;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Mail\AdoptionMail;
use Illuminate\Support\Facades\Mail;
use App\Model\FileStorage;
use App\Model\TransactFileStorage;
use App\Model\TransactHeader;
use App\Model\TransactDetail;
use App\Model\TransactConfirmation;
use App\Model\ClaimPayment;
use App\Model\ClaimInvoice;
use App\Model\ClaimHistoryComment;
use App\Model\UserGroup;
use App\Model\UserGroupSupplyDivision;
use App\Model\UserGroupDataType;
use App\Model\GroupTraderRelation;
use App\Model\ActionNameStatusForward;

use Exception;
use App\Model\MailQue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Services\BaseService;

class ClaimService extends BaseService {
    const DEFAULT_PAGE_COUNT = 20;
    const DEFAULT_PAGE_COUNT2 = 100;
    // TODO ステータス下記も影響あり
    const MAIL_STR = [
        Task::STATUS_UNCLAIMED => '未登録',
        Task::STATUS_INVOICING => '登録中',
        Task::STATUS_CONFIRM => '確認待ち',
        Task::STATUS_CLAIMED => '確認済',
    ];

    // 業者別合計表出力
    const TOTAL_TABLE_BY_TRADER = 1;
    // メーカー別合計表出力
    const TOTAL_TABLE_BY_MAKER  = 2;

    const TITLE_TRADER_NAME = '業者';
    const TITLE_MAKER_NAME  = 'メーカー';

    const OUTPUT_TYPE_ARRARY = [
        self::TOTAL_TABLE_BY_TRADER => '施設・供給区分・業者別合計表',
        self::TOTAL_TABLE_BY_MAKER  => '施設・供給区分・メーカー別合計表'
    ];

    const REVERT_TYPE_INITIAL = 0;  // 取り下げでも差し戻しでもない
    const REVERT_TYPE_WITHDRAW = 1; // 取り下げ
    const REVERT_TYPE_REMAND = 2;   // 差し戻し
    const REVERT_TYPE_REJECT = 3;   // 却下

    /*
     * コンストラクタ
     */
    public function __construct() {
        $this->medicine = new Medicine();
        $this->valiation = new Valiation();
        $this->facilityMedicine = new FacilityMedicine();
        $this->maker = new Maker();
        $this->tasks = new Task();
        $this->facilityPrice = new FacilityPrice();
        $this->medicinePrice = new MedicinePrice();
        $this->packUnit = new PackUnit();

        $this->fileStorage = new FileStorage();
        $this->transactFileStorage = new TransactFileStorage();
        $this->transactHeader = new TransactHeader();
        $this->transactDetail = new TransactDetail();
        $this->transactConfirmation = new TransactConfirmation();

        $this->claimPayment = new ClaimPayment();
        $this->claimInvoice = new ClaimInvoice();
        $this->claimHistoryComment = new ClaimHistoryComment();
        $this->mailQue = new MailQue();

        $this->user = new User();
        $this->userGroup = new UserGroup();
        $this->supply    = new UserGroupSupplyDivision();
        $this->userGroupDataType = new UserGroupDataType();
        $this->groupTraderRelation    = new GroupTraderRelation();
        $this->actionNameStatusForward = new ActionNameStatusForward();
    }

    /*
     * 請求登録履歴一覧取得
     */
    public function getList(Request $request, $user) {
        $this->start_log();
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->transactFileStorage->getList($request, $count, $user);
        // リストにボタン情報付加
        if (!is_null($list)) {
            foreach($list as $key => $claim) {
                // TODO 請求登録のボタン丸替え。ただし、buttonは詳細側と共通処理の為、考慮が必要だが。。
                // 請求照会と処理が変わりそうだから共通が難しかったら別で対応することも視野
                $list[$key]->button = $this->button($claim, 3, 0);

                //TODO 却下に変更
                $list[$key]->buttonReject = $this->buttonReject($claim);
                $list[$key]->url = route('claim.trader',['id' => $claim->id ]);
            }
        }
        $this->end_log();
        return $list;
    }

    /*
     * 請求登録履歴一覧SQL取得
     */
    public function getClaimListForSQL(Request $request, $user) {
        $this->start_log();
    	$sql =  $this->transactFileStorage->getListSql($request, -1,$user);

    	$csv_data = array();
    	$csv_row=array();

    	$csv_row['SQL'] = $sql; // SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = [ '--請求登録履歴一覧SQL' ];

    	array_unshift($csv_data, $csvHeader);

        $this->end_log();
    	return $csv_data;
    }

    /*
     * 請求登録履歴ユーザーガイド文取得
     */
    public function getRegistListUserGuide($user) {
        $this->start_log();
        $user_guide =  $this->transactFileStorage->getUserGuide($user);
        $this->end_log();
        return $user_guide;
    }     

    /*
     * 請求照会_業者別ユーザーガイド文取得
     */
    public function getTraderUserGuide($transact_header_id,$user) {
        $this->start_log();
        $user_guide =  $this->transactConfirmation->getUserGuide($transact_header_id,$user);
        $this->end_log();
        return $user_guide;
    }  

    /*
     * 請求詳細取得 TODO おそらく使用していない
     */
    public function getTransactFileStorage(Request $request, $transact_file_storage_id) {
        $this->start_log();
    	$detail = $this->transactFileStorage->getTransactFileStorage($transact_file_storage_id);

        // リストにボタン情報付加
        $detail->button = $this->button($detail);
        $detail->buttonRemand = $this->buttonRemand($detail);
        $detail->flg = $request->flg;
        if (!empty($request->page)) {
            $detail->page = $request->page;
        }
        $this->end_log();
        return $detail;
    }

    /*
     * 標準薬品情報取得
     */
    public function getMedicine($id) {
        $this->start_log();
        $result = $this->medicine->find($id);
        $this->end_log();
        return $result;
    }

    /*
     * 検索フォームの施設チェックボックスに使用する施設一覧
     */
    public function getFacilities($user) {
        $this->start_log();
        $result = $this->facility->getFacility($user->facility, 'id')->hospital()->orderBy('name', 'asc')->get();
        $this->end_log();
        return $result;
    }

    /**
     * 検索フォームの施設チェックボックスに使用する施設一覧
     * @param User $user ユーザ情報
     * @return UserGroup情報(id,name,disp_orderの3カラム1組の複数)
     */
    public function getUserGroups($user) {
        $this->start_log();
        $result = $this->userGroup->getUserGroups($user);
        $this->end_log();
        return $result;
    }


    // 請求登録用施設
    public function getUserGroupClaimRegistFacility($user) {
        $this->start_log();
        $result = $this->userGroup->getUserGroupClaimRegistFacility($user);
        $this->end_log();
        return $result;
    }

    // 請求登録履歴用検索フォーム施設
    public function getUserGroupClaimRegist($user) {
        $this->start_log();
        $result = $this->userGroup->getUserGroupClaimRegist($user);
        $this->end_log();
        return $result;
    }


    // 請求照会用検索フォーム施設
    public function getUserGroupClaim($user) {
        $this->start_log();
        $result = $this->userGroup->getUserGroupClaim($user);
        $this->end_log();
        return $result;
    }

    /*
     * 取引ヘッダ一覧
     */
    public function getTransactHeader($header_id,$user) {
        $this->start_log();
        $result = $this->transactHeader->getData($header_id, $user);
        $this->end_log();
    	return $result;
    }

    /*
     * 請求一覧明細別ヘッダーSQL取得
     */
    public function getDetailHeaderForSQL($header_id, $user) {
        $this->start_log();
    	$sql =  $this->transactHeader->getDataSql($header_id, $user);

    	$csv_data = array();
    	$csv_row = array();

    	$csv_row['SQL'] = $sql;//SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = [ '--明細別ヘッダーSQL' ];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 取引ヘッダ一覧
     */
    public function getDetailHeaders(Request $request, $user) {
        $this->start_log();
    	$count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->transactHeader->getList($request, $user, $count);

        // リストにボタン情報付加
        if (!is_null($list)) {
            foreach ($list as $key => $header) {
                $list[$key]->url = route('claim.trader',['id' => $header->id ]);
            }
        }
        $this->end_log();
        return $list;
    }

    /*
     * 請求一覧SQL取得
     */
    public function getClaimIndexForSQL(Request $request, $user) {
        $this->start_log();
    	// getDetailHeaders 請求一覧で使用
    	$sql =  $this->transactHeader->getListSql($request, $user, -1);

    	$csv_data = array();
    	$csv_row=array();

    	$csv_row['SQL'] = $sql; // SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = [ '--請求一覧SQL' ];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 請求一覧SQL取得(業者別ヘッダー) 請求一覧照会と一緒のSQL
    */
    public function getTraderHeaderForSQL(Request $request, $user) {
        $this->start_log();
    	// getDetailHeaders 請求一覧で使用
    	$sql =  $this->transactHeader->getListSql($request, $user, -1);

    	$csv_data = array();
    	$csv_row=array();

    	$csv_row['SQL'] = $sql;//SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = ['--請求一覧業者別ヘッダーSQL'];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 業者一覧
     */
    public function getDetailTraders($transact_header_id, $count) {
        $this->start_log();
    	$user = Auth::user();
        $list =  $this->transactConfirmation->getList($transact_header_id, $user,$count);

        // リストにボタン情報付加
        if (!is_null($list)) {
            foreach($list as $key => $trader_detail) {
                $list[$key]->url = route('claim.detail',['id' => $trader_detail->id ]);
                $list[$key]->taskButtonFacility = $this->button($trader_detail, 5, 1);
                // $list[$key]->taskButtonTrader = $this->button($trader_detail, 4, 2);
                $list[$key]->buttonWithdrawFacility = $this->buttonWithdraw($trader_detail, 1);
                // $list[$key]->buttonWithdrawTrader = $this->buttonWithdraw($trader_detail, 2);
                $list[$key]->buttonRemandFacility = $this->buttonRemand_new($trader_detail, 1);
                // $list[$key]->buttonRemandTrader = $this->buttonRemand_new($trader_detail, 2);
            }
        }
        $this->end_log();
        return $list;
    }

    /*
     * 請求一覧SQL取得(業者別明細)
     */
    public function getTraderDetailForSQL($transact_header_id, $count) {
        $this->start_log();
    	$user = Auth::user();
    	$sql =  $this->transactConfirmation->getListSql($transact_header_id, $user, $count, null);

    	$csv_data = array();
    	$csv_row=array();

    	$csv_row['SQL'] = $sql;//SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = ['--請求一覧業者別明細SQL'];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 業者を取得
     */
    public function getTrader($trader_id) {
        $this->start_log();
        $result = $this->facility->where('id', $trader_id)->first();
        $this->end_log();
        return $result;
    }

    /*
     * コメント取得
     */
    public function getClaimHistoryComment(Request $request) {
        $this->start_log();
        $list =  $this->claimHistoryComment->getDataList($request->id);
        $this->end_log();
        return $list;
    }

    /*
     * 既読フラグ更新
     */
    public function updateReadFlg(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $result = $this->claimHistoryComment->updateReadFlg($user->id, $request->id);
        $this->end_log();
        return $result;
    }

    /*
     * 請求登録
     */
    public function regist(Request $request) {
        $this->start_log();
        $data_name = $this->userGroupDataType->select('data_type_name')->where('id', $request->supply_division )->first();
    	$check = $this->transactFileStorage->checkFacilityDataType($request->facility, isset($data_name) ? $data_name->data_type_name : '' );

        if ( !$check[0]->f_claim_regist_user_group_data_type_check ) {
            $request->session()->flash('errorMessage', '施設とファイル種別の組み合わせが不正です');
            $request->session()->flash('claim_date', $request->claim_month);
            $request->session()->flash('select_data_type', $request->supply_division);
            $request->session()->flash('select_facility', $request->facility);
            $request->session()->flash('medicine_price_total', $request->medicine_price_total);
            $request->session()->flash('sales_price_total', $request->sales_price_total);
            $request->session()->flash('purchase_price_total', $request->purchase_price_total);
            $this->end_log();
            return false;
        }

        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $file_name = $request->file('file')->getClientOriginalName();

            //請求情報の登録
            $this->transactFileStorage->claim_month = $request->claim_month.'/01';//取引年月
            $this->transactFileStorage->data_type = $request->supply_division;//供給区分

            $this->transactFileStorage->medicine_price_total = empty($request->medicine_price_total) ? null : str_replace(',','',$request->medicine_price_total);//薬価金額合計
            $this->transactFileStorage->sales_price_total = empty($request->sales_price_total) ? null : str_replace(',','',$request->sales_price_total);//売上金額合計
            $this->transactFileStorage->purchase_price_total = empty($request->purchase_price_total) ? null : str_replace(',','',$request->purchase_price_total);//仕入金額合計

            $this->transactFileStorage->claim_task_status = 10; //初期ステータスを設定
            $this->transactFileStorage->attachment = encodeByteaData($request->file('file'));//添付ファイル
            $this->transactFileStorage->file_name = $file_name;//ファイル名

            // リクエストからに変更
            $this->transactFileStorage->user_group_id = $request->facility;
            $this->transactFileStorage->revert_type = self::REVERT_TYPE_INITIAL;
            $this->transactFileStorage->save();

            // メールキュー登録
            $mail_users = $this->transactFileStorage->getEmailClaim(
                $this->transactFileStorage->claim_task_status,
                $this->transactFileStorage->user_group_id,
                0
            );
            $mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->find($this->transactFileStorage->user_group_id);
            $userGroupDataType = $this->userGroupDataType->find($this->transactFileStorage->data_type);

            $mq->send_mail_add_claim(
                $mail_users,
                $userGroup,
                $userGroupDataType,
                $this->transactFileStorage
            );


            \DB::commit();
            $request->session()->flash('message', $file_name.'を登録しました。登録したファイルは[請求登録履歴]メニューで確認できます。');
            $request->session()->flash('claim_date', $request->claim_month);
            $request->session()->flash('select_data_type', $request->supply_division);
            $request->session()->flash('select_facility', $request->facility);
            $request->session()->flash('medicine_price_total', $request->medicine_price_total);
            $request->session()->flash('sales_price_total', $request->sales_price_total);
            $request->session()->flash('purchase_price_total', $request->purchase_price_total);
            $this->end_log();
            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            echo $e->getMessage();
            $request->session()->flash('errorMessage', $e->getMessage());
            \Log::debug($e->getMessage());
            $this->end_log();
            return false;
        }
    }

    /*
     * 請求受付
     */
    public function recept_old(Request $request) {
    	\DB::beginTransaction();
    	try {
    		$user = Auth::user();
    		$transactFileStorage = $this->transactFileStorage->find($request->id);

    		$kengen= $this->transactFileStorage->getRegistPrivilegesTransact(
                $user->id,
                $transactFileStorage->sales_user_group_id,
                $transactFileStorage->claim_task_status,
                0
            );
    		$staus=$kengen[0]->status_forward;

    		if ($staus > Task::STATUS_TRADER_CONFIRM) {
    			$staus=Task::STATUS_TRADER_CONFIRM;
    		}

    		$transactFileStorage->claim_task_status = $staus;
    		$transactFileStorage->revert_type = self::REVERT_TYPE_INITIAL;
    		$transactFileStorage->receipt_date = now();
   			$transactFileStorage->save();

   			//ここはメール必要ない

    		\DB::commit();
            $this->end_log();
    		return true;

    	} catch (\PDOException $e){
    		\DB::rollBack();
    		echo $e->getMessage();
    		$request->session()->flash('errorMessage', $e->getMessage());
            \Log::debug($e->getMessage());
            $this->end_log();
    		return false;
    	}
    }

    /*
     * 請求受付
     */
    public function recept(Request $request) {
        $this->start_log();
    	\DB::beginTransaction();
    	try {
    		$user = Auth::user();
    		$transactFileStorage = $this->transactFileStorage->find($request->id);
    		$transactFileStorage->claim_task_status = $request->status_forward;
    		$transactFileStorage->revert_type = self::REVERT_TYPE_INITIAL;
    		if($request->status_forward = 20){
                $transactFileStorage->receipt_date = now();
            }
                
   			$transactFileStorage->save();

    	    $mail_users = $this->transactFileStorage->getEmailClaim(
                $transactFileStorage->claim_task_status,
                $transactFileStorage->user_group_id,
                0
            );
            $mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->find($transactFileStorage->user_group_id);
            $userGroupDataType = $this->userGroupDataType->find($transactFileStorage->data_type);

            foreach($mail_users as $mail_user) {
                $mq->send_mail_add_claim(
                    $mail_user->email,
                    $userGroup,
                    $userGroupDataType,
                    $transactFileStorage
                );
            }

    		\DB::commit();
    		$request->session()->flash('message', '更新しました');
            $this->end_log();
    		return true;

    	} catch (\PDOException $e){
    		\DB::rollBack();
    		echo $e->getMessage();
    		$request->session()->flash('errorMessage', $e->getMessage());
            \Log::debug($e->getMessage());
            $this->end_log();
    		return false;
    	}
    }


    /*
     * 請求登録再実行
     */
    public function regist_retry(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $tfs = $this->transactFileStorage->find($request->id);
            $kengen = $this->transactFileStorage->getRegistPrivilegesTransact(
                $user->id,
                $user->primary_user_group_id,
                Task::STATUS_UNCLAIMED,
                0
            );
            $staus = $kengen[0]->status_forward;
            $tmp_claim_task_status = $tfs->claim_task_status;
            $tfs->claim_task_status = $kengen[0]->status_forward;
            $tfs->revert_type = self::REVERT_TYPE_INITIAL;
            $tfs->save();

            $mail_users = $this->transactFileStorage->getEmailClaim(
                $tfs->claim_task_status,
                $tfs->sales_user_group_id,
                0
            );
            $mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->getUserGroup($tfs->sales_user_group_id);
            foreach($mail_users as $mail_user) {
                $mq->send_mail_add_claim(
                    $mail_user->email,
                    $userGroup[0]->id,
                    $userGroup[0]->name,
                    $tmp_claim_task_status,
                    $tfs->claim_month
                );
            }

            \DB::commit();
            $this->end_log();
            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            $this->end_log();
            return false;
        }
    }

    /*
     * バリエーション作成
     */
    public function makeValiation($claim) {
        $this->start_log();
        // valiationデータ作成
        $data = array();
        $valiation = $this->valiation->getByClaimID($claim->id);

        $data['current_task_id'] = Task::getByStatusForClaim($claim->claim_task_status, $claim->target_actor_id)->id;
        $data['next_task_id'] = Task::nextForClaim($data['current_task_id'], $claim->target_actor_id)->id;

        if (is_null($valiation)) {
            $data['price_adoption_id'] = 0;
            $data['claim_id'] = $claim->id;
            $this->valiation->create($data);
        } else {
            $valiation->update($data);
        }
        $this->end_log();
    }

    /*
     * 支払確認
     */
    public function payconf_old(Request $request) {
        $this->start_log();
    	$user = Auth::user();

    	try {
    		\DB::beginTransaction();

    		//$this->next($request);

    		//請求支払確認
    		$transactConfirmations = $this->transactConfirmation->where("id", $request->id)->get();
    		$kengen= $this->transactFileStorage->getRegistPrivilegesTransact(
                $user->id,
                $transactConfirmations[0]->sales_user_group_id,
                $transactConfirmations[0]->trader_confirmation_status,
                1
            );

    		if (empty($transactConfirmations)) { // 存在しない場合
    			throw new PDOException('請求支払確認データが存在しません。');
    		} else {
    			foreach($transactConfirmations as $trans_confirm) {

    				$data = array();
    				//$data['trader_confirmation_status'] = Task::STATUS_CLAIMED;//
    				$data['trader_confirmation_status'] = $kengen[0]->status_forward;//
    				$data['revert_type'] = self::REVERT_TYPE_INITIAL;
    				$trans_confirm->update($data);
    			}
    		}

    		$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();
    		$mail_users = $this->transactFileStorage->getEmailClaim(
                $transactConfirmations[0]->trader_confirmation_status,
                $transactConfirmations[0]->sales_user_group_id,
                1
            );
    		$mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->sales_user_group_id);
            foreach ($mail_users as $mail_user) {
    			$mq->send_mail_add_claim_detail(
                    $mail_user->email,
                    $userGroup[0]->name,
                    $transactConfirmations[0]->trader_confirmation_status,
                    $transactConfirmations[0]->claim_month
                );
    		}


    		\DB::commit();

    	} catch (\PDOException $e){
    		\DB::rollBack();
    		$request->session()->flash('errorMessage', $e->getMessage());
            $this->end_log();
            return false;
        }
        $this->end_log();
    	return true;
    }

     /*
     * 支払確認(請求ステータス更新)
     */
    public function payconf(Request $request) {
        $this->start_log();
    	$user = Auth::user();

    	try {
    		\DB::beginTransaction();

    		//請求支払確認
    		$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();

    		if (empty($transactConfirmations)) { // 存在しない場合
    			throw new PDOException('請求支払確認データが存在しません。');
    		} else {
    			foreach($transactConfirmations as $trans_confirm) {

    				$data = array();
                    if ($request->claim_control == 1) {
                    	$data['facility_confirmation_status'] = $request->facility_status_foward;
                    	$data['facility_confirmation_date'] = date('Y-m-d');//施設確認日
                        $data['facility_confirmation_account'] = $user->id;//施設確認アカウント
                    } else if ($request->claim_control == 2) {
    				    $data['trader_confirmation_status'] = $request->trader_status_foward;
    				    $data['trader_confirmation_date'] = date('Y-m-d');//施設確認日
                        $data['trader_confirmation_account'] = $user->id;//施設確認アカウント
                    }
    				$data['revert_type'] = self::REVERT_TYPE_INITIAL;
    				$trans_confirm->update($data);
    			}
    		}

    		$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();
    		$mail_users = $this->transactFileStorage->getEmailClaimDetail(
                $transactConfirmations[0]->sales_user_group_id,
                $transactConfirmations[0]->purchase_user_group_id,
                $transactConfirmations[0]->facility_confirmation_status,
                $transactConfirmations[0]->trader_confirmation_status,
                $request->claim_control
            );
    		$mq = new MailQueService($this->mailQue->getMailQueID());

            $userGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->sales_user_group_id);
            $traderUserGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->purchase_user_group_id);
            $status ="";
    	    if ($request->claim_control == 1) {
            	$status = $transactConfirmations[0]->facility_confirmation_status;
            } else if($request->claim_control == 2) {
            	$status = $transactConfirmations[0]->trader_confirmation_status;
            }

            $mq->send_mail_add_claim_detail(
                $mail_users,
                $userGroup[0]->name,
                $status,
                $transactConfirmations[0]->claim_month,
                $request->claim_control,
                $traderUserGroup[0]->name,
                $transactConfirmations[0]->transact_header_id
            );


            // foreach ($mail_users as $mail_user) {
    		// 	$mq->send_mail_add_claim_detail(
            //         $mail_user->email,
            //         $userGroup[0]->name,
            //         $status,
            //         $transactConfirmations[0]->claim_month,
            //         $request->claim_control,
            //         $traderUserGroup[0]->name,
            //         $transactConfirmations[0]->transact_header_id
            //     );
    		// };

            \DB::commit();
            $request->session()->flash('message', '更新しました');
    	} catch (\PDOException $e){
    		\DB::rollBack();
            $request->session()->flash('errorMessage', $e->getMessage());
            $this->end_log();
    		return false;
        }
        $this->end_log();
    	return true;
    }

    /*
     * 請求明細削除
     */
    public function delete_detail(Request $request) {
        $this->start_log();
    	$result=array();

    	try {
    		\DB::beginTransaction();

    		//請求支払確認
    		$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();

    		if (empty($transactConfirmations)) {//存在しない場合
    			throw new PDOException('請求支払確認データが存在しません。');
    		} else {
    			$transact_confirm_id      = $transactConfirmations[0]->id;
    			$transact_header_id       = $transactConfirmations[0]->transact_header_id;
    			$transact_file_storage_id = $transactConfirmations[0]->transact_file_storage_id;

    			\DB::table('transact_details')->where('transact_confirmation_id', '=', $transact_confirm_id)->delete();
    			\DB::table('transact_confirmations')->where('id', '=', $transact_confirm_id)->delete();
    			\DB::table('claim_history_comments')->where('transact_confirmation_id', '=', $transact_confirm_id)->delete();

    			$trans_detail = $this->transactDetail->where('transact_header_id', '=', $transact_header_id)->first();
    			if (empty($trans_detail)) {
    				\DB::table('transact_headers')->where('id', '=', $transact_header_id)->delete();

    				$result[]="1";
    			}
    		}

    		\DB::commit();
    	} catch (\PDOException $e) {
    		\DB::rollBack();
            $request->session()->flash('errorMessage', $e->getMessage());
            $this->end_log();
    		return array();
        }
        $this->end_log();
    	return $result;
    }

    /*
     * 請求確認
     */
    public function confirm(Request $request) {
        $this->start_log();
        $user = Auth::user();

        try {
            \DB::beginTransaction();

            //請求支払確認
            $transactConfirmations = $this->transactConfirmation->where("id", $request->id)->get();
            $status=$transactConfirmations[0]->trader_confirmation_status;

            if (empty($transactConfirmations)) { // 存在しない場合
                throw new PDOException('請求支払確認データが存在しません。');
            } else {
                foreach ($transactConfirmations as $trans_confirm) {
                    $data = array();
                    $data['facility_confirmation_status'] = Task::STATUS_CLAIMOK;//施設確認状況
                    $data['facility_confirmation_date'] = date('Y-m-d');//施設確認日
                    $data['facility_confirmation_account'] = $user->id;//施設確認アカウント
                    $data['trader_confirmation_status'] = Task::STATUS_CLAIMOK;
                    $data['revert_type'] = self::REVERT_TYPE_INITIAL;
                    $trans_confirm->update($data);
                }
            }

            $mail_users = $this->transactFileStorage->getEmailClaim(
                $transactConfirmations[0]->trader_confirmation_status,
                $transactConfirmations[0]->sales_user_group_id,
                1
            );
            $mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->sales_user_group_id);
            foreach ($mail_users as $mail_user) {
            	$mq->send_mail_add_claim_detail(
                    $mail_user->email,
                    $userGroup[0]->name,
                    $status,
                    $transactConfirmations[0]->claim_month
                );
            }

            \DB::commit();
        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', $e->getMessage());
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 価格確定
     */
    public function complete(Request $request) {
        $this->start_log();
        try {
            \DB::beginTransaction();

            $transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();
            if (empty($transactConfirmations)) { // 存在しない場合
                throw new \PDOException('請求支払確認データが存在しません。');
            }
            foreach ($transactConfirmations as $trans_confirm) {
                //支払確認書 請求支払確認分、作成
                $this->claimPayment->claim_month = $trans_confirm->claim_month;//取引年月

                //明細を取得
                $trans_detail = $this->transactDetail->getList($trans_confirm->id)->first();
                if (empty($trans_detail)) {
                    throw new \PDOException('請求明細が存在しません。');
                }
                $this->claimPayment->bunkaren_payment_code = $trans_detail->bunkaren_payment_code;//文化連支払先CD
                $this->claimPayment->payee_name ='';
                $this->claimPayment->save();

                //請求書 請求支払確認分、作成
                $this->claimInvoice->claim_month = $trans_confirm->claim_month;//取引年月
                $this->claimInvoice->bunkaren_billing_code = $trans_detail->bunkaren_billing_code;//文化連請求先CD
                $this->claimInvoice->invoice_name = '';//請求先名
                $this->claimInvoice->save();

                //請求支払確認
                $data = array();
                $data['claim_invoice_id'] = $this->claimInvoice->id;//請求書参照ID
                $data['claim_payment_id'] = $this->claimPayment->id;//支払確認書参照ID
                $data['trader_confirmation_status'] = Task::STATUS_COMPLETE;
                $data['revert_type'] = self::REVERT_TYPE_INITIAL;
                $trans_confirm->update($data);
            }
            \DB::commit();

        } catch (\PDOException $e) {
            \DB::rollBack();
            $request->session()->flash('errorMessage', $e->getMessage());
            \Log::debug($e->getMessage());
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 売買登録(ファイルアップロード)
     */
    public function import(Request $request) {
        $this->start_log();
        // 下記いずれかがnullの場合、詳細にリダイレクト
        if (is_null($request->file('file')) || empty($request->file('file'))) {
            \Redirect::to(route('claim.regist_detail'))->send();
            exit;
        }

        try {
            \DB::beginTransaction();

            $user = Auth::user();

            $file_name = $request->file('file')->getClientOriginalName();

            if (Storage::disk('local')->exists('public/'.$file_name)){
                $file_name .= date('Ymds');
            }

            $request->file('file')->storeAs('public/', $file_name);

            $a = $request->file('file')->getClientMimeType();
            if ($request->file('file')->getClientMimeType() == "text/plain" ||
                $request->file('file')->getClientMimeType() == "application/octet-stream"
            ) {
            } else {
            	throw new \PDOException('ファイル形式がTSVではありません。'.$a);
            }

            //ファイルストレージ
            $fileStorage = $this->fileStorage->getData($file_name);
            //アップロードID取得
            $upload_id=$this->fileStorage->getUploadSequence();
            if (empty($fileStorage)) { // 存在しない場合
                $this->fileStorage = new FileStorage();
                $this->fileStorage->attachment = encodeByteaData($request->file('file'));//添付ファイル
                $this->fileStorage->file_name = $file_name;//ファイル名
                $this->fileStorage->upload_id=$upload_id;
                $this->fileStorage->save();
                $fileStorage = $this->fileStorage->getData($file_name);
            }

            \DB::commit();
            $request->session()->flash('message', 'アップロードしました');
            $this->end_log();
            return true;
        } catch (\PDOException $e){
            \Log::debug($e->getMessage());
            \DB::rollBack();
            $request->session()->flash('errorMessage', $e->getMessage());
            $this->end_log();
            return false;
        }
    }

    /*
     * next
     */
    public function next(Request $request, $batch_user_id = null) {
        $this->start_log();
        try {
            // valation取得
            $valiation = $this->valiation->where('claim_id', $request->id)->first();

            // invoice取得
            $transactFileStorage = $this->transactFileStorage->find($request->id);
            $next = Task::nextForClaim($valiation->current_task_id, $transactFileStorage->target_actor_id);

            $user = Auth::user();
            $transactFileStorage->claim_task_status = $next->status; // 現在のステータスをセット
            if (!empty($batch_user_id)) {
                $transactFileStorage->updater = $batch_user_id;
            }
            $transactFileStorage->save();

            // valiationデータ更新
            $valiation->current_task_id = $valiation->next_task_id;
            $next = Task::nextForClaim($valiation->current_task_id, $transactFileStorage->target_actor_id);
            if (is_null($next)) {
                $valiation->next_task_id = 0;
            } else {
                $valiation->next_task_id = $next->id;
            }
            $valiation->save();
            $this->end_log();
            return true;
        } catch (\PDOException $e){
            throw $e;
        }
    }

    /*
     * prev
     */
    public function prev(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();

        	logger('PREVVVVVVVVVVVV');
        	logger($request->id);
        	logger($request);

            //請求支払確認
            $transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();
            if ($request->claim_control == 1) {
                $status=$request->facility_status_withdraw ;
            } else if ($request->claim_control == 2) {
                $status=$request->trader_status_withdraw ;
            }

            if (empty($transactConfirmations)) { // 存在しない場合
                throw new PDOException('請求支払確認データが存在しません。');
            } else {
                foreach ($transactConfirmations as $trans_confirm) {
                    $data = array();
                    if ($request->claim_control == 1) {
                        $data['facility_confirmation_status'] = $status;
                        $data['facility_confirmation_date'] = date('Y-m-d');//施設確認日
                        $data['facility_confirmation_account'] = $user->id;//施設確認アカウント
                    } else if ($request->claim_control == 2) {
                        $data['trader_confirmation_status'] = $status;
                        $data['trader_confirmation_date'] = date('Y-m-d');//業者確認日
                        $data['trader_confirmation_account'] = $user->id;//業者確認アカウント
                    }
                    $data['revert_type'] = self::REVERT_TYPE_WITHDRAW; // 取り下げ
                    $trans_confirm->update($data);
                }
            }

            \DB::commit();
            $request->session()->flash('message', '更新しました');
            $this->end_log();
            return true;
        } catch (\PDOException $e){
            \DB::rollBack();
            $this->end_log();
            return false;
        }
    }


    /*
     * prev
     */
    public function prev_old(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();
        	if (empty($request->isDetail)) {
        		// invoice取得
                $transactFileStorage = $this->transactFileStorage->find($request->id);
				$kengen = $this->transactFileStorage->getRegistPrivilegesTransact(
                    $user->id,
                    $transactFileStorage->sales_user_group_id,
                    $transactFileStorage->claim_task_status,
                    0
                );

				//請求支払確認
                $transactFileStorage->claim_task_status = $kengen[0]->status_back; // 現在のステータスをセット
                $transactFileStorage->revert_type = self::REVERT_TYPE_WITHDRAW; // 取り下げ

        		$transactFileStorage->save();
        	} else {

				//請求支払確認
				$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();
				$kengen= $this->transactFileStorage->getRegistPrivilegesTransact(
                    $user->id,
                    $transactConfirmations[0]->sales_user_group_id,
                    $transactConfirmations[0]->trader_confirmation_status,
                    1
                );
				$staus=$kengen[0]->status_back;

				if (empty($transactConfirmations)) { // 存在しない場合
					throw new PDOException('請求支払確認データが存在しません。');
				} else {
					foreach($transactConfirmations as $trans_confirm) {
						$data = array();
						$data['trader_confirmation_status'] = $staus;//
						$data['revert_type'] = self::REVERT_TYPE_WITHDRAW; // 取り下げ
						$trans_confirm->update($data);
					}
				}
        	}
            \DB::commit();
            $this->end_log();
            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            $this->end_log();
            return false;
        }
    }

    /*
     * 差し戻し処理
     */
    public function remand(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            //請求支払確認
            $transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();

            //差し戻し後のステータス取得
            if ($request->claim_control == 1) {
                $remandStatus=$request->facility_status_remand;
            } else if ($request->claim_control == 2) {
                $remandStatus=$request->trader_status_remand;
            }

            if (empty($transactConfirmations)) { // 存在しない場合
                throw new PDOException('請求支払確認データが存在しません。');
            } else {
                foreach ($transactConfirmations as $trans_confirm) {
                    $data = array();
                    if ($request->claim_control == 1) {
                        $data['facility_confirmation_status'] = $remandStatus;
                        $data['facility_confirmation_date'] = date('Y-m-d');//施設確認日
                        $data['facility_confirmation_account'] = $user->id;//施設確認アカウント
                    } else if ($request->claim_control == 2) {
                        $data['trader_confirmation_status'] = $remandStatus;
                        $data['trader_confirmation_date'] = date('Y-m-d');//業者確認日
                        $data['trader_confirmation_account'] = $user->id;//業者確認アカウント
                    }
                    $data['reject_comment'] = $request->reject_comment;//
                    $data['revert_type'] = self::REVERT_TYPE_REMAND; // 差し戻し
                    $trans_confirm->update($data);
                }
            }
    		$mail_users = $this->transactFileStorage->getEmailClaimDetail(
                $transactConfirmations[0]->sales_user_group_id,
                $transactConfirmations[0]->purchase_user_group_id,
                $transactConfirmations[0]->facility_confirmation_status,
                $transactConfirmations[0]->trader_confirmation_status,
                $request->claim_control
            );
    		$mq = new MailQueService($this->mailQue->getMailQueID());

            $userGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->sales_user_group_id);
            $traderUserGroup = $this->userGroup->getUserGroup($transactConfirmations[0]->purchase_user_group_id);
            $status ="";
    	    if ($request->claim_control == 1) {
            	$status = $transactConfirmations[0]->facility_confirmation_status;
            } else if($request->claim_control == 2) {
            	$status = $transactConfirmations[0]->trader_confirmation_status;
            }

            $mq->send_mail_add_claim_detail(
                $mail_users,
                $userGroup[0]->name,
                $status,
                $transactConfirmations[0]->claim_month,
                $request->claim_control,
                $traderUserGroup[0]->name,
                $transactConfirmations[0]->transact_header_id
            );
            
            \DB::commit();
            $request->session()->flash('message', '更新しました');

        } catch (\PDOException $e) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }


    /*
     * 差し戻し処理
     */
    public function remand_old(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();
        	if (empty($request->isDetail)) {
                $tfs = $this->transactFileStorage->find($request->id);

        		$kengen = $this->transactFileStorage->getRegistPrivilegesTransact($user->id, $tfs->sales_user_group_id, $tfs->claim_task_status, 0);
                $tmp_claim_task_status = $tfs->claim_task_status;
                $tfs->claim_task_status = $kengen[0]->status_back;
                $tfs->revert_type = self::REVERT_TYPE_REMAND; // 差し戻し
                $tfs->save();

                $mail_users = $this->transactFileStorage->getEmailClaim($tfs->claim_task_status,$tfs->sales_user_group_id, 0);
        		$mq = new MailQueService($this->mailQue->getMailQueID());
                $userGroup = $this->userGroup->getUserGroup($tfs->sales_user_group_id);
        		foreach ($mail_users as $mail_user) {
                    $mq->send_mail_add_claim($mail_user->email, $userGroup[0]->id, $userGroup[0]->name, $tmp_claim_task_status, $tfs->claim_month,1,true);
                }
        	} else {
        		//請求支払確認
        		$transactConfirmations = $this->transactConfirmation->where("id",$request->id)->get();

        		//差し戻し後のステータス取得
        		$kengen= $this->transactFileStorage->getRegistPrivilegesTransact($user->id,$transactConfirmations[0]->sales_user_group_id,$transactConfirmations[0]->trader_confirmation_status,1);
        		$remandStatus=$kengen[0]->status_back;

        		if (empty($transactConfirmations)) { // 存在しない場合
        			throw new PDOException('請求支払確認データが存在しません。');
        		} else {
        			foreach($transactConfirmations as $trans_confirm) {
        				$data = array();
        				$data['trader_confirmation_status'] = $remandStatus;//
        				$data['reject_comment'] = $request->reject_comment;//
        				$data['revert_type'] = self::REVERT_TYPE_REMAND; // 差し戻し
        				$trans_confirm->update($data);
        			}
        		}
        	}

            \DB::commit();
            $request->session()->flash('message', '更新しました');

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

     /*
     * 却下処理
     */
    public function reject(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $tfs = $this->transactFileStorage->find($request->id);

            $tmp_claim_task_status = $tfs->claim_task_status;
            $tfs->claim_task_status = $request->status_reject;
            $tfs->revert_type = self::REVERT_TYPE_REJECT; // 却下
            $tfs->save();

            $mail_users = $this->transactFileStorage->getEmailClaimReject($tfs->id);
        	$mq = new MailQueService($this->mailQue->getMailQueID());
            $userGroup = $this->userGroup->find($tfs->user_group_id);
            $userGroupDataType = $this->userGroupDataType->find($tfs->data_type);

        	// foreach($mail_users as $mail_user) {
            //     $mq->send_mail_add_claim(
            //         $mail_user->email,
            //         $userGroup,
            //         $userGroupDataType,
            //         $tfs,
            //         1
            //     );
            // }
            $mq->send_mail_add_claim(
                $mail_users,
                $userGroup,
                $userGroupDataType,
                $tfs,
                1,
                true
            );

            \DB::commit();
            $request->session()->flash('message', '削除しました');

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 更新
     */
    public function edit(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            foreach ($request->sales_comment as $key => $value) {
                $transactDetail = $this->transactDetail->find($key);
                $transactDetail->sales_comment = $value;
                $transactDetail->save();
            }
            if (!empty($request->buy_comment)) {
                foreach ($request->buy_comment as $key => $value) {
                    $transactDetail = $this->transactDetail->find($key);
                    $transactDetail->buy_comment = $value;
                    $transactDetail->save();
                }
            }
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            $this->end_log();
            return true;

        } catch (\PDOException $e) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \Log::debug($e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
    }


    /*
     * 採用一覧のボタンの情報を返す
     * $apply_payment_flg 3:請求登録実績、4：請求照会業者別、5：請求照会施設別
     * $claim_control 0:未設定、1:施設、2:業者
     *
     */
    public function button($claim, $apply_payment_flg, $claim_control) {
    	// 請求登録履歴は下記でOKそう
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => '',
            'style' => ''
            ];

        if ($apply_payment_flg == '3') {
        	// 請求登録実績のステータス遷移は却下のみの為
            if ($claim->status_forward > 0) {
                $button_data = $this->actionNameStatusForward
                    ->where('apply_payment_flg', $apply_payment_flg )
                    ->where('status_forward', $claim->status_forward)
                    ->where('deleted_at', null)
                    ->first();
    	        if (!empty($button_data)) {
    	       	    $property = [
    	    		   'url' => route($button_data->url,['id' => $claim->id]),
    	    		   'label' => $claim->action_name_foward,
    	    	       'style' => $button_data->style
    	    	    ];
    	        }
    	    }
        } else {
        	// 請求照会のステータス遷移は取り下げがあるのと業者と施設２つのステータスを更新する
        	if ($claim_control == '1') {
                if ($claim->facility_status_foward > 0) {
                    $button_data = $this->actionNameStatusForward
                        ->where('apply_payment_flg', $apply_payment_flg )
                        ->where('status_forward', $claim->facility_status_foward)
                        ->where('deleted_at', null)
                        ->first();
    	            if (!empty($button_data)) {
    	       	       $property = [
    	    		      'url' => route($button_data->url,['id' => $claim->id ]),
    	    		      'label' => $claim->action_name_facility_status_foward,
    	    	          'style' => $button_data->style
    	    	       ];
    	            }

    	        }
        	} else if($claim_control == '2') {
        	    if ($claim->trader_status_foward > 0) {
                    $button_data = $this->actionNameStatusForward
                        ->where('apply_payment_flg', $apply_payment_flg )
                        ->where('status_forward', $claim->trader_status_foward)
                        ->where('deleted_at', null)
                        ->first();
    	            if (!empty($button_data)) {
    	       	       $property = [
    	    		      'url' => route($button_data->url,['id' => $claim->id ]),
    	    		      'label' => $claim->action_name_trader_status_foward,
    	    	          'style' => $button_data->style
    	    	       ];
    	            }
    	        }
        	}
        }
        return $property;
    }

    /*
     * 採用一覧のボタンの情報を返す
     */
    public function button_old($claim,$isDetail=null) {
        $this->start_log();
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => '',
            'style' => ''
        ];

        if ($claim->status_forward > 0) {
        	if (isset($claim->claim_task_status) && $claim->claim_task_status == TASK::STATUS_TRADER_CONFIRM ){
        	} else {
	        	switch ($claim->status_forward) {
	        		case TASK::STATUS_INVOICING:
	                	$property = [
                            'url' => route('claim.regexec_retry', ['id' => $claim->id]),
                            'label' => '請求登録',
                            'style' => 'btn-primary'
                        ];
	        			break;

	        		case TASK::STATUS_TRADER_CONFIRM:
	        			$property = [
                            'url' => route('claim.recept', ['id' => $claim->id]),
                            'label' => '請求受付',
                            'style' => 'btn-primary'
                        ];
	        			break;

	        		case TASK::STATUS_CLAIMED:
	        			$property = [
                            'url' => route('claim.payconf', ['id' => $claim->id,'head_id' => $claim->transact_header_id]),
                            'label' => '支払確認',
                            'style' => 'btn-danger'
                        ];
	        			break;

	        		case TASK::STATUS_CLAIMOK:
	        			$property = [
                            'url' => route('claim.confirm', ['id' => $claim->id,'head_id' => $claim->transact_header_id]),
                            'label' => '請求確認',
                            'style' => 'btn-danger'
	        			];
	        			break;

	        		case TASK::STATUS_COMPLETE:
	                	$property = [
	                        'url' => route('claim.complete', ['id' => $claim->id,'head_id' => $claim->transact_header_id]),
	                        'label' => '請求確定',
	                        'style' => 'btn-danger'
                        ];
                        break;

                    default:
	        	}
        	}
        } else if ($claim->status_back > 0) {
            if (isset($claim->claim_task_status) && $claim->claim_task_status == TASK::STATUS_TRADER_CONFIRM ) {
            } else {
                $property = [
                    'url' => route('claim.withdraw',['id' => $claim->id ]),
                    'label' => $claim->status_back_text,
                    'style' => 'btn-secondary'
                ];
            }
        } else {
            $this->end_log();
        	return;
        }
        $this->end_log();
        return $property;
    }

    /*
     * 取り下げボタンの情報を返す
     * $claim_control 0:未設定、1:施設、2:業者
     */
    public function buttonWithdraw($claim, $claim_control) {
        $this->start_log();
    	$user = Auth::user();
    	$property = [
            'url' => null,
            'label' => '',
            'style' => ''
    	];

    	if ($claim_control == 1) {
            if ($claim->facility_status_withdraw > 0) {
    		    $property = [
    				'url' => route('claim.withdraw',['id' => $claim->id ]),
    				'label' => '取り下げ',
    		    	'style' => 'btn-secondary'
    		    ];
    	    }
    	} else if ($claim_control == 2) {
    	    if ($claim->trader_status_withdraw > 0) {
    		    $property = [
    				'url' => route('claim.withdraw',['id' => $claim->id ]),
    				'label' => '取り下げ',
    		    	'style' => 'btn-secondary'
    		    ];
    	    }
    	}
        $this->end_log();
    	return $property;
    }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     */
    public function buttonWithdraw_old($claim) {
        $this->start_log();
    	$user = Auth::user();
    	$property = [
            'url' => null,
            'label' => '',
            'style' => ''
    	];

    	if ($claim->withdraw_status > 0) {
    	    if (isset($claim->claim_task_status) && $claim->claim_task_status == TASK::STATUS_TRADER_CONFIRM ){
            } else {
            	if (isset($claim->trader_confirmation_status) && $claim->trader_confirmation_status == TASK::STATUS_TRADER_CLAIMED ) {
            		$property = [
                        'url' => route('claim.delete_detail',['id' => $claim->id ]),
                        'label' => '明細削除',
                        'style' => 'btn-secondary'
            		];
            	} else {
	            	$property = [
                        'url' => route('claim.withdraw',['id' => $claim->id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'
	            	];
            	}
            }
    	}
        $this->end_log();
    	return $property;
    }

    /*
     * 請求照会の差し戻しボタンの情報を返す
     */
    public function buttonRemand($claim)
    {
        $this->start_log();
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => '',
        	'comment' => ''
        ];

        if ($claim->reject_status > 0) {
        	if (isset($claim->claim_task_status) && $claim->claim_task_status == TASK::STATUS_TRADER_CONFIRM) {
        	} else {
        		if (isset($claim->trader_confirmation_status) && $claim->trader_confirmation_status == TASK::STATUS_TRADER_CLAIMED) {
        		} else {
		        	$property = [
                        'url' => route('claim.remand',['id' => $claim->id ]),
                        'label' => '差し戻し',
                        'comment' => '1'
		        	];
        		}
        	}
        }
        $this->end_log();
        return $property;
    }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     */
    public function buttonRemand_new_old($claim)
    {
        $this->start_log();
    	$user = Auth::user();
    	$property = [
            'url' => null,
            'label' => '',
            'comment' => ''
    	];

    	if ($claim->status_back > 0 && $claim->status_forward > 0) {
    		if (isset($claim->claim_task_status) && $claim->claim_task_status == TASK::STATUS_TRADER_CONFIRM ) {
                $property = [
                    'url' => route('claim.remand',['id' => $claim->id ]),
                    'label' => $claim->status_back_text,
                    'comment' => ''
                ];
    		} else {
    			if (isset($claim->trader_confirmation_status) && $claim->trader_confirmation_status == TASK::STATUS_TRADER_CLAIMED ) {
    			} else {
    				$property = [
                        'url' => route('claim.remand',['id' => $claim->id ]),
                        'label' => $claim->status_back_text,
                        'comment' => '1'
    				];
    			}
    		}
    	}
        $this->end_log();
    	return $property;
    }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     * $claim_control 0:未設定、1:施設、2:業者
     */
    public function buttonRemand_new($claim, $claim_control) {
        $this->start_log();
    	$user = Auth::user();
    	$property = [
            'url' => null,
            'label' => '',
            'comment' => ''
    	];

    	if ($claim_control == 1) {
            if ($claim->facility_status_remand > 0) {
    		    $property = [
    				'url' => route('claim.remand',['id' => $claim->id ]),
    				'label' => '差し戻し',
    				'comment' => ''
    		    ];
    	    }
    	} else if ($claim_control == 2) {
    	    if ($claim->trader_status_remand > 0) {
    		    $property = [
    				'url' => route('claim.remand',['id' => $claim->id ]),
    				'label' => '差し戻し',
    				'comment' => ''
    		    ];
    	    }
    	}
        $this->end_log();
    	return $property;
    }

    /*
     * 却下処理(請求登録履歴のみ)
     */
    public function buttonReject($claim) {
        $this->start_log();
    	$user = Auth::user();
    	$property = [
            'url' => null,
            'label' => '',
            'comment' => ''
    	];

    	if ($claim->status_reject > 0) {
    		$property = [
                'url' => route('claim.reject',['id' => $claim->id ]),
                'label' => $claim->action_name_reject,
                'comment' => ''
    		];
    	}
        $this->end_log();
    	return $property;
    }

    /*
     * ステータス一覧
     */
    public function getTasks_old() {
        $this->start_log();
        $this->end_log();
        return Task::STATUS_STR2;
    }

 /*
     * ステータス一覧
     */
    public function getTasks($apply_payment_flg) {
        $this->start_log();
        $this->end_log();
        return $this->tasks->getStatusClaimList($apply_payment_flg);
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function getConditions(Request $request) {
        $this->start_log();
        // 検索条件
        $conditions = array();
        $conditions['status'] = array();
        $conditions['facility'] = array();
        $conditions['supply_division'] = '';
        $conditions['claim_month_from'] = '';
        $conditions['claim_month_to'] = '';
        $conditions['user_group'] = array();
        $is_cond = false;

        //検索実行の場合
        if (!empty($request->is_search)) {
            // 状態
            if (!empty($request->status)) {
                $conditions['status'] = $request->status;
                $is_cond = true;
            } else {
                $conditions['status'] = array();
            }
        	if (!empty($request->user_group)) {
        		$conditions['user_group'] = $request->user_group;
        		$is_cond = true;
        	} else {
        		$conditions['user_group'] = array();
        	}
            // 対象年月
            if (!empty($request->claim_month_from)) {
                $conditions['claim_month_from'] = $request->claim_month_from;
                $is_cond = true;
            }
            if (!empty($request->claim_month_to)) {
            	$conditions['claim_month_to'] = $request->claim_month_to;
            	$is_cond = true;
            }

            // 供給区分
            if (!empty($request->supply_division)) {
                $conditions['supply_division'] = $request->supply_division;
                $is_cond = true;
            }
            $conditions['is_condition'] = $is_cond;
            session()->put(['claim_conditions' => $conditions]);

        //サイドバーからの遷移
        } else if(!empty($request->initial)) {
        	// TODO 施設
        	$request->user_group = $conditions['user_group'];
            $request->claim_month_from = $conditions['claim_month_from'];
            $request->claim_month_to = $conditions['claim_month_to'];

            $conditions['is_condition'] = $is_cond;
            session()->put(['claim_regist_conditions' => $conditions]);
        //サイドバーからの遷移
        } else if(!empty($request->reginitial)) {
            $request->user_group = $conditions['user_group'];
            $request->claim_month_from = $conditions['claim_month_from'];
            $request->claim_month_to = $conditions['claim_month_to'];

            $conditions['is_condition'] = $is_cond;
            session()->put(['claim_conditions' => $conditions]);
            //登録履歴検索
        } else if(!empty($request->is_regist_search)) {
        	// 状態
        	if (!empty($request->status)) {
        		$conditions['status'] = $request->status;
        		$is_cond = true;
        	} else {
        		$conditions['status'] = array();
        	}
        	// 施設
        	if (!empty($request->user_group)) {
        		$conditions['user_group'] = $request->user_group;
        		$is_cond = true;
        	} else {
        		$conditions['user_group'] = array();
        	}
        	// 対象年月
        	if (!empty($request->claim_month_from)) {
        		$conditions['claim_month_from'] = $request->claim_month_from;
        		$is_cond = true;
        	}
        	// 対象年月
        	if (!empty($request->claim_month_to)) {
        		$conditions['claim_month_to'] = $request->claim_month_to;
        		$is_cond = true;
        	}

        	// 供給区分
        	if (!empty($request->supply_division)) {
        		$conditions['supply_division'] = $request->supply_division;
        		$is_cond = true;
        	}
        	$conditions['is_condition'] = $is_cond;
        	session()->put(['claim_regist_conditions' => $conditions]);

        } else {
            if (!empty(session()->get('claim_conditions'))) {
                $conditions = session()->get('claim_conditions');

                //リクエストに値を設定する。
                // 状態
                if (!empty($conditions['status'])) {
                    $request->status = $conditions['status'];
                } else {
                    $request->status = array();
                }
                if (!empty($conditions['user_group'])) {
                    $request->user_group = $conditions['user_group'];
                } else {
                    $request->user_group = array();
                }

                // 対象年月
                if (!empty($conditions['claim_month_from'])) {
                    $request->claim_month_from = $conditions['claim_month_from'];
                }
                // 対象年月
                if (!empty($conditions['claim_month_to'])) {
                    $request->claim_month_to = $conditions['claim_month_to'];
                }
                // 供給区分
                if (!empty($conditions['supply_division'])) {
                    $request->supply_division = $conditions['supply_division'];
                }
            }
        }
        $this->end_log();
        return $conditions;
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function isInitial(Request $request) {
        $this->start_log();
    	$status = array();

        if (isBunkaren()) {
            $status = array(
                Task::STATUS_INVOICING,
                Task::STATUS_CONFIRM,
                Task::STATUS_CLAIMED,
                Task::STATUS_COMPLETE
            );
        } elseif (isHeadQuqrters()) {
            $status = array();
        } elseif (isHospital()) {
            $status = array(
                Task::STATUS_INVOICING,
                Task::STATUS_CONFIRM,
                Task::STATUS_CLAIMED
            );
        } elseif(isKojin()) {
            $status = array(
                Task::STATUS_INVOICING,
                Task::STATUS_CONFIRM,
                Task::STATUS_CLAIMED
            );
        }
        $this->end_log();
        return $status;
    }


     /*
     * 明細別明細ヘッダー
     */
    public function getMeisaiHeader($transact_header_id, $user, Request $request) {
        $this->start_log();
        $detail = $this->transactConfirmation->getDetailHeader($transact_header_id, $user, $request->id);
        // $detail->buttonFacility = $this->button($detail, 5, 1);
        $detail->buttonTrader = $this->button($detail, 4, 2);
        // $detail->buttonWithdrawFacility = $this->buttonWithdraw($detail, 1);
        $detail->buttonWithdrawTrader = $this->buttonWithdraw($detail, 2);
        // $detail->buttonRemandFacility = $this->buttonRemand_new($detail, 1);
        $detail->buttonRemandTrader = $this->buttonRemand_new($detail, 2);

        // $detail->edit = true;
        $detail->flg = $request->flg;
        $detail->page = "";
        if (!empty($request->page)) {
            $detail->page = $request->page;
        }
        $this->end_log();
        return $detail;
    }

    /*
     * 詳細取得
     */
    public function getClaimDetail(Request $request) {
        $this->start_log();
    	$user = Auth::user();
    	//TODO 今回の修正でConfirmationのSQLと入れ替える
        $detail = $this->transactConfirmation->getDetailHeader($request->id, $user);
        $detail->flg = $request->flg;
        if (!empty($request->page)) {
            $detail->page = $request->page;
        }
        $this->end_log();
        return $detail;
    }

    /*
     * 明細一覧取得
     */
    public function getTransactDetailsList(Request $request) {
        $this->start_log();
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;

        $user = Auth::user();
        $list = $this->transactDetail->getListClaimDetail($request, $user, $count);
        $this->end_log();
        return $list;
    }

    /*
     * 請求一覧SQL取得(明細別明細)
    */
    public function getDetailListForSQL(Request $request, $user, $count) {
        $this->start_log();
    	$sql =  $this->transactDetail->getListClaimDetailSql($request, $user, $count);

    	$csv_data = array();
    	$csv_row=array();

    	$csv_row['SQL'] = $sql;//SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = [ '--明細別明細SQL' ];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }


    /*
     * コメント登録実行
     */
    public function registComment(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            //コメントの登録
            $this->claimHistoryComment->trading_history_id = $this->claimHistoryComment->getCommentID();//取引履歴ID
            $this->claimHistoryComment->described_user_id = $user->id;//ユーザ参照ID
            $this->claimHistoryComment->comment = $request->comment;//コメント
            $this->claimHistoryComment->transact_confirmation_id = $request->tc_id;//請求支払確認参照ID
            $this->claimHistoryComment->save();

            \DB::commit();
            $this->end_log();
            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            \Log::debug($e->getMessage());
            $this->end_log();
            return false;
        }
    }


    /*
     * 請求照会(業者別) 施設アップロード
     */
    public function sendh_upload(Request $request) {
        $this->start_log();
    	if ($request->hasFile('send_file')) {
    	} else {
            $request->session()->flash('errorMessage', 'ファイルが選択されていません');
            $this->end_log();
    		return false;
    	}
        \DB::beginTransaction();
        try {
            $user = Auth::user();

               $file_name = $request->file('send_file')->getClientOriginalName();
               $thr = $this->transactHeader->find($request->id);
               $thr->attachment_send = encodeByteaData($request->file('send_file')); //添付ファイル
               $thr->attachment_send_file_name = $file_name;
               $thr->attachment_send_updater = $user->id;
               $thr->attachment_send_updated_at = date('Y-m-d H:i:s');
               $thr->save();

               \DB::commit();
               $request->session()->flash('message', 'ファイルアップロードしました');


        } catch (\PDOException $e){
        	logger('UPLOADERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 請求照会(業者別) 押印済み施設アップロード
     */
    public function receiveh_upload(Request $request) {
        $this->start_log();
        if ($request->hasFile('receive_file')) {
    	} else {
            $request->session()->flash('errorMessage', 'ファイルが選択されていません');
            $this->end_log();
    		return false;
    	}
        \DB::beginTransaction();
        try {
            $user = Auth::user();

              $file_name = $request->file('receive_file')->getClientOriginalName();
              $thr = $this->transactHeader->find($request->id);
              $thr->attachment_receive = encodeByteaData($request->file('receive_file')); //添付ファイル
              $thr->attachment_receive_file_name = $file_name;
              $thr->attachment_receive_updater = $user->id;
              $thr->attachment_receive_updated_at = date('Y-m-d H:i:s');
              $thr->save();

              \DB::commit();
              $request->session()->flash('message', 'ファイルアップロードしました');


        } catch (\PDOException $e) {
        	logger('UPLOADERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 請求照会(業者別) 施設アップロード削除
     */
    public function sendh_delete(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();

            $thr = $this->transactHeader->find($request->id);
            $thr->attachment_send = NULL;
            $thr->attachment_send_file_name = NULL;
            $thr->attachment_send_updater = $user->id;
            $thr->attachment_send_updated_at = date('Y-m-d H:i:s');
            $thr->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルを削除しました');
        } catch (\PDOException $e) {
        	logger('DELETEERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 請求照会(業者別) 押印済み施設アップロード削除
     */
    public function receiveh_delete(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();

            $thr = $this->transactHeader->find($request->id);
            $thr->attachment_receive = NULL;
            $thr->attachment_receive_file_name = NULL;
            $thr->attachment_receive_updater = $user->id;
            $thr->attachment_receive_updated_at = date('Y-m-d H:i:s');
            $thr->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルを削除しました');
        } catch (\PDOException $e){
        	logger('DELETEERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 請求照会(業者別) 施設ダウンロード
     */
    public function sendh_download(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $transactHeader = $this->transactHeader->find($request->id);

        //ダウンロード
        $type = getMimeType( pathinfo($transactHeader->attachment_send_file_name, PATHINFO_EXTENSION));
        $stream = decodeByteaData($transactHeader->attachment_send);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode($transactHeader->attachment_send_file_name),
        );
        $this->end_log();
        return Response::make($stream, 200, $headers);
    }

    /*
     * 請求照会(業者別) 押印済み施設ダウンロード
     */
    public function receiveh_download(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $transactHeader = $this->transactHeader->find($request->id);

        //ダウンロード
        $type = getMimeType( pathinfo($transactHeader->attachment_receive_file_name, PATHINFO_EXTENSION));
        $stream = decodeByteaData($transactHeader->attachment_receive);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode($transactHeader->attachment_receive_file_name),
        );
        $this->end_log();
        return Response::make($stream, 200, $headers);
    }


    /*
     * 請求照会(明細別) 業者アップロード
     */
    public function send_upload(Request $request) {
        $this->start_log();
        if ($request->hasFile('send_file')) {
    	} else {
            $request->session()->flash('errorMessage', 'ファイルが選択されていません');
            $this->end_log();
    		return false;
    	}
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $file_name = $request->file('send_file')->getClientOriginalName();
            $trc = $this->transactConfirmation->find($request->id);
            $trc->attachment_send = encodeByteaData($request->file('send_file')); //添付ファイル
            $trc->attachment_send_file_name = $file_name;
            $trc->attachment_send_updater = $user->id;
            $trc->attachment_send_updated_at = date('Y-m-d H:i:s');
            $trc->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルアップロードしました');
        } catch (\PDOException $e) {
        	logger('UPLOADERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * 請求照会(明細別) 押印済み業者アップロード
     */
    public function receive_upload(Request $request) {
        $this->start_log();
        if ($request->hasFile('receive_file')) {
    	} else {
            $request->session()->flash('errorMessage', 'ファイルが選択されていません');
            $this->end_log();
    		return false;
    	}
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $file_name = $request->file('receive_file')->getClientOriginalName();
            $trc = $this->transactConfirmation->find($request->id);
            $trc->attachment_receive = encodeByteaData($request->file('receive_file')); //添付ファイル
            $trc->attachment_receive_file_name = $file_name;
            $trc->attachment_receive_updater = $user->id;
            $trc->attachment_receive_updated_at = date('Y-m-d H:i:s');
            $trc->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルアップロードしました');

        } catch (\PDOException $e) {
        	logger('UPLOADERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

     /*
     * 請求照会(明細別) 業者アップロード削除
     */
    public function send_delete(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();

            $trc = $this->transactConfirmation->find($request->id);
            $trc->attachment_send = NULL;
            $trc->attachment_send_file_name = NULL;
            $trc->attachment_send_updater = $user->id;
            $trc->attachment_send_updated_at = date('Y-m-d H:i:s');
            $trc->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルを削除しました');
        } catch (\PDOException $e) {
        	logger('DELETEERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }

     /*
     * 請求照会(明細別) 押印済み業者アップロード削除
     */
    public function receive_delete(Request $request) {
        $this->start_log();
        \DB::beginTransaction();
        try {
        	$user = Auth::user();

            $trc = $this->transactConfirmation->find($request->id);
            $trc->attachment_receive = NULL;
            $trc->attachment_receive_file_name = NULL;
            $trc->attachment_receive_updater = $user->id;
            $trc->attachment_receive_updated_at = date('Y-m-d H:i:s');
            $trc->save();

            \DB::commit();
            $request->session()->flash('message', 'ファイルを削除しました');

        } catch (\PDOException $e) {
        	logger('DELETEERROR');
        	logger($e->getMessage());
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            $this->end_log();
            return false;
        }
        $this->end_log();
        return true;
    }


    /*
     * 請求照会(明細別) 業者ダウンロード
     */
    public function send_download(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $transactConfirmation = $this->transactConfirmation->find($request->id);

        //ダウンロード
        $type = getMimeType( pathinfo($transactConfirmation->attachment_send_file_name, PATHINFO_EXTENSION));
        $stream = decodeByteaData($transactConfirmation->attachment_send);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode($transactConfirmation->attachment_send_file_name),
        );
        $this->end_log();
        return Response::make($stream, 200, $headers);
    }

    /*
     * 請求照会(明細別) 押印済み業者ダウンロード
     */
    public function receive_download(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $transactConfirmation = $this->transactConfirmation->find($request->id);

        //ダウンロード
        $type = getMimeType( pathinfo($transactConfirmation->attachment_receive_file_name, PATHINFO_EXTENSION));
        $stream = decodeByteaData($transactConfirmation->attachment_receive);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode($transactConfirmation->attachment_receive_file_name),
        );
        $this->end_log();
        return Response::make($stream, 200, $headers);
    }

    /*
     * ダウンロード
     */
    public function download(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $transactFileStorage = $this->transactFileStorage->find($request->id);

        //ダウンロード
        $type = getMimeType( pathinfo($transactFileStorage->file_name, PATHINFO_EXTENSION));
        $stream = decodeByteaData($transactFileStorage->attachment);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode($transactFileStorage->file_name),
        );

        $this->end_log();
        return Response::make($stream, 200, $headers);
    }

    /*
     * 明細ダウンロード
     */
    public function downloadDetail(Request $request, $user) {
        $this->start_log();

    	$baseData = array();

        $baseData=$this->transactDetail->getListCsv($request, $user);
        $result = $this->makeDetailDownloadData($baseData);
        $this->end_log();
    	return $result;
    }

     /*
     * 売上価格の権限判定
     */
    public function getPrivilegeSalesPrice($sales_price, $show_sales_price) {
        $this->start_log();

        $sql  = "";
    	$sql .= " select ";
    	$sql .= "      f_show_sales_price_claim as f_show_sales_price_claim";
    	$sql .= " from ";
    	$sql .= "     f_show_sales_price_claim(".$sales_price."," .$show_sales_price.")";

    	$all_rec = \DB::select($sql);
        $this->end_log();
    	return $all_rec;
    }

    /*
     * 仕入価格の権限判定
     */
    public function getPrivilegePurchasePrice($purchase_price, $show_purchase_price) {
        $this->start_log();
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      f_show_purchase_price as f_show_purchase_price ";
    	$sql .= " from ";
    	$sql .= "     f_show_purchase_price(". $purchase_price . "," . $show_purchase_price . ")";

    	$all_rec = \DB::select($sql);
        $this->end_log();
    	return $all_rec;
    }


    function makeDetailDownloadData($baseData) {
        $this->start_log();
    	$csv_data = array();
    	$csv_row=array();

    	$user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        $purchase_kengen = $kengen["請求登録_仕入価枠表示"]->privilege_value == "true";
        $sales_kengen = $kengen["請求登録_納入価枠表示"]->privilege_value == "true";

    	try {
    		foreach($baseData as $key => $transactDetail) {
    			$csv_row['facility_name']                   = trim($transactDetail->facility_name);// 施設
    			$csv_row['trader_name']                     = trim($transactDetail->trader_name); // 業者
    			$csv_row['supply_division_name']            = trim($transactDetail->supply_division_name) ;// 供給区分
    			$csv_row['claim_date']                      = trim($transactDetail->claim_date); // 取引年月日
    			$csv_row['bunkaren_trading_history_code']   = trim($transactDetail->bunkaren_trading_history_code); //文化連取引履歴ID
    			// 条件
    			if ($sales_kengen) {
    				if ($transactDetail->show_sales_price) {
    			       $csv_row['sales_slip_number']   = trim($transactDetail->sales_slip_number); //売上伝票番号
    				} else {
    				   $csv_row['sales_slip_number']   = "";
    				}
    		    }
    			// 条件
    		    if ($purchase_kengen) {
    		    	if ($transactDetail->show_purchase_price) {
    			       $csv_row['buy_slip_number']   = trim($transactDetail->buy_slip_number); //仕入伝票番号
    		    	} else {
    		    	   $csv_row['buy_slip_number']   = "";
    		    	}
    		    }

    			$csv_row['bunkaren_item_code']   = trim($transactDetail->bunkaren_item_code); //文化連商品CD
    			$csv_row['jan_code']                = $transactDetail->jan_code; //JAN
    			$csv_row['gtin_code']               = $transactDetail->gtin_code; //GTIN
    			$csv_row['gs1_sscc']                = $transactDetail->gs1_sscc; //GS1_SSCC
    			$csv_row['maker_name']   = trim($transactDetail->maker_name); //販売メーカー
    			$csv_row['item_name']   = trim($transactDetail->item_name); //商品名
    			$csv_row['standard']   = trim($transactDetail->standard); //規格
    			$csv_row['item_code']   = trim($transactDetail->item_code); //製品番号
    			$csv_row['quantity']   = trim($transactDetail->quantity); //数量
    			$csv_row['unit_name']   = trim($transactDetail->unit_name); //単位名
    			$csv_row['refund_price']            = $transactDetail->refund_price; //償還価格
    			if ($sales_kengen) {
    				if ($transactDetail->show_sales_price) {
			    	    $csv_row['sales_unit_price']   = $transactDetail->sales_unit_price; //売上単価
			    	    $csv_row['sales_price']   = $transactDetail->sales_price; //売上金額
			    	    $csv_row['sales_comment']   = $transactDetail->sales_comment; //売上備考
    				} else {
    					$csv_row['sales_unit_price']   = ""; //売上単価
			    	    $csv_row['sales_price']   = ""; //売上金額
			    	    $csv_row['sales_comment']   = ""; //売上備考
    				}
			    }
			    if ($purchase_kengen) {
			    	if ($transactDetail->show_purchase_price) {
			    	   $csv_row['buy_unit_price']   = $transactDetail->buy_unit_price; //仕入単価
			    	   $csv_row['buy_price']        = $transactDetail->buy_price; //仕入金額
			    	   $csv_row['buy_comment']      = $transactDetail->buy_comment; //仕入備考
			    	} else {
			    	   $csv_row['buy_unit_price']   = ""; //仕入単価
			    	   $csv_row['buy_price']        = ""; //仕入金額
			    	   $csv_row['buy_comment']      = ""; //仕入備考

			    	}
			    }
			    $csv_row['tax_division']            = \App\Helpers\Claim\getTaxDivisionText($transactDetail->tax_division); //消費税率区分
			    $csv_row['tax_rate']                = $transactDetail->tax_rate.'%'; //消費税率
			    $csv_row['department_name']         = $transactDetail->department_name; //部署名
			    if ($sales_kengen) {
    				if ($transactDetail->show_sales_price) {
			            $csv_row['facility_item_code']      = $transactDetail->facility_item_code; //施設商品ID
    				} else {
    					$csv_row['facility_item_code']      = ""; //施設商品ID
    				}
			    }
			    if ($purchase_kengen) {
			    	if ($transactDetail->show_purchase_price) {
			            $csv_row['trader_item_code']        = $transactDetail->trader_item_code; //業者商品ID
			    	} else {
			            $csv_row['trader_item_code']        = ""; //業者商品ID
			    	}
			    }

    			$csv_data[] = $csv_row;
    		}
    	} catch (Exception $e){
    		$a=1;
    	}

    	$csvHeader=array();
    	$csvHeader[] = '施設';
    	$csvHeader[] = '業者';
    	$csvHeader[] = '供給区分';
    	$csvHeader[] = '取引年月日';
    	$csvHeader[] = '文化連取引履歴ID';
    	if ($sales_kengen) {
    	    $csvHeader[] = '売上伝票番号';
    	}
    	if ($purchase_kengen) {
    	    $csvHeader[] = '仕入伝票番号';
    	}
    	$csvHeader[] = '文化連商品CD';
    	$csvHeader[] = 'JANCD';
    	$csvHeader[] = 'GTIN';
    	$csvHeader[] = 'GS1_SSCC';
    	$csvHeader[] = '販売メーカー';
    	$csvHeader[] = '商品名';
    	$csvHeader[] = '規格';
    	$csvHeader[] = '製品番号';
    	$csvHeader[] = '数量';
    	$csvHeader[] = '単位名';
    	$csvHeader[] = '償還価格';
    	if ($sales_kengen) {
    		$csvHeader[] = '売上単価';
    		$csvHeader[] = '売上金額';
    		$csvHeader[] = '売上備考';
    	}
    	if ($purchase_kengen) {
	    	$csvHeader[] = '仕入単価';
	    	$csvHeader[] = '仕入金額';
	    	$csvHeader[] = '仕入備考';
		}
    	$csvHeader[] = '消費税率区分';
    	$csvHeader[] = '消費税率';
    	$csvHeader[] = '部署名';
    	if ($sales_kengen) {
    	    $csvHeader[] = '施設商品ID';
    	}
    	if ($purchase_kengen) {
    	    $csvHeader[] = '業者商品ID';
    	}

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 明細ダウンロード
     */
    public function downloadDetail_old(Request $request, $user_id, $user_group_id) {
        $this->start_log();
    	$kengen=$this->transactFileStorage->getPrivilegesTransact($user_id,$user_group_id);

    	$baseData;
    	if (!empty($request->transact_header_id)) {
    		$baseData=$this->transactDetail->getListByHeaderId($request->transact_header_id);
    	} else if (!empty($request->transact_confirmation_id)) {
    		$baseData=$this->transactDetail->getList($request->transact_confirmation_id);
    	}
        $this->end_log();
    	return $this->makeDetailDownloadData($baseData,$kengen);
    }

    function makeDetailDownloadData_old($baseData) {
    	$csv_data = array();
    	$csv_row=array();

    	try {
    		foreach($baseData as $key => $transactDetail) {
    			$csv_row['claim_date']                      = trim($transactDetail->claim_date); // 取引年月日
    			$csv_row['is_stock_or_sale']                = \App\Helpers\Claim\getStockOrSaleDivisionText($transactDetail->is_stock_or_sale); //売仕区分
    			$csv_row['bunkaren_trading_history_code']   = trim($transactDetail->bunkaren_trading_history_code); //文化連取引履歴ID
    			$csv_row['sales_slip_number']   = trim($transactDetail->sales_slip_number); //売上伝票番号
    			$csv_row['buy_slip_number']   = trim($transactDetail->buy_slip_number); //仕入伝票番号
    			$csv_row['bunkaren_item_code']   = trim($transactDetail->bunkaren_item_code); //文化連商品CD
    			$csv_row['maker_name']   = trim($transactDetail->maker_name); //販売メーカー
    			$csv_row['item_name']   = trim($transactDetail->item_name); //商品名
    			$csv_row['standard']   = trim($transactDetail->standard); //規格
    			$csv_row['item_code']   = trim($transactDetail->item_code); //製品番号
    			$csv_row['quantity']   = trim($transactDetail->quantity); //数量
    			$csv_row['unit_name']   = trim($transactDetail->unit_name); //単位名

			    if ($kengen->show_transact_sales_columns == 1 ) {
			    	$csv_row['sales_unit_price']   = $transactDetail->sales_unit_price; //売上単価
			    	$csv_row['sales_price']   = $transactDetail->sales_price; //売上金額
			    	$csv_row['sales_comment']   = $transactDetail->sales_comment; //売上備考
			    }
			    if ($kengen->show_transact_purchase_columns == 1 ) {
			    	$csv_row['buy_unit_price']   = $transactDetail->buy_unit_price; //仕入単価
			    	$csv_row['buy_price']        = $transactDetail->buy_price; //仕入金額
			    	$csv_row['buy_comment']      = $transactDetail->buy_comment; //仕入備考
			    }
			    $csv_row['tax_division']            = \App\Helpers\Claim\getTaxDivisionText($transactDetail->tax_division); //消費税率区分
			    $csv_row['tax_rate']                = $transactDetail->tax_rate.'%'; //消費税率
			    $csv_row['refund_price']            = $transactDetail->refund_price; //償還価格
			    $csv_row['jan_code']                = $transactDetail->jan_code; //JAN
			    $csv_row['gtin_code']               = $transactDetail->gtin_code; //GTIN
			    $csv_row['department_name']         = $transactDetail->department_name; //部署名
			    $csv_row['gs1_sscc']                = $transactDetail->gs1_sscc; //GS1_SSCC
			    $csv_row['facility_item_code']      = $transactDetail->facility_item_code; //施設商品ID
			    $csv_row['trader_item_code']        = $transactDetail->trader_item_code; //業者商品ID

    			$csv_data[] = $csv_row;
    		}
    	} catch (Exception $e){
    		$a=1;
    	}

    	$csvHeader = array();
    	$csvHeader[] = '取引年月日';
    	$csvHeader[] = '売仕区分';
    	$csvHeader[] = '文化連取引履歴ID';
    	$csvHeader[] = '売上伝票番号';
    	$csvHeader[] = '仕入伝票番号';
    	$csvHeader[] = '文化連商品CD';
    	$csvHeader[] = '販売メーカー';
    	$csvHeader[] = '商品名';
    	$csvHeader[] = '規格';
    	$csvHeader[] = '製品番号';
    	$csvHeader[] = '数量';
    	$csvHeader[] = '単位名';

    	if ($kengen->show_transact_sales_columns == 1 ) {
    		$csvHeader[] = '売上単価';
    		$csvHeader[] = '売上金額';
    		$csvHeader[] = '売上備考';
    	}
		if ($kengen->show_transact_purchase_columns == 1 ) {
	    	$csvHeader[] = '仕入単価';
	    	$csvHeader[] = '仕入金額';
	    	$csvHeader[] = '仕入備考';
		}
    	$csvHeader[] = '消費税率区分';
    	$csvHeader[] = '消費税率';
    	$csvHeader[] = '償還価格';
    	$csvHeader[] = 'JAN';
    	$csvHeader[] = 'GTIN';
    	$csvHeader[] = '部署名';
    	$csvHeader[] = 'GS1_SSCC';
    	$csvHeader[] = '施設商品ID';
    	$csvHeader[] = '業者商品ID';

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    function makeDetailDownloadData_old2($baseData, $kengen) {
        $this->start_log();
    	$csv_data = array();
    	$csv_row=array();

    	try {
    		foreach($baseData as $key => $transactDetail) {
    			$csv_row['claim_date']                      = trim($transactDetail->claim_date);
    			$csv_row['is_stock_or_sale']                = \App\Helpers\Claim\getStockOrSaleDivisionText($transactDetail->is_stock_or_sale);
    			$csv_row['bunkaren_trading_history_code']   = trim($transactDetail->bunkaren_trading_history_code);
    			$csv_row['sales_slip_number']   = trim($transactDetail->sales_slip_number);
    			$csv_row['buy_slip_number']   = trim($transactDetail->buy_slip_number);
    			$csv_row['bunkaren_item_code']   = trim($transactDetail->bunkaren_item_code);
    			$csv_row['maker_name']   = trim($transactDetail->maker_name);
    			$csv_row['item_name']   = trim($transactDetail->item_name);
    			$csv_row['standard']   = trim($transactDetail->standard);
    			$csv_row['item_code']   = trim($transactDetail->item_code);
    			$csv_row['quantity']   = trim($transactDetail->quantity);
    			$csv_row['unit_name']   = trim($transactDetail->unit_name);

			    if ($kengen->show_transact_sales_columns == 1 ) {
			    	$csv_row['sales_unit_price']   = $transactDetail->sales_unit_price;
			    	$csv_row['sales_price']   = $transactDetail->sales_price;
			    	$csv_row['sales_comment']   = $transactDetail->sales_comment;
			    }
			    if ($kengen->show_transact_purchase_columns == 1 ) {
			    	$csv_row['buy_unit_price']   = $transactDetail->buy_unit_price;
			    	$csv_row['buy_price']        = $transactDetail->buy_price;
			    	$csv_row['buy_comment']      = $transactDetail->buy_comment;
			    }
			    $csv_row['tax_division']            = \App\Helpers\Claim\getTaxDivisionText($transactDetail->tax_division);
			    $csv_row['tax_rate']                = $transactDetail->tax_rate.'%';
			    $csv_row['refund_price']            = $transactDetail->refund_price;
			    $csv_row['jan_code']                = $transactDetail->jan_code;
			    $csv_row['gtin_code']               = $transactDetail->gtin_code;
			    $csv_row['department_name']         = $transactDetail->department_name;
			    $csv_row['gs1_sscc']                = $transactDetail->gs1_sscc;
			    $csv_row['facility_item_code']      = $transactDetail->facility_item_code;
			    $csv_row['trader_item_code']        = $transactDetail->trader_item_code;

    			$csv_data[] = $csv_row;
    		}
    	} catch (Exception $e){
    		$a = 1;
    	}

    	$csvHeader = array();
    	$csvHeader[] = '取引年月日';
    	$csvHeader[] = '売仕区分';
    	$csvHeader[] = '文化連取引履歴ID';
    	$csvHeader[] = '売上伝票番号';
    	$csvHeader[] = '仕入伝票番号';
    	$csvHeader[] = '文化連商品CD';
    	$csvHeader[] = '販売メーカー';
    	$csvHeader[] = '商品名';
    	$csvHeader[] = '規格';
    	$csvHeader[] = '製品番号';
    	$csvHeader[] = '数量';
    	$csvHeader[] = '単位名';

    	if ($kengen->show_transact_sales_columns == 1 ) {
    		$csvHeader[] = '売上単価';
    		$csvHeader[] = '売上金額';
    		$csvHeader[] = '売上備考';
    	}
		if ($kengen->show_transact_purchase_columns == 1 ) {
	    	$csvHeader[] = '仕入単価';
	    	$csvHeader[] = '仕入金額';
	    	$csvHeader[] = '仕入備考';
		}
    	$csvHeader[] = '消費税率区分';
    	$csvHeader[] = '消費税率';
    	$csvHeader[] = '償還価格';
    	$csvHeader[] = 'JAN';
    	$csvHeader[] = 'GTIN';
    	$csvHeader[] = '部署名';
    	$csvHeader[] = 'GS1_SSCC';
    	$csvHeader[] = '施設商品ID';
    	$csvHeader[] = '業者商品ID';

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /*
     * 請求登録時の権限チェック
     */
    function getRegistPrivileges($user_id,$user_group_id) {
        $this->start_log();
    	$rec=$this->transactFileStorage->getRegistPrivileges($user_id,$user_group_id);
        $this->end_log();
    	if ($rec[0]->regist_kengen < 0) {
    		return false;
    	} else {
    		return true;
    	}
    }



    /*
     * 新規登録が可能か(ステータス側)
     */
    function getTransactRegistPrivileges($user_id,$user_group_id,$status) {
        $this->start_log();
    	$rec=$this->transactFileStorage->getRegistPrivilegesTransact($user_id,$user_group_id,$status,0);
        $this->end_log();
    	if ($rec[0]->status_forward < 0) {
    		return false;
    	} else {
    		return true;
    	}
    }

    /*
     * データ区分取得
     */
    public function getDataTypeList($user) {
        $this->start_log();
        $result = $this->transactFileStorage->getDataTypeList($user);
        $this->end_log();
    	return $result;
    }

    public function getDataTypeListClaimRegistList($user) {
        $this->start_log();
        $result = $this->transactFileStorage->getDataTypeListClaimRegistList($user);
        $this->end_log();
    	return $result;
    }

     /*
     * データ区分取得
     */
    public function getDataTypeList_old($user) {
        $this->start_log();
        $result = $this->transactFileStorage->getDataTypeList($user->primary_user_group_id);
        $this->end_log();
    	return $result;
    }

    /*
     * 供給区分取得
     */
    public function getSupplyDevisionList($user) {
        $this->start_log();
        $result = $this->transactHeader->getSupplyDevisionList($user->primary_user_group_id);
        $this->end_log();
    	return $result;
    }

    /*
     * 請求権限取得
     *
     */
    function getPrivilegesTransact($user_id,$user_group_id) {
        $this->start_log();
        $result = $this->transactFileStorage->getPrivilegesTransact($user_id,$user_group_id);
        $this->end_log();
    	return $result;
    }

    /**
     * 所属している施設取得
     * @param User $user ユーザ情報
     * @param UserGroup モデル情報
     */
    public function getBelongUserGroups(User $user) {
        $this->start_log();
    	// if ( $user->userGroup()->group_type === \Config::get('const.kojin_name') ) {
        //     $result = $this->userGroup
        //         ->getUserGroups($user)
        //         ->where('group_type', \Config::get('const.kojin_name'));
    	// } else if ($user->userGroup()->group_type === \Config::get('const.hospital_name') )  {
        //     $result = $this->userGroup
        //         ->getUserGroups($user)
        //         ->where('group_type', \Config::get('const.hospital_name'));
    	// } else if ($user->userGroup()->group_type === \Config::get('const.bunkaren_name') ) {
        //     $result = $this->userGroup
        //         ->getUserGroups($user)
        //         ->whereIn('group_type', array(\Config::get('const.hospital_name'), \Config::get('const.kojin_name')));
        // }
        $result = $this->userGroup->getUserGroupsFacility($user);
        $this->end_log();
        logger($user->userGroup());
        logger($user->userGroup()->group_type);
        logger($result);
        return $result;
    }

    /**
     * 現在日より過去3年分を取得
     * @return array 年月情報
     */
    public function getPast3Year() {
        $this->start_log();
    	$dt = Carbon::now();
    	$months = [];
    	for ($loop=0; $loop < \Config::get('const.past_year_limit'); $loop++) {
    		$dt->subMonthNoOverFlow();
    		$months[] = $dt->format('Y年m月');
        }
        $this->end_log();
    	return $months;
    }

    /**
     * 供給区分一覧取得
     * @param array $list ユーザグループIDs
     * @return UserGroupSupplyDivisions モデル情報
     */
    public function getSupplies($list) {
        $this->start_log();
        $result = $this->supply
            ->select('supply_division_name')
            ->whereIn('user_group_id', $list)
            ->groupBy('supply_division_name')
            ->pluck('supply_division_name');
        $this->end_log();
    	return $result;
    }

    /**
     * 全業者一覧取得(所属を考慮していない)
     */
    public function getTraders() {
        $this->start_log();
        $result = $this->groupTraderRelation->getGroupTraderDistinctList();
        $this->end_log();
    	return $result;

    }

    
    /**
     * userに紐づく業者一覧取得
     */
    public function getGroupTraderListByUser($user) {
        $this->start_log();
        $result = $this->groupTraderRelation->getGroupTraderListByUser($user);
        $this->end_log();
    	return $result;

    }



    /**
     * メーカー一覧取得
     * @return Maker モデル情報
     */
    public function getMakers($list) {
        $this->start_log();
        $result = $this->transactDetail
            ->select('maker_name')
            ->whereIn('sales_user_group_id', $list)
            ->groupBy('maker_name')
            ->orderBy('maker_name')
            ->pluck('maker_name');
        $this->end_log();
    	return $result;

    }

    /**
     * 請求データ出力取得
     * @param Request $request リクエスト情報
     * @param User $user ユーザ情報
     * @param array 請求情報
     */
    public function getInvoiceData(Request $request, User $user): ?array {
        $this->start_log();

        $invoices = [];
        // 出力帳票区分は必須
        if (empty($request->output_type)) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . '出力帳票区分の指定していません。');
            $this->end_log();
            return null;
        }
        // ユーザグループ情報
        $sales_user_groups = $request->input('user_groups', null);
        // 供給区分情報
        $supplies = $request->input('supplies', null);
        // 指定無い場合、所属内のユーザグループ範囲取得する。
        // 個人の場合
        if ( $user->userGroup()->group_type === \Config::get('const.kojin_name') ) {
            $limit_user_groups = $this->userGroup
                ->getUserGroups($user)
                ->where('group_type', \Config::get('const.kojin_name'))
                ->pluck('id');
        } else {
            $limit_user_groups = $this->userGroup
                ->getUserGroups($user)
                ->where('group_type', \Config::get('const.hospital_name'))
                ->pluck('id');
        }
        if (!empty($supplies)) {
            $supplies = $this->supply
                ->select('id', 'user_group_id', 'supply_division_name as name')
                // 供給区分は所属しているユーザグループIDで制限する。
                ->where(function($query) use($limit_user_groups){
                    $query->whereIn('user_group_id', $limit_user_groups);
                })
                ->where(function($query) use($supplies){
                    if (!empty($supplies)) {
                        $query->whereIn('supply_division_name', $supplies);
                    }
                })
                ->get()
                ->toArray();
        }
        // 業者情報
        $traders  = $request->input('traders', null);
        // メーカー情報
        //$makers   = $request->input('makers', null);
        $makers_tmp   = $request->input('makers', null);
        $makers = array();

        if (!empty($makers_tmp)) {
        	foreach ($makers_tmp as $tmp) {
        		if (empty($tmp)) {
        			$makers[] = '';
        		} else {
        			$makers[] = $tmp;
        		}
        	}
        }

        // 過去3年分の年月
        $past_dates  = $request->input('past_dates', null);
        if (!empty($past_dates)) {
            // 年月を検索用に加工
            foreach ($past_dates as $key => $month) {
                $past_dates[$key] = substr($month,0,4) . '-' . substr($month, 7, 2);
            }
        }
        // 合計表出力
        $result = $this->getSummary((int)$request->output_type, $sales_user_groups, $supplies, $traders, $past_dates, $makers, $limit_user_groups);
        $tmp = null;
        $row = 0;
        $total_sales_price = $total_refund_price = 0;
        $subtotal_sales_price = $subtotal_refund_price = 0;
        foreach ($result as $line) {
            if (empty($tmp)) {
                $tmp = $line;
                $total_sales_price  = $subtotal_sales_price  = $line['sales_price'];
                $total_refund_price = $subtotal_refund_price = $line['refund_price'];
                $invoices[$row] = $line;
                $invoices[$row]['sales_price'] = number_format((float)$invoices[$row]['sales_price'], 2);
                $invoices[$row]['refund_price'] = number_format((float)$invoices[$row]['refund_price'], 2);
                $row++;
            } else {
                // 一致
                if ($tmp['claim_date'] === $line['claim_date'] &&
                    $tmp['supply_division_name'] === $line['supply_division_name'] &&
                    $tmp['sale_name'] === $line['sale_name'] && $tmp['comon_name'] === $line['comon_name']) {
                    $tmp['sales_price']  += $line['sales_price'];
                    $tmp['refund_price'] += $line['refund_price'];
                    $total_sales_price  += $line['sales_price'];
                    $total_refund_price += $line['refund_price'];
                    $subtotal_sales_price  += $line['sales_price'];
                    $subtotal_refund_price += $line['refund_price'];
                } else {
                    // 不一致 小計追加
                    if ($tmp['key'] === $line['key']) {
                    	$tmp_common_name=$tmp['comon_name'];

                    	$tmp = $line;
                        $total_sales_price  += $line['sales_price'];
                        $total_refund_price += $line['refund_price'];
                        $subtotal_sales_price  += $line['sales_price'];
                        $subtotal_refund_price += $line['refund_price'];
                        $invoices[$row] = $line;

                        if ($tmp_common_name === $invoices[$row]['comon_name']) {
                        	$invoices[$row]['comon_name']="";
                        }

                        $invoices[$row]['sales_price'] = number_format((float)$invoices[$row]['sales_price'], 2);
                        $invoices[$row]['refund_price'] = number_format((float)$invoices[$row]['refund_price'], 2);
                        $row++;
                    } else {
                        $tmp['claim_date'] = $tmp['supply_division_name'] = $tmp['sale_name'] = null;
                        $tmp['comon_name'] = '小計';
                        $invoices[$row-1]['sales_price'] = number_format((float)$tmp['sales_price'], 2);
                        $invoices[$row-1]['refund_price'] = number_format((float)$tmp['refund_price'], 2);
                        $invoices[$row] = $tmp;
                        $invoices[$row]['sales_price'] = number_format((float)$subtotal_sales_price, 2);
                        $invoices[$row]['refund_price'] = number_format((float)$subtotal_refund_price, 2);
                        $row++;
                        $total_sales_price  += $line['sales_price'];
                        $total_refund_price += $line['refund_price'];
                        $subtotal_sales_price  = $line['sales_price'];
                        $subtotal_refund_price = $line['refund_price'];
                        $tmp = $line;
                        $invoices[$row] = $line;
                        $invoices[$row]['sales_price'] = number_format((float)$invoices[$row]['sales_price'], 2);
                        $invoices[$row]['refund_price'] = number_format((float)$invoices[$row]['refund_price'], 2);
                        $row++;
                    }
                }
            }
        }
        // 最後の部分
        $tmp['claim_date'] = $tmp['supply_division_name'] = $tmp['sale_name'] = null;
        $tmp['comon_name'] = '小計';
        if ($row >= 1) {
            $invoices[$row-1]['sales_price'] = number_format((float)$tmp['sales_price'], 2);
            $invoices[$row-1]['refund_price'] = number_format((float)$tmp['refund_price'], 2);
            $invoices[$row] = $tmp;
            $invoices[$row]['sales_price'] = number_format((float)$subtotal_sales_price, 2);
            $invoices[$row]['refund_price'] = number_format((float)$subtotal_refund_price, 2);
            $row++;
            // 合計処理
            $tmp['comon_name'] = '合計';
            $tmp['sales_price'] = number_format((float)$total_sales_price, 2);
            $tmp['refund_price'] = number_format((float)$total_refund_price, 2);
            $invoices[$row] = $tmp;
        } else {
            $invoices[0]['sales_price'] = number_format((float)0, 2);
            $invoices[0]['refund_price'] = number_format((float)0, 2);
            $invoices[0] = $tmp;
            $invoices[0]['sales_price'] = number_format((float)0, 2);
            $invoices[0]['refund_price'] = number_format((float)0, 2);
            // 合計処理
            $tmp['comon_name'] = '合計';
            $tmp['sales_price'] = number_format((float)0, 2);
            $tmp['refund_price'] = number_format((float)0, 2);
            $invoices[1] = $tmp;
        }
        $this->end_log();
        return $invoices;
    }


    /**
     * 集計処理
     * @param int $output 1=業者, 2=メーカー集計区分
     * @param array $sales_user_groups 施設情報
     * @param array $supplies 供給区分情報
     * @param array $traders 業者情報
     * @param array $past_dates 年月情報
     * @param array $makers メーカー情報
     * @param array $user_groups 全件取得の範囲情報
     * @return array 集計結果
     */
    private function getSummary(int $output, $sales_user_groups, $supplies, $traders, $past_dates, $makers, $user_groups):array {
        $this->start_log();
    	$keyList=array();

    	$query = $this->transactDetail
            ->select(
                \DB::Raw("sum(transact_details.sales_price) as sales_price"),
                \DB::Raw("sum(transact_details.refund_price * transact_details.quantity) as refund_price")
            );
    	if (!empty($sales_user_groups)) {
            $query
                ->addSelect('sale.name as sale_name')
    		    ->leftJoin('user_groups as sale', 'transact_details.sales_user_group_id', '=', 'sale.id');
    		$keyList[]="sale.name";
    	} else {
    		$query->addSelect(\DB::Raw("'' as sale_name"));
    	}
    	if (!empty($supplies)) {
            $query
                ->addSelect('user_group_supply_divisions.supply_division_name')
                ->leftJoin('user_group_supply_divisions', 'transact_details.supply_division', '=', 'user_group_supply_divisions.id');
    		$keyList[]="user_group_supply_divisions.supply_division_name";
    	} else {
    		$query->addSelect(\DB::Raw("'' as supply_division_name"));
    	}
    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
                $query
                    ->addSelect('purchase.name as comon_name')
                    ->leftJoin('user_groups as purchase', 'transact_details.purchase_user_group_id', '=', 'purchase.id');
    			$keyList[]="purchase.name";
    		} else {
    			$query->addSelect(\DB::Raw("'' as comon_name"));
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->addSelect('transact_details.maker_name as comon_name');
    			$keyList[]="transact_details.maker_name";
    		} else {
    			$query->addSelect(\DB::Raw("'' as comon_name"));
    		}
    	}
    	if (!empty($past_dates)) {
    		$query->addSelect(\DB::Raw("to_char(transact_details.claim_date, 'YYYYMM') as claim_date"));
    	} else {
    		$query->addSelect(\DB::Raw("'' as claim_date"));
    	}

    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
	    	//条件が全て指定されているばあい
	    	if (!empty($sales_user_groups) && !empty($past_dates) && !empty($supplies) && !empty($traders)) {
	    		if (count($past_dates) == 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="user_group_supply_divisions.supply_division_name";
	    		} else {
		    		if (count($traders) > 1) {
		    			$keyList=array();
		    			$keyList[]="sale.name";
		    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
		    			$keyList[]="user_group_supply_divisions.supply_division_name";

		    		}
	    		}
	    	}

	    	if (!empty($sales_user_groups) && !empty($past_dates) && empty($supplies) && !empty($traders)) {
	    		if (count($traders) > 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
	    		}
	    	}

    	} else {
    		//条件が全て指定されているばあい
    		if (!empty($sales_user_groups) && !empty($past_dates) && !empty($supplies) && !empty($makers)) {
    			if (count($past_dates) == 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="user_group_supply_divisions.supply_division_name";
    			} else {
	    			if (count($makers) > 1) {
	    				$keyList=array();
	    				$keyList[]="sale.name";
		    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
	    				$keyList[]="user_group_supply_divisions.supply_division_name";
	    				//$keyList[]="makers.name";
	    			}
    			}
    		}

    		if (!empty($sales_user_groups) && !empty($past_dates) && empty($supplies) && !empty($makers)) {
    			if (count($makers) > 1) {
    				$keyList=array();
    				$keyList[]="sale.name";
	    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
    			}
    		}

    	}

		$key=implode( " || ", $keyList );
		$query->addSelect(\DB::Raw($key." as key"));

    	$query->where(function($query) use($sales_user_groups) {
    		if (!empty($sales_user_groups)) {
    			$query->whereIn('transact_details.sales_user_group_id', $sales_user_groups);
    		}
    	});
   		$query->where(function($query) use($supplies) {
   			if (!empty($supplies)) {
   				$ids = collect($supplies)->pluck('id');
   				$query->whereIn('transact_details.supply_division', $ids);
   			}
   		});
		if ($output === self::TOTAL_TABLE_BY_TRADER) {
			$query->where(function($query) use($traders) {
				if (!empty($traders)) {
					$query->whereIn('transact_details.purchase_user_group_id', $traders);
   				}
			});
		} else {
			$query->where(function($query) use($makers) {
				if (!empty($makers)) {
					$query->whereIn('transact_details.maker_name', $makers);
				}
   			});
		}
    	$query->where(function($query) use($past_dates){
    		if (!empty($past_dates)) {
    			foreach ($past_dates as $m) {
    				$query->orWhere('transact_details.claim_date', 'like', "{$m}%");
    			}
    		}
    	});


    	// 全て指定なしの場合は制限を設ける
    	if (empty($sales_user_groups) && empty($supplies) && empty($traders) && empty($makers)) {
    		$query->whereIn('transact_details.sales_user_group_id', $user_groups);
    	}
    	if (!empty($sales_user_groups)) {
    		$query->groupBy('sale.name');
    	}
    	if (!empty($supplies)) {
    		$query->groupBy('user_group_supply_divisions.supply_division_name');
    	}
    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
    			$query->groupBy('purchase.name');
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->groupBy('transact_details.maker_name');
    		}
    	}
    	if (!empty($past_dates)) {
    		//$query->groupBy("claim_date");
    		$query->groupBy(\DB::Raw("to_char(transact_details.claim_date, 'YYYYMM')"));
    	}

    	if (!empty($sales_user_groups)) {
    		$query->orderBy('sale.name');
    	}
        if (!empty($supplies)) {
    		$query->orderBy('user_group_supply_divisions.supply_division_name');
    	}
    	if (!empty($past_dates)) {
    		$query->orderBy("claim_date");
    	}

    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
    			$query->orderBy('purchase.name');
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->orderBy('transact_details.maker_name');
    		}
    	}
        $this->end_log();
    	return $query->get()->toArray();
    }

    /**
     * 請求データ出力SQL取得
     */
    public function getInvoiceDataForSQL(Request $request, $user) {
        $this->start_log();
    	$sql =  $this->getInvoiceDataSQL($request, $user);

    	$csv_data = array();
    	$csv_row = array();

    	$csv_row['SQL'] = $sql;//SQL文を格納
    	$csv_data[] = $csv_row ;

    	$csvHeader = ['--請求データ出力SQL'];

    	array_unshift($csv_data, $csvHeader);
        $this->end_log();
    	return $csv_data;
    }

    /**
     * 請求データ出力SQL
     * @param Request $request リクエスト情報
     * @param User $user ユーザ情報
     */
     public function getInvoiceDataSQL(Request $request, User $user) {
        $this->start_log();
     	$invoices = [];
        // 出力帳票区分は必須
        if (empty($request->output_type)) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . '出力帳票区分の指定していません。');
            $this->end_log();
            return null;
        }
        // ユーザグループ情報
        $sales_user_groups = $request->input('user_groups', null);
        // 供給区分情報
        $supplies = $request->input('supplies', null);
        // 指定無い場合、所属内のユーザグループ範囲取得する。
        // 個人の場合
        if ( $user->userGroup()->group_type === \Config::get('const.kojin_name') ) {
            $limit_user_groups = $this->userGroup
                ->getUserGroups($user)
                ->where('group_type', \Config::get('const.kojin_name'))
                ->pluck('id');
        } else {
            $limit_user_groups = $this->userGroup
                ->getUserGroups($user)
                ->where('group_type', \Config::get('const.hospital_name'))
                ->pluck('id');
        }
        if (!empty($supplies)) {
            $supplies = $this->supply
                ->select('id', 'user_group_id', 'supply_division_name as name')
                // 供給区分は所属しているユーザグループIDで制限する。
                ->where(function($query) use($limit_user_groups) {
                    $query->whereIn('user_group_id', $limit_user_groups);
                })
                ->where(function($query) use($supplies) {
                    if (!empty($supplies)) {
                        $query->whereIn('supply_division_name', $supplies);
                    }
                })
                ->get()
                ->toArray();
        }
        // 業者情報
        $traders  = $request->input('traders', null);
        // メーカー情報
        //$makers   = $request->input('makers', null);
        $makers_tmp   = $request->input('makers', null);
        $makers = array();

        if (!empty($makers_tmp)) {
        	foreach ($makers_tmp as $tmp) {
        		if (empty($tmp)) {
        			$makers[] = '';
        		} else {
        			$makers[] = $tmp;
        		}
        	}
        }

        // 過去3年分の年月
        $past_dates  = $request->input('past_dates', null);
        if (!empty($past_dates)) {
            // 年月を検索用に加工
            foreach ($past_dates as $key => $month) {
                $past_dates[$key] = substr($month,0,4) . '-'. substr($month, 7, 2);
            }
        }
        // 合計表のSQL
        $sql = $this->getSummarySql((int)$request->output_type, $sales_user_groups, $supplies, $traders, $past_dates, $makers, $limit_user_groups);

        $this->end_log();
     	return $sql;
     }

    private function getSummarySql(int $output, $sales_user_groups, $supplies, $traders, $past_dates, $makers, $user_groups) {
    	$keyList=array();

    	$query = $this->transactDetail
            ->select(
                \DB::Raw("sum(transact_details.sales_price) as sales_price"),
                \DB::Raw("sum(transact_details.refund_price * transact_details.quantity) as refund_price")
            );
    	if (!empty($sales_user_groups)) {
            $query
                ->addSelect('sale.name as sale_name')
    		    ->leftJoin('user_groups as sale', 'transact_details.sales_user_group_id', '=', 'sale.id');
    		$keyList[]="sale.name";
    	} else {
    		$query->addSelect(\DB::Raw("'' as sale_name"));
    	}
    	if (!empty($supplies)) {
            $query
                ->addSelect('user_group_supply_divisions.supply_division_name')
                ->leftJoin('user_group_supply_divisions', 'transact_details.supply_division', '=', 'user_group_supply_divisions.id');
    		$keyList[]="user_group_supply_divisions.supply_division_name";
    	} else {
    		$query->addSelect(\DB::Raw("'' as supply_division_name"));
    	}
    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
                $query
                    ->addSelect('purchase.name as comon_name')
                    ->leftJoin('user_groups as purchase', 'transact_details.purchase_user_group_id', '=', 'purchase.id');
    			$keyList[]="purchase.name";
    		} else {
    			$query->addSelect(\DB::Raw("'' as comon_name"));
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->addSelect('transact_details.maker_name as comon_name');
    			$keyList[]="transact_details.maker_name";
    		} else {
    			$query->addSelect(\DB::Raw("'' as comon_name"));
    		}
    	}
    	if (!empty($past_dates)) {
    		$query->addSelect(\DB::Raw("to_char(transact_details.claim_date, 'YYYYMM') as claim_date"));
    	} else {
    		$query->addSelect(\DB::Raw("'' as claim_date"));
    	}

    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
	    	//条件が全て指定されているばあい
	    	if (!empty($sales_user_groups) && !empty($past_dates) && !empty($supplies) && !empty($traders)) {
	    		if (count($past_dates) == 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="user_group_supply_divisions.supply_division_name";
	    		} else {
		    		if (count($traders) > 1) {
		    			$keyList=array();
		    			$keyList[]="sale.name";
		    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
		    			$keyList[]="user_group_supply_divisions.supply_division_name";
		    		}
	    		}
	    	}

	    	if (!empty($sales_user_groups) && !empty($past_dates) && empty($supplies) && !empty($traders)) {
	    		if (count($traders) > 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
	    		}
	    	}

    	} else {
    		//条件が全て指定されているばあい
    		if (!empty($sales_user_groups) && !empty($past_dates) && !empty($supplies) && !empty($makers)) {
    			if (count($past_dates) == 1) {
	    			$keyList=array();
	    			$keyList[]="sale.name";
	    			$keyList[]="user_group_supply_divisions.supply_division_name";
    			} else {
	    			if (count($makers) > 1) {
	    				$keyList=array();
	    				$keyList[]="sale.name";
		    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
	    				$keyList[]="user_group_supply_divisions.supply_division_name";
	    			}
    			}
    		}

    		if (!empty($sales_user_groups) && !empty($past_dates) && empty($supplies) && !empty($makers)) {
    			if (count($makers) > 1) {
    				$keyList=array();
    				$keyList[]="sale.name";
	    			$keyList[]="to_char(transact_details.claim_date, 'YYYYMM')";
    			}
    		}

    	}

		$key=implode( " || ", $keyList );
		$query->addSelect(\DB::Raw($key." as key"));

    	$query->where(function($query) use($sales_user_groups) {
    		if (!empty($sales_user_groups)) {
    			$query->whereIn('transact_details.sales_user_group_id', $sales_user_groups);
    		}
    	});
   		$query->where(function($query) use($supplies) {
   			if (!empty($supplies)) {
   				$ids = collect($supplies)->pluck('id');
   				$query->whereIn('transact_details.supply_division', $ids);
   			}
   		});
		if ($output === self::TOTAL_TABLE_BY_TRADER) {
			$query->where(function($query) use($traders) {
				if (!empty($traders)) {
					$query->whereIn('transact_details.purchase_user_group_id', $traders);
   				}
			});
		} else {
			// SQL用に編集
			$query->where(function($query) use($makers) {
				if (!empty($makers)) {
					foreach ($makers as $maker) {
					   $query->where('transact_details.maker_name', "'{$maker}'");
					}
				}
   			});
		}
		// SQL用に編集
    	$query->where(function($query) use($past_dates){
    		if (!empty($past_dates)) {
    			foreach ($past_dates as $m) {
    				$query->orWhere('transact_details.claim_date', 'like', "'{$m}%'");
    			}
    		}
    	});


    	// 全て指定なしの場合は制限を設ける
    	if (empty($sales_user_groups) && empty($supplies) && empty($traders) && empty($makers)) {
    		$query->whereIn('transact_details.sales_user_group_id', $user_groups);
    	}
    	if (!empty($sales_user_groups)) {
    		$query->groupBy('sale.name');
    	}
    	if (!empty($supplies)) {
    		$query->groupBy('user_group_supply_divisions.supply_division_name');
    	}
    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
    			$query->groupBy('purchase.name');
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->groupBy('transact_details.maker_name');
    		}
    	}
    	if (!empty($past_dates)) {
    		//$query->groupBy("claim_date");
    		$query->groupBy(\DB::Raw("to_char(transact_details.claim_date, 'YYYYMM')"));
    	}

    	if (!empty($sales_user_groups)) {
    		$query->orderBy('sale.name');
    	}
        if (!empty($supplies)) {
    		$query->orderBy('user_group_supply_divisions.supply_division_name');
    	}
    	if (!empty($past_dates)) {
    		$query->orderBy("claim_date");
    	}

    	if ($output === self::TOTAL_TABLE_BY_TRADER) {
    		if (!empty($traders)) {
    			$query->orderBy('purchase.name');
    		}
    	} else {
    		if (!empty($makers)) {
    			$query->orderBy('transact_details.maker_name');
    		}
    	}

    	$sql = preg_replace_array('/\?/',$query->getBindings(), $query->toSql());
        $this->end_log();
    	return $sql;
    }
}
