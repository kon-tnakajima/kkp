<?php

namespace App\Model;

use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Model\TransactHeader;
use App\Model\Task;
use App\Model\ClaimHistoryComment;
use App\Model\BaseModel;
use App\Observers\AuthorObserver;

class TransactFileStorage extends BaseModel {
    use SoftDeletes;

    protected $guarded = ['id'];

    //売仕区分
    const STOCK_OR_SALE_DIVISION_SALE = 1;
    const STOCK_OR_SALE_DIVISION_PURCHASE = 2;
    //供給区分
    const SUPPLY_DIVISION_MEDICINE = 10;
    const SUPPLY_DIVISION_MATERIAL = 20;
    const SUPPLY_DIVISION_REAGENT = 22;
    const SUPPLY_DIVISION_INSTRUMENT = 30;
    const SUPPLY_DIVISION_MAINTENANCE = 32;


    /*
     * boot
     */
    public static function boot() {
        parent::boot();
        self::observe(new AuthorObserver());
    }


    /*
     * 請求登録履歴一覧
     */
    public function getList(Request $request, $count, $user) {

		$sql  = $this->getListSql($request, $count, $user);

		$count_sql = " select count(*) from (".$sql.") as tmp ";
		$all_count = \DB::select($count_sql);

		$count2=$all_count[0]->count;

		$sql .=" order by ";
		$sql .="     id desc ";

		if ($count == -1) {
			$per_page = isset($request->page_count) ? $request->page_count : 1;
		} else {
			if (!isset($request->page_count)){
				$offset=0;
			} else {
				if ($request->page == 0) {
					$offset=0;
				} else {
					$offset=($request->page - 1) * $request->page_count;
				}
			}

			$per_page = isset($request->page_count) ? $request->page_count : 1;
			$sql .=" limit ".$per_page." offset ".$offset;

		}

		$all_rec = \DB::select($sql);
		$result = TransactFileStorage::query()->hydrate($all_rec);
		// ページ番号が指定されていなかったら１ページ目
		$page_num = isset($request->page) ? $request->page : 1;
		// ページ番号に従い、表示するレコードを切り出す
		$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

		// ページャーオブジェクトを生成
		$pager= new \Illuminate\Pagination\LengthAwarePaginator(
				$result, // ページ番号で指定された表示するレコード配列
				$count2, // 検索結果の全レコード総数
				$per_page, // 1ページ当りの表示数
				$page_num, // 表示するページ
				['path' => $request->url()] // ページャーのリンク先のURLを指定
				);

		return $pager;

    }

 	/*
     * 請求登録履歴一覧SQL
     */
    public function getListSql(Request $request, $count, $user) {
		$sql  ="";
        $sql .=" select ";
        $sql .="     tfs.id ";
        $sql .="     , tfs.file_name ";
        $sql .="     , tfs.claim_month ";
        $sql .="     , tfs.data_type as supply_division ";
        $sql .="     , ugdt.data_type_name ";
        $sql .="     , tfs.medicine_price_total ";
        $sql .="     , case  ";
        $sql .="         when vprcr.show_purchase_price = 0  ";
        $sql .="             then null  ";
        $sql .="         else tfs.purchase_price_total ";
        $sql .="         end as purchase_price_total ";
        $sql .="     , case  ";
        $sql .="         when vprcr.show_sales_price = 0  ";
        $sql .="             then null  ";
        $sql .="         else tfs.sales_price_total ";
        $sql .="         end as sales_price_total ";
		$sql .="     , tfs.claim_task_status ";
		$sql .="     , ta.name status_name ";
        $sql .="     , ug.name as user_group_name ";
        $sql .="     , tfs.receipt_date ";
        $sql .="     , tfs.created_at ";
        $sql .="     , u.name as upload_user_name      ";
        $sql .="     , vprcrb.status_forward ";
        $sql .="     , vprcrb.action_name_foward ";
        $sql .="     , vprcrb.status_reject ";
        $sql .="     , vprcrb.action_name_reject ";
        // 20201027SQL変更分1行追加
        $sql .="     ,case when vprcrb.status_forward>0 then 1 else 0 end as flg_in_charge_of_transact_file_storage ";
        $sql .=" from ";
        $sql .="    transact_file_storages tfs ";
        // 20201127 ビューから関数に変更
        $sql .="    inner join f_privileges_row_claim_regist(". $user->id . ") as vprcr ";
        $sql .="         on tfs.user_group_id = vprcr.user_group_id  ";
        $sql .="         and vprcr.show_claim_regist_rows = 1  ";
        // 20201127 ビューから関数に変更
        $sql .="       inner join f_privileges_row_claim_regist_bystate(". $user->id . ") as vprcrb ";
        $sql .="         on tfs.user_group_id = vprcrb.user_group_id  ";
        $sql .="         and vprcrb.status = tfs.claim_task_status  ";
        $sql .="     left join user_groups ug  ";
        $sql .="         on vprcr.user_group_id = ug.id  ";
        $sql .="     left join users u  ";
        $sql .="         on tfs.creater = u.id  ";
        $sql .="     left join user_group_data_types ugdt  ";
		$sql .="         on ugdt.id = tfs.data_type  ";
		$sql .="     left join tasks ta ";
	    $sql .="		 on tfs.claim_task_status = ta.status ";
    	$sql .="    	 and apply_payment_flg = 3 ";
        $sql .=" where ";
        $sql .="     1 = 1  ";

        $tasks = new Task();
		// 状態
		if (!empty($request->status)) {
			// チェックあり 却下も含むので全部
            $task_list = $tasks->getStatusClaimList(3);
			$status_list = array();
			foreach($task_list as $key => $value) {
				$status_list[] = $value['status'];
			}
			$sql .= " and tfs.claim_task_status in (".implode(',', $status_list).")";
		} else {
			// チェック無し 却下を除く全部 13、23、33を除外
			$task_list = $tasks->getStatusClaimList(3);
			$status_list = array();
		    foreach($task_list as $key => $value) {
		    	if($value['status'] != 13 && $value['status'] != 23 && $value['status'] != 33) {
		    		$status_list[] = $value['status'];
		    	}
			}
            $sql .= " and tfs.claim_task_status in (".implode(',', $status_list).")";
		}

        // 施設
		if (!empty($request->user_group)) {
			$sql .= " and tfs.user_group_id in (".implode(',', $request->user_group).")";
		}
		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .="     and tfs.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .="     and tfs.claim_month <='".$request->claim_month_to.'/01'."'";
		}
		// 供給区分."
		if (!empty($request->supply_division)) {
			$sql .="     and tfs.data_type =".$request->supply_division;
		}
		return $sql;

    }

	/*
     * 請求登録履歴ユーザーガイド文
     */
    public function getUserGuide($user) {
		$sql  = "SELECT * FROM";
        $sql .= " f_user_guide_claim_regist_list('" . $user->id . "')";
		$user_guide = $this->simple_execute_sql($sql);
        return $user_guide;
	}

    /*
     * 採用一覧SQL
     */
    public function getListSql_old(Request $request, $count, $user) {
		$sql  ="";
        $sql .=" select ";
        $sql .="     tfs.id ";
        $sql .="     , tfs.file_name ";
        $sql .="     , tfs.claim_month ";
        $sql .="     , tfs.supply_division ";
        $sql .="     , ugdt.data_type_name ";
        $sql .="     , tfs.medicine_price_total ";
        $sql .="     , case ";
        $sql .="         when vprcr.show_purchase_price = 0 ";
        $sql .="             then null ";
        $sql .="         else tfs.purchase_price_total ";
        $sql .="         end as purchase_price_total ";
        $sql .="     , case ";
        $sql .="         when vprcr.show_sales_price = 0 ";
        $sql .="             then null ";
        $sql .="         else tfs.sales_price_total ";
        $sql .="         end as sales_price_total ";
        $sql .="     , tfs.claim_task_status ";
        $sql .="     , ug.name as user_group_name ";
        $sql .="     , tfs.receipt_date ";
        $sql .="     , tfs.created_at ";
        $sql .="     , u.name as upload_user_name ";
        $sql .="     , vprcrb.status_forward ";
        $sql .="     , vprcrb.action_name_foward ";
        $sql .="     , vprcrb.status_reject ";
        $sql .="     , vprcrb.action_name_reject ";
        $sql .=" from ";
        $sql .="     transact_file_storages tfs ";
        $sql .="     inner join v_privileges_row_claim_regist as vprcr ";
        $sql .="         on tfs.sales_user_group_id = vprcr.user_group_id ";
        $sql .="         and vprcr.user_id = " . $user->id;
        $sql .="         and vprcr.show_claim_regist_rows = 1 ";
        $sql .="     inner join v_privileges_row_claim_regist_bystate as vprcrb ";
        $sql .="         on tfs.sales_user_group_id = vprcrb.user_group_id ";
        $sql .="         and vprcrb.user_id = " . $user->id;
        $sql .="         and vprcrb.status = tfs.claim_task_status "; //ここが前のと違う
        $sql .=" left join user_groups ug ";
        $sql .="         on vprcr.user_group_id = ug.id ";
        $sql .="     left join users u ";
        $sql .="         on tfs.creater = u.id ";
        $sql .="     left join user_group_data_types ugdt ";
        $sql .="         on ugdt.id = tfs.supply_division ";
        $sql .=" where";
        $sql .="     1 = 1 ";

		// 状態
		if (!empty($request->status)) {
			$sql .= " and transact_file_storages.claim_task_status in (".implode(',', $request->status).")";
		}
		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .="     and transact_file_storages.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .="     and transact_file_storages.claim_month <='".$request->claim_month_to.'/01'."'";
		}
		// 供給区分."
		if (!empty($request->supply_division)) {
			$sql .="     and transact_file_storages.supply_division =".$request->supply_division;
		}
		return $sql;

    }

    /*
     * 採用一覧SQL
     */
    public function getListSql_old2(Request $request, $count, $user) {
		$sql  ="";
		$sql .=" select ";
		$sql .="      transact_file_storages.id ";
		$sql .="     ,transact_file_storages.file_name ";
		$sql .="     ,transact_file_storages.claim_month ";
		$sql .="     ,transact_file_storages.supply_division ";
		$sql .="     ,user_group_data_types.data_type_name ";
		$sql .="     ,transact_file_storages.medicine_price_total ";
		$sql .="     ,transact_file_storages.purchase_price_total ";
		$sql .="     ,case when vprtf.show_transact_sales_columns = 0 then 0 else transact_file_storages.sales_price_total end as sales_price_total ";
		$sql .="     ,transact_file_storages.claim_task_status ";
		$sql .="     ,user_groups.name as user_group_name ";
		$sql .="     ,transact_file_storages.receipt_date ";
		$sql .="     ,transact_file_storages.created_at ";
		$sql .="     ,users.name as upload_user_name ";
		$sql .="     ,detail.detail_count ";
		$sql .="     ,vprtf.show_transact_purchase_columns ";
		$sql .="     ,vprtf.show_transact_sales_columns ";
		$sql .="     ,case when vprtfb.status_forward > ".Task::STATUS_TRADER_CONFIRM." then ".Task::STATUS_TRADER_CONFIRM." else vprtfb.status_forward end status_forward ";
		$sql .="     ,vprtfb.status_back ";
		$sql .="     ,vprtfb.status_back_text ";
    	$sql .=" from ";
		$sql .="     transact_file_storages ";
		$sql .="     inner join v_privileges_row_transact_flow2 as vprtf";
		$sql .="         on transact_file_storages.sales_user_group_id = vprtf.user_group_id ";
		$sql .="         and vprtf.user_id =".$user->id;
		$sql .="         and vprtf.show_transact_rows=1 ";

		$sql .="     inner join v_privileges_row_transact_flow2_bystate as vprtfb";
		$sql .="         on transact_file_storages.sales_user_group_id = vprtfb.user_group_id ";
		$sql .="         and vprtfb.user_id =".$user->id;
		$sql .="         and vprtfb.status = transact_file_storages.claim_task_status";

		$sql .="     left join user_groups ";
		$sql .="         on vprtf.user_group_id  = user_groups.id ";
		$sql .="     left join users ";
		$sql .="         on transact_file_storages.creater = users.id ";
		$sql .="     left join user_group_data_types ";
		$sql .="         on user_group_data_types.id = transact_file_storages.supply_division ";
		$sql .="     left join ( ";
		$sql .="         select ";
		$sql .="              transact_details.transact_file_storage_id ";
		$sql .="             ,cast(count(transact_details.*) as integer) as detail_count ";
		$sql .="         from ";
		$sql .="             transact_details ";
		$sql .="         where ";
		$sql .="             transact_details.deleted_at is null ";
		$sql .="         group by ";
		$sql .="              transact_details.transact_file_storage_id ";
		$sql .="    ) as detail ";
		$sql .="         on transact_file_storages.id = detail.transact_file_storage_id ";
		$sql .=" where ";
		$sql .="     1=1 ";

		// 状態
		if (!empty($request->status)) {
			$sql .= " and transact_file_storages.claim_task_status in (".implode(',', $request->status).")";
		}
		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .="     and transact_file_storages.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .="     and transact_file_storages.claim_month <='".$request->claim_month_to.'/01'."'";
		}
		// 供給区分."
		if (!empty($request->supply_division)) {
			$sql .="     and transact_file_storages.supply_division =".$request->supply_division;
		}
		return $sql;

    }

	/*
     * 施設を取得
     */
    public function user() {
        return $this->belongsTo('App\User');
    }

    /*
     * 施設と対象年月でデータ取得
     */
    public function getData($facility_id, $claim_month) {
        return $this->where('facility_id', $facility_id)->where('claim_month', $claim_month)->first();
    }

    /*
     * 施設を取得
     */
    public function transactHeader() {
        return $this->hasMany('App\Model\TransactHeader');
    }

    /*
     * 薬価金額合計を取得
     */
    public function medicine_price_total() {
        $transactHeaders = $this->transactHeader;
        $rtn = (float)0;
        foreach ($transactHeaders as $header) {
            $rtn = $rtn + (float)$header->medicine_price_total();
        }
        return $rtn;
    }

    /*
     * 売上金額合計を取得
     */
    public function sales_price_total() {
        $transactHeaders = $this->transactHeader;
        $rtn = (float)0;
        foreach ($transactHeaders as $header) {
            $rtn = $rtn + (float)$header->sales_price_total();
        }
        return $rtn;
    }

    /*
     * 売上金額合計を取得
     */
    public function purchase_price_total() {
        $transactHeaders = $this->transactHeader;
        $rtn = (float)0;
        foreach ($transactHeaders as $header) {
            $rtn = $rtn + (float)$header->purchase_price_total();
        }
        return $rtn;
    }

    /*
     * データ区分一覧を取得(請求登録権限つきに変更）
     */
    public function getDataTypeList($user) {
    	$sql  = "";
    	$sql .= " select ";
        $sql .= "   vprc.user_group_id AS user_group_id ";
        $sql .= "    ,facility.name AS name ";
        $sql .= "    ,ugtp.id AS id ";
        $sql .= "    ,ugtp.data_type_name AS data_type_name ";
        $sql .= "  from f_privileges_row_claim_regist(" . $user->id .") AS vprc ";
        $sql .= "  inner join user_groups AS facility ";
        $sql .= "   on facility.id = vprc.user_group_id ";
        $sql .= "  inner join user_group_data_types AS ugtp ";
        $sql .= "   on vprc.user_group_id = ugtp.user_group_id ";
		$sql .= "  WHERE vprc.user_id = " . $user->id;
		$sql .= "   and vprc.arrow10 = 1 ";
        $sql .= "  ORDER BY ugtp.user_group_id,data_type_name ";

    	$all_rec = \DB::select($sql);
    	$result = TransactFileStorage::query()->hydrate($all_rec);

    	return $result;
	}
	

	public function getDataTypeListClaimRegistList($user) {
    	$sql  = "";
    	$sql .= " select ";
        $sql .= "   vprc.user_group_id AS user_group_id ";
        $sql .= "    ,facility.name AS name ";
        $sql .= "    ,ugtp.id AS id ";
        $sql .= "    ,ugtp.data_type_name AS data_type_name ";
        $sql .= "  from f_privileges_row_claim_regist(" . $user->id .") AS vprc ";
        $sql .= "  inner join user_groups AS facility ";
        $sql .= "   on facility.id = vprc.user_group_id ";
        $sql .= "  inner join user_group_data_types AS ugtp ";
        $sql .= "   on vprc.user_group_id = ugtp.user_group_id ";
		$sql .= "  WHERE vprc.user_id = " . $user->id;
        $sql .= "   and vprc.show_claim_regist_rows = 1 ";
        $sql .= "  ORDER BY ugtp.disp_order,ugtp.user_group_id,data_type_name ";

    	$all_rec = \DB::select($sql);
    	$result = TransactFileStorage::query()->hydrate($all_rec);

    	return $result;
	}


    /*
     * 施設とデータ区分のチェック
     */
    public function checkFacilityDataType($user_group_id, $data_name) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "  f_claim_regist_user_group_data_type_check ";
        $sql .= " from f_claim_regist_user_group_data_type_check(" . $user_group_id . ",'" . $data_name ."')";

        $all_rec = \DB::select($sql);
		$result = TransactFileStorage::query()->hydrate($all_rec);

    	return $result;
    }

    public function getTransactFileStorage($id) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      transact_file_storages.* ";
    	$sql .= "     ,user_group_data_types.data_type_name ";
    	$sql .= "     ,facilities.name as facility_name  ";
    	$sql .= "     ,users.name as user_name  ";
    	$sql .= " from ";
    	$sql .= "     transact_file_storages ";
    	$sql .= "     left join user_group_data_types ";
    	$sql .= "         on transact_file_storages.user_group_id = user_group_data_types.user_group_id ";
    	$sql .= "         and transact_file_storages.data_type = user_group_data_types.id ";
    	$sql .= "     left join facilities ";
    	$sql .= "         on transact_file_storages.facility_id = facilities.id ";
    	$sql .= "     left join users ";
    	$sql .= "         on transact_file_storages.creater = users.id ";
    	$sql .= " where ";
    	$sql .= "     transact_file_storages.deleted_at is null ";
    	$sql .= " limit 1 ";

    	$all_rec = \DB::select($sql);
    	$result = TransactFileStorage::query()->hydrate($all_rec);

    	return $result[0];
    }

	/*
     * 請求登録権限取得(請求登録で使用)
     */
    public function getRegistPrivileges($user_id,$user_group_id) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      vpry.arrow10 as regist_kengen ";
    	$sql .= " from ";
    	$sql .= "     f_privileges_row_claim_regist(". $user_id .") as vpry ";
    	$sql .= " where ";
    	$sql .= "      vpry.user_id = ".$user_id;
    	$sql .= "      and vpry.user_group_id =".$user_group_id;

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->regist_kengen = -1;

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
    }


    /*
     * 請求登録権限取得(請求登録履歴などの権限のところで使用)
     */
    public function getRegistPrivilegesTransact($user_id,$user_group_id,$status,$detail_count) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      vpry.status_forward ";
    	$sql .= "     ,vpry.action_name_foward ";
    	$sql .= "     ,vpry.status_reject ";
    	$sql .= "     ,vpry.action_name_reject ";
    	$sql .= " from ";
    	$sql .= "     v_privileges_row_claim_regist_bystate as vpry ";
    	$sql .= " where ";
    	$sql .= "      vpry.user_id = ".$user_id;
    	$sql .= "      and vpry.user_group_id =".$user_group_id;
    	$sql .= "      and vpry.status =".$status;

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->status_forward = -1;
    		$stdObj->action_name_foward = "";
    		$stdObj->status_reject = -1;
    		$stdObj->action_name_reject = "";

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
    }

    /*
     * 請求登録権限取得
     */
    public function getRegistPrivilegesTransact_old($user_id,$user_group_id,$status,$detail_count) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      vpry.status_forward ";
    	$sql .= "     ,vpry.status_back ";
    	$sql .= "     ,vpry.status_back_text ";
    	$sql .= " from ";
    	$sql .= "      v_privileges_row_transact_flow2_bystate as vpry ";
    	$sql .= " where ";
    	$sql .= "      vpry.user_id = ".$user_id;
    	$sql .= "      and vpry.user_group_id =".$user_group_id;
    	$sql .= "      and vpry.status =".$status;

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->status_forward = -1;
    		$stdObj->status_back = -1;
    		$stdObj->status_back_text = "";

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
    }

    /*
     * 請求照会取得（請求照会ステータスの権限）
     */
    public function getPrivilegesTransact($user_id,$user_group_id,$purchase_user_group_id, $facility_status, $trader_status) {
    	// TODO ビューが変更あり user_group_idが２つになった。salesとpurchaseの
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      vpry.facility_status ";
    	$sql .= "     ,vpry.trader_status ";
    	$sql .= "     ,vpry.facility_status_foward ";
        $sql .= "     ,vpry.action_name_facility_status_foward ";
    	$sql .= "     ,vpry.facility_status_remand ";
    	$sql .= "     ,vpry.facility_status_withdraw ";
        $sql .= "     ,vpry.trader_status_foward ";
        $sql .= "     ,vpry.action_name_trader_status_foward ";
    	$sql .= "     ,vpry.trader_status_remand ";
    	$sql .= "     ,vpry.trader_status_withdraw ";
    	$sql .= " from ";
    	$sql .= "      v_privileges_row_claim_check_bystate as vpry ";
    	$sql .= " where ";
    	$sql .= "      vpry.user_id = ".$user_id;
    	$sql .= "      and vpry.sales_user_group_id = ". $user_group_id;
    	// TODO 1件に絞り込む為、キー追加
    	$sql .= "      and vpry.purchase_user_group_id = " . $purchase_user_group_id;
    	$sql .= "      and vpry.facility_status = " . $facility_status;
    	$sql .= "      and vpry.trader_status = " . $trader_status;

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->facility_status = -1;
    		$stdObj->trader_status = -1;
    		$stdObj->facility_status_foward = -1;
    		$stdObj->action_name_facility_status_foward = "";
    		$stdObj->facility_status_remand = -1;
    		$stdObj->facility_status_withdraw = -1;
    		$stdObj->trader_status_foward = -1;
			$stdObj->action_name_trader_status_foward = "";
			$stdObj->trader_status_remand = -1;
			$stdObj->trader_status_withdraw = -1;

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
    }


    /*
     * 請求権限取得
     */
    public function getPrivilegesTransact_old($user_id,$user_group_id) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      vpry.show_transact_sales_columns";
    	$sql .= "     ,vpry.show_transact_purchase_columns";
    	$sql .= " from ";
    	$sql .= "      v_privileges_row_transact_flow as vpry ";
    	$sql .= " where ";
    	$sql .= "      vpry.user_id = ".$user_id;
    	$sql .= "      and vpry.user_group_id =".$user_group_id;

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->next_status = -1;
    		$stdObj->reject_status = -1;
    		$stdObj->withdraw_status = -1;

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec[0];
    }

    /*
     * 請求管理 メール送付先取得 請求照会側
     */
    public function getEmailClaimDetail($sales_user_group_id,$purchase_user_group_id, $facility_status, $trader_status, $claim_control) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     a.email ";
    	$sql .= " from  ";
    	$sql .= " (select ";
    	$sql .= "      vpry.facility_status ";
    	$sql .= "     ,vpry.trader_status ";
    	$sql .= "     ,vpry.facility_status_foward ";
        $sql .= "     ,vpry.action_name_facility_status_foward ";
    	$sql .= "     ,vpry.facility_status_remand ";
    	$sql .= "     ,vpry.facility_status_withdraw ";
        $sql .= "     ,vpry.trader_status_foward ";
        $sql .= "     ,vpry.action_name_trader_status_foward ";
    	$sql .= "     ,vpry.trader_status_remand ";
    	$sql .= "     ,vpry.trader_status_withdraw ";
    	$sql .= "     ,b.email ";
    	$sql .= "  from ";
    	$sql .= "      v_privileges_row_claim_check_bystate as vpry ";
    	$sql .= "       inner join users as b ";
    	$sql .= "        on vpry.user_id = b.id";
    	$sql .= "  where ";
    	$sql .= "      vpry.sales_user_group_id = ". $sales_user_group_id;
    	$sql .= "      and vpry.purchase_user_group_id = " . $purchase_user_group_id;
    	$sql .= "      and vpry.facility_status = " . $facility_status;
		$sql .= "      and vpry.trader_status = " . $trader_status;
		$sql .= "      and b.is_claim_mail = true ";

    	$sql .= " ) as a ";
    	$sql .= " where  1 = 1";

    	if ( $claim_control == 1) {
    	    $sql .= " and a.facility_status_foward > 0 ";
    	} else if ($claim_control == 2) {
    		$sql .= " and a.trader_status_foward > 0 ";
    	}


    	if (env( 'ENV_TYPE' ) === 'staging') {
    		$sql .= " and (a.email like '%bunkaren.or.jp' ";
    		$sql .= "   or a.email like '%k-on.co.jp' ";
    		$sql .= "   or a.email like '%example.com') ";
    	}
    	$sql .= " group by  ";
		$sql .= "     a.email ";
		
    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->email = "";

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
    }

    /*
     * 請求管理 メール送付先取得 請求登録実績側
     */
    public function getEmailClaim($status,$user_group_id,$detail_count) {
    	$user = Auth::User();

    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     a.email ";
    	$sql .= " from  ";
    	$sql .= " (select ";
    	$sql .= "      status_forward ";
    	$sql .= "     ,action_name_foward";
    	$sql .= "     ,status_reject";
    	$sql .= "     ,action_name_reject";
    	$sql .= "     ,b.email ";
    	$sql .= "  from ";
    	$sql .= "      v_privileges_row_claim_regist_bystate vpry ";
    	$sql .= "      inner join users as b ";
    	$sql .= "          on vpry.user_id = b.id";
    	$sql .= "  where ";
    	$sql .= "      vpry.user_group_id =".$user_group_id;
		$sql .= "      and vpry.status =".$status;
		$sql .= "      and b.is_claim_mail = true";
    	$sql .= " ) as a ";
    	$sql .= " where  ";
    	$sql .= "     a.status_forward > 0 ";

    	if (env( 'ENV_TYPE') === 'staging') {
    		$sql .= " and (a.email like '%bunkaren.or.jp' ";
    		$sql .= "   or a.email like '%k-on.co.jp' ";
    		$sql .= "   or a.email like '%example.com') ";
    	}
    	$sql .= " group by  ";
		$sql .= "     a.email ";
		
    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->email = "";

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
	}
	
    public function getEmailClaimReject($transactFileStorageID) {
    	$user = Auth::User();

    	$sql  = "";
		$sql .= " select";
		$sql .= " 	a.email ";
		$sql .= " from";
		$sql .= " 	( ";
		$sql .= " 		select";
		$sql .= " 			t.id";
		$sql .= " 			, t.user_group_id";
		$sql .= " 			, u.id user_id";
		$sql .= " 			, u.name";
		$sql .= " 			, v.arrow10";
		$sql .= " 			, u.email ";
		$sql .= " 		from";
		$sql .= " 			transact_file_storages t"; 
		$sql .= " 			inner join v_privileges_row_claim_regist v ";
		$sql .= " 				on t.user_group_id = v.user_group_id ";
		$sql .= " 				and (v.arrow10 = 1 or v.arrow20 = 1)  ";
		$sql .= " 			inner join users u ";
		$sql .= " 				on v.user_id = u.id ";
		$sql .= " 				and u.is_claim_mail = true ";
		$sql .= " 		where";
		$sql .= " 			t.id =".$transactFileStorageID;
		$sql .= " 	) as a ";
		$sql .= " where 1 = 1";
		
    	if (env( 'ENV_TYPE') === 'staging') {
    		$sql .= " and (a.email like '%bunkaren.or.jp' ";
    		$sql .= "   or a.email like '%k-on.co.jp' ";
    		$sql .= "   or a.email like '%example.com') ";
    	}
    	$sql .= " group by  ";
		$sql .= "     a.email ";
		
    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->email = "";

    		$all_rec[] = $stdObj;
    	}

    	return $all_rec;
	}




    /*
     * 請求管理 メール送付先取得
     */
    public function getEmailClaim_old($status,$user_group_id,$detail_count) {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     a.email ";
    	$sql .= " from  ";
    	$sql .= " (select ";
    	$sql .= "      status_forward ";
    	$sql .= "     ,status_back";
    	$sql .= "     ,status_back_text";
    	$sql .= "     ,b.email ";
    	$sql .= "  from ";
    	$sql .= "      v_privileges_row_transact_flow2_bystate vpry inner join users as b";
    	$sql .= "          on vpry.user_id = b.id";
    	$sql .= "  where ";
    	$sql .= "      b.is_claim_mail = true";
    	$sql .= "      and vpry.user_group_id =".$user_group_id;
    	$sql .= "      and vpry.status =".$status;
    	$sql .= " ) as a ";
    	$sql .= " where  ";
    	$sql .= "     a.status_forward > 0 ";


    	if (env( 'ENV_TYPE' ) === 'staging') {
    		$sql .= " and (a.email like '%bunkaren.or.jp' ";
    		$sql .= "   or a.email like '%k-on.co.jp' ";
    		$sql .= "   or a.email like '%example.com') ";
    	}
    	$sql .= " group by  ";
    	$sql .= "     a.email ";

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = [];

    		$stdObj = new \stdClass();
    		$stdObj->email = "";

    		$all_rec[] = $stdObj;
    	}
    	return $all_rec;
    }
}
