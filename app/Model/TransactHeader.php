<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Facility;
use App\Model\TransactFileStorage;
use App\Model\TransactConfirmation;
use App\Model\TransactDetail;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;

class TransactHeader extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    /*
     * boot
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * データ取得
     * ・取引年月
     * ・施設ID
     * ・供給区分

    public function getData($claim_month, $facility_id, $supply_division)
    {
        return $this->where('claim_month', $claim_month)->where('facility_id', $facility_id)->where('supply_division', $supply_division)->first();
    }
     */
    public function getData($header_id,$user)
    {

    	$sql = $this->getDataSql($header_id, $user);

    	$count_sql = " select count(*) from (".$sql.") as tmp ";
    	$all_count = \DB::select($count_sql);

    	$count2=$all_count[0]->count;
    	$per_page = 1;
    	$offset=0;

   		$sql .=" limit ".$per_page." offset ".$offset;

    	$all_rec = \DB::select($sql);
    	$result = TransactHeader::query()->hydrate($all_rec);
    	// ページ番号が指定されていなかったら１ページ目
    	$page_num = 1;
    	// ページ番号に従い、表示するレコードを切り出す
    	$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

    	// ページャーオブジェクトを生成
    	$pager= new \Illuminate\Pagination\LengthAwarePaginator(
    			$result, // ページ番号で指定された表示するレコード配列
    			$count2, // 検索結果の全レコード総数
    			$per_page, // 1ページ当りの表示数
    			$page_num, // 表示するページ
    			[] // ページャーのリンク先のURLを指定
    			);

    	return $pager;


    	//return $this->where('id', $header_id)->first();
    }

     public function getDataSql($header_id,$user)
    {

    	$sql  = "";
    	$sql .=" select ";
    	$sql .="      a.claim_month ";
    	$sql .="     ,a.data_type_name ";
    	$sql .="     ,a.facility_name ";
    	$sql .="     ,a.trader_name ";
    	$sql .="     ,a.transact_header_id as id";
    	$sql .="     ,a.show_transact_sales_columns";
    	$sql .="     ,a.show_transact_purchase_columns";
    	$sql .="     ,detail.bunkaren_billing_code";
    	$sql .="     ,max(header.all_cnt) as all_cnt";
    	$sql .="     ,count(a.transact_header_id) as detail_count ";
    	$sql .="     ,sum(detail.medicine_price_total) as medicine_price_total ";
    	$sql .="     ,sum(detail.sales_price_total) as sales_price_total ";
    	$sql .="     ,sum(detail.purchase_price_total) as purchase_price_total ";
    	$sql .="     ,max(a.created_at) as created_at ";
    	$sql .=" from ( ";
    	$sql .="     select ";
    	$sql .="          transact_confirmations.claim_month ";
    	$sql .="         ,user_group_supply_divisions.supply_division_name as data_type_name ";
    	$sql .="         ,user_groups.name as facility_name ";
    	$sql .="         ,trader.name as trader_name ";
    	$sql .="         ,transact_confirmations.transact_header_id ";
    	$sql .="         ,transact_confirmations.medicine_price_total ";
    	$sql .="         ,vprtf.show_transact_sales_columns ";
    	$sql .="         ,vprtf.show_transact_purchase_columns ";
    	$sql .="         ,case when vprtf.show_transact_sales_columns = 1 then transact_confirmations.sales_price_total else 0 end as sales_price_total ";
    	$sql .="         ,case when vprtf.show_transact_purchase_columns = 1 then transact_confirmations.purchase_price_total else 0 end as purchase_price_total ";
    	$sql .="         ,transact_confirmations.created_at ";
    	$sql .="         ,f_show_transact_row(transact_confirmations.trader_confirmation_status,vprtf.show_transact_rows_over3,vprtf.show_transact_rows_over5,vprtf.show_transact_rows_over6) as show_row ";
    	$sql .="     from ";
    	$sql .="         transact_confirmations ";
    	$sql .="         inner join v_privileges_row_transact_flow as vprtf";
    	$sql .="             on transact_confirmations.sales_user_group_id = vprtf.user_group_id ";
    	$sql .="             and vprtf.user_id =".$user->id;
    	$sql .="         left join user_group_supply_divisions";
    	$sql .="             on transact_confirmations.supply_division = user_group_supply_divisions.id ";
    	$sql .="         left join user_groups";
    	$sql .="             on transact_confirmations.sales_user_group_id = user_groups.id ";
    	$sql .="         left join user_groups as trader";
    	$sql .="             on transact_confirmations.purchase_user_group_id = trader.id ";
    	$sql .=" ) as a ";
    	$sql .=" left join ( ";
    	$sql .="     select ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,count(*) as all_cnt ";
    	$sql .="     from ";
    	$sql .="         transact_confirmations ";
    	$sql .="     where ";
    	$sql .="         deleted_at is null  ";
    	$sql .="     group by ";
    	$sql .="          transact_header_id ";
    	$sql .=" ) as header ";
    	$sql .="     on a.transact_header_id = header.transact_header_id ";
    	$sql .=" left join ( ";
    	$sql .="     select ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,bunkaren_billing_code ";
    	$sql .="         ,sum(refund_price) as medicine_price_total ";
    	$sql .="         ,sum(sales_price) as sales_price_total ";
    	$sql .="         ,sum(buy_price) as purchase_price_total ";
    	$sql .="     from ";
    	$sql .="          transact_details ";
    	$sql .="     where ";
    	$sql .="         deleted_at is null  ";
    	$sql .="     group by ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,bunkaren_billing_code ";
    	$sql .=" ) as detail ";
    	$sql .="     on a.transact_header_id = detail.transact_header_id ";
    	$sql .=" where ";
    	$sql .="     a.transact_header_id = ".$header_id;
		$sql .="     and show_row=1 ";
    	$sql .=" group by ";
    	$sql .="      a.claim_month ";
    	$sql .="     ,a.data_type_name ";
    	$sql .="     ,a.facility_name ";
    	$sql .="     ,a.trader_name ";
    	$sql .="     ,a.transact_header_id ";
    	$sql .="     ,a.show_transact_sales_columns";
    	$sql .="     ,a.show_transact_purchase_columns";
    	$sql .="     ,detail.bunkaren_billing_code";
    	$sql .=" order by ";
    	$sql .="      a.claim_month desc ";
    	$sql .="     ,a.facility_name ";
    	$sql .="     ,a.data_type_name ";

    	return $sql;
    }



    /*
     * 取引ファイル管理参照IDでリスト取得
     */
    public function getList(Request $request, $user,$count)
    {
        $sql = $this->getListSql($request, $user,$count);

		$count_sql = " select count(*) from (".$sql.") as tmp ";
		$all_count = \DB::select($count_sql);

		$count2=$all_count[0]->count;

		//$sql .=" order by ";
		//$sql .="      a.claim_month desc ";
		//$sql .="     ,a.facility_name ";
		//$sql .="     ,a.data_type_name ";

		$sql .=" order by ";
		$sql .=" th.claim_month desc ";
        $sql .=" ,th.sales_user_group_id ";
        $sql .=" ,th.supply_division ";

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
		$result = TransactHeader::query()->hydrate($all_rec);
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

        //return $this->where('transact_file_storage_id', $transact_file_storage_id)->paginate($count);

    }

 /*
     * 請求一覧SQL
     */
    public function getListSql(Request $request, $user,$count)
    {

    	$sql  = "";
        $sql .=" select ";
        $sql .="     th.id as id ";
        $sql .="     , th.claim_month ";
        $sql .="     , th.supply_division ";
        $sql .="     , th.sales_user_group_id ";
        $sql .="     , max(ugsd.supply_division_name) as supply_division_name ";
        $sql .="     , max(ug.name) as facility_name    ";
    //--     , th.show_transact_sales_columns
    //--     , th.show_transact_purchase_columns
    //--     , max(header.all_cnt) as all_cnt
        $sql .="     , sum(tc.medicine_price_total) as medicine_price_total ";
        $sql .="     , sum(f_show_sales_price_claim(tc.sales_price_total,p.show_sales_price)) as sales_price_total ";
        $sql .="     , sum(f_show_purchase_price(tc.purchase_price_total,p.show_purchase_price)) as purchase_price_total ";
        $sql .="     , sum(case when tc.facility_confirmation_status>=20 then 1 else 0 end) facility_confirmed_count ";
        $sql .="     , sum(case when tc.trader_confirmation_status>=20 then 1 else 0 end) trader_confirmed_count ";
        $sql .="     , count(tc.id) all_confirmed_count ";
        $sql .="     , max(th.created_at) as created_at  ";
        //$sql .="     , max(p.show_sales_price_waku) show_sales_price_waku ";
        //$sql .="     , max(p.show_purchase_price_waku) show_purchase_price_waku ";
        $sql .="     , th.attachment_send_file_name ";
        $sql .="     , th.attachment_receive_file_name ";
        // 20201027SQL変更分項目追加→20201112変更
        //$sql .="     , case when max(pb.facility_status_foward)>=0 or max(pb.trader_status_foward)>=0 then 1 else 0 end as flg_in_charge_of_transact_header ";
        $sql .="     , case when max(f_claim_check_forward_facility_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_faci, arrow10_faci_at_trad_over30, p.arrow20_faci, p.arrow20_faci_at_trad_over30, p.arrow30_faci, p.arrow40_faci, p.arrow50_faci, p.arrow60_faci))>=0  ";
        $sql .="        or max(f_claim_check_forward_trader_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_trad, arrow10_trad_at_faci_over30, p.arrow20_trad, p.arrow20_trad_at_faci_over30, p.arrow30_trad, p.arrow40_trad, p.arrow50_trad, p.arrow60_trad))>=0  ";
        $sql .="        then 1 else 0 end as flg_in_charge_of_transact_header ";

        // 20201111登録ボタン権限制御変更
        $sql .="     , max(p.up_facility_invoice) up_facility_invoice ";
        $sql .="     , max(p.del_facility_invoice) del_facility_invoice ";
        $sql .="     , max(p.up_facility_confirmed_invoice) up_facility_confirmed_invoice ";
        $sql .="     , max(p.del_facility_confirmed_invoice) del_facility_confirmed_invoice ";
        $sql .=" from ";
        $sql .="     transact_headers th  ";
        //$sql .="     left join transact_confirmations tc  ";
        $sql .="     inner join transact_confirmations tc  "; // 20201027SQL変更反映
        $sql .="         on th.id = tc.transact_header_id  ";
        $sql .="         and tc.deleted_at is null ";
        // 20201127 ビューから関数へ変更
        //$sql .="     inner join v_privileges_row_claim_check p ";
        $sql .="       inner join f_privileges_row_claim_check(". $user->id .") p ";
        $sql .="         on tc.sales_user_group_id = p.sales_user_group_id  ";
        $sql .="         and tc.purchase_user_group_id = p.purchase_user_group_id  ";
        //$sql .="        and p.user_id = " .$user->id;
        // 20201027SQL変更分追加 start →20201112削除
        //$sql .="     inner join v_privileges_row_claim_check_bystate pb  ";
        //$sql .="       on tc.sales_user_group_id = pb.sales_user_group_id  ";
        //$sql .="        and tc.purchase_user_group_id = pb.purchase_user_group_id  ";
        //$sql .="        and pb.user_id = " .$user->id;
        //$sql .="        and pb.facility_status = tc.facility_confirmation_status  ";
        //$sql .="        and pb.trader_status = tc.trader_confirmation_status  ";
        // 20201027SQL変更分追加 end
        $sql .="     left join user_group_supply_divisions ugsd  ";
        $sql .="         on th.supply_division = ugsd.id  ";
        $sql .="     left join user_groups ug  ";
        $sql .="        on tc.sales_user_group_id = ug.id  ";
        $sql .=" where ";
        $sql .="     1 = 1  ";
        $sql .="     and p.show_claim_check_rows = 1 ";
        $sql .="    and th.deleted_at is null ";


		if (!empty($request->id)) {
			$sql .=" and th.id=".$request->id;

		}

		// 状態
		//if (!empty($request->status)) {
		//	$sql .= " and a.trader_confirmation_status in (".implode(',', $request->status).")";
		//}

        // 施設
        if (!empty($request->user_group)) {
		     $sql .= " and th.sales_user_group_id in (".implode(',', $request->user_group).")";
		}

		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .= " and th.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .= " and th.claim_month <= '".$request->claim_month_to.'/01'."'";
		}

		// 供給区分
		if (!empty($request->supply_division)) {
			//データ区分名取得
			$dsql  = "select supply_division_name from user_group_supply_divisions where id=".$request->supply_division;
			$dataTypeRec = \DB::select($dsql);

			$sql .= " and supply_division_name = '".$dataTypeRec[0]->supply_division_name."'";
		}

		$sql .=" group by ";
    	$sql .="     th.claim_month ";
      	$sql .="   , th.supply_division ";
     	$sql .="    , th.sales_user_group_id ";
     	$sql .="    , th.id  ";

		return $sql;

    }


 /*
     * 請求一覧SQL
     */
    public function getListSql_old(Request $request, $user,$count)
    {

    	$sql  = "";
    	$sql .=" select ";
    	//TODO ここだけ変える
    	$sql .="     th.id as id ";
    	//$sql .="     th.id as header_id ";
    	$sql .="     , th.claim_month ";
    	$sql .="     , th.supply_division ";
    	$sql .="     , th.sales_user_group_id ";
    	$sql .="     , max(ugsd.supply_division_name) as supply_division_name ";
    	$sql .="     , max(ug.name) as facility_name         ";
    //--     , th.show_transact_sales_columns
    //--     , th.show_transact_purchase_columns
    //--     , max(header.all_cnt) as all_cnt
    	$sql .="  , sum(tc.medicine_price_total) as medicine_price_total ";
    	$sql .="     , sum(tc.sales_price_total) as sales_price_total ";
    	$sql .="     , sum(tc.purchase_price_total) as purchase_price_total ";
    	$sql .="     , sum(case when tc.facility_confirmation_status>=20 then 1 else 0 end) facility_confirmed_count ";
    	$sql .="     , sum(case when tc.trader_confirmation_status>=20 then 1 else 0 end) trader_confirmed_count ";
    	$sql .="     , count(tc.id) all_confirmed_count ";
    	$sql .="     , max(th.created_at) as created_at  ";
    	$sql .=" from ";
    	$sql .="     transact_headers th  ";
    	$sql .="     left join transact_confirmations tc  ";
    	$sql .="         on th.id = tc.transact_header_id  ";
    	$sql .="     left join v_privileges_row_claim_check pf  ";
    	$sql .="         on tc.sales_user_group_id = pf.user_group_id  ";
    	$sql .="         and pf.user_id = " .$user->id;
    	$sql .="     left join v_privileges_row_claim_check pt  ";
    	$sql .="         on tc.purchase_user_group_id = pf.user_group_id  ";
    	$sql .="         and pf.user_id = " .$user->id;
    	$sql .="     left join user_group_supply_divisions ugsd  ";
    	$sql .="         on th.supply_division = ugsd.id  ";
    	$sql .="     left join user_groups ug  ";
    	$sql .="         on tc.sales_user_group_id = ug.id  ";
    	$sql .=" where ";
    	$sql .="     1 = 1  ";
//--     and (
//--         pf.show_claim_check_rows = 1
//--         or pt.show_claim_check_rows = 1
//--     )
		//$sql .=" where ";
		//$sql .="     1=1";
		//$sql .="     and show_row=1 ";

		if (!empty($request->id)) {
			$sql .=" and th.id=".$request->id;

		}

		// 状態
		//if (!empty($request->status)) {
		//	$sql .= " and a.trader_confirmation_status in (".implode(',', $request->status).")";
		//}

		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .= " and th.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .= " and th.claim_month <= '".$request->claim_month_to.'/01'."'";
		}

		// 供給区分
		if (!empty($request->supply_division)) {
			//データ区分名取得
			$dsql  = "select supply_division_name from user_group_supply_divisions where id=".$request->supply_division;
			$dataTypeRec = \DB::select($dsql);

			$sql .= " and th.supply_division_name = '".$dataTypeRec[0]->supply_division_name."'";
		}

		//$sql .=" group by ";
		//$sql .="      a.claim_month ";
		//$sql .="     ,a.data_type_name ";
		//$sql .="     ,a.facility_name ";
		//$sql .="     ,a.transact_header_id ";
		//$sql .="     ,a.show_transact_sales_columns";
		//$sql .="     ,a.show_transact_purchase_columns";
		//$sql .="     ,detail.bunkaren_billing_code";

		$sql .=" group by ";
    	$sql .="     th.claim_month ";
      	$sql .="   , th.supply_division ";
     	$sql .="    , th.sales_user_group_id ";
     	$sql .="    , th.id  ";

		return $sql;

    }


    /*
     * 請求一覧SQL
     */
    public function getListSql_old2(Request $request, $user,$count)
    {

    	$sql  = "";
    	$sql .=" select ";
    	$sql .="      a.claim_month ";
    	$sql .="     ,a.data_type_name ";
    	$sql .="     ,a.facility_name ";
    	$sql .="     ,a.transact_header_id as id";
    	$sql .="     ,a.show_transact_sales_columns";
    	$sql .="     ,a.show_transact_purchase_columns";
    	$sql .="     ,detail.bunkaren_billing_code";
    	$sql .="     ,max(header.all_cnt) as all_cnt";
    	$sql .="     ,count(a.transact_header_id) as detail_count ";
    	$sql .="     ,max(detail.medicine_price_total) as medicine_price_total ";
    	$sql .="     ,max(detail.sales_price_total) as sales_price_total ";
    	$sql .="     ,max(detail.purchase_price_total) as purchase_price_total ";
/*
    	$sql .="     ,sum(a.medicine_price_total) as medicine_price_total ";
    	$sql .="     ,sum(a.sales_price_total) as sales_price_total ";
    	$sql .="     ,sum(a.purchase_price_total) as purchase_price_total ";
*/
    	$sql .="     ,max(a.created_at) as created_at ";
    	$sql .=" from ( ";
    	$sql .="     select ";
    	$sql .="          transact_confirmations.claim_month ";
    	$sql .="         ,user_group_supply_divisions.supply_division_name as data_type_name ";
    	$sql .="         ,user_groups.name as facility_name ";
    	$sql .="         ,transact_confirmations.transact_header_id ";
    	$sql .="         ,transact_confirmations.medicine_price_total ";
    	$sql .="         ,vprtf.show_transact_sales_columns ";
    	$sql .="         ,vprtf.show_transact_purchase_columns ";
    	$sql .="         ,case when vprtf.show_transact_sales_columns = 1 then transact_confirmations.sales_price_total else 0 end as sales_price_total ";
    	$sql .="         ,case when vprtf.show_transact_purchase_columns = 1 then transact_confirmations.purchase_price_total else 0 end as purchase_price_total ";
    	$sql .="         ,transact_confirmations.created_at ";
    	$sql .="         ,transact_confirmations.trader_confirmation_status ";
    	$sql .="         ,f_show_transact_row(transact_confirmations.trader_confirmation_status,vprtf.show_transact_rows_over3,vprtf.show_transact_rows_over5,vprtf.show_transact_rows_over6) as show_row ";
    	$sql .="     from ";
    	$sql .="         transact_confirmations ";
    	$sql .="         left join v_privileges_row_transact_flow as vprtf";
    	$sql .="             on transact_confirmations.sales_user_group_id = vprtf.user_group_id ";
    	$sql .="             and vprtf.user_id =".$user->id;
    	$sql .="         left join user_group_supply_divisions";
    	$sql .="             on transact_confirmations.supply_division = user_group_supply_divisions.id ";
    	$sql .="         left join user_groups";
    	$sql .="             on transact_confirmations.sales_user_group_id = user_groups.id ";
    	$sql .="         left join v_privileges_row_transact_flow as vprtfp";
    	$sql .="             on transact_confirmations.purchase_user_group_id = vprtfp.user_group_id ";
    	$sql .="             and vprtfp.user_id =".$user->id;
    	$sql .="     where ";
    	$sql .="         vprtf.user_id is not null or vprtfp.user_id is not null ";
    	$sql .=" ) as a ";
    	$sql .=" left join ( ";
    	$sql .="     select ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,count(*) as all_cnt ";
    	$sql .="         ,sum(medicine_price_total) as medicine_price_total ";
    	$sql .="         ,sum(sales_price_total) as sales_price_total ";
    	$sql .="         ,sum(purchase_price_total) as purchase_price_total ";
    	$sql .="     from ";
    	$sql .="         transact_confirmations ";
    	$sql .="     where ";
    	$sql .="         deleted_at is null  ";
    	$sql .="     group by ";
    	$sql .="          transact_header_id ";
    	$sql .=" ) as header ";
    	$sql .="     on a.transact_header_id = header.transact_header_id ";
    	$sql .=" left join ( ";
    	$sql .="     select ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,bunkaren_billing_code ";
    	$sql .="         ,sum(refund_price) as medicine_price_total ";
    	$sql .="         ,sum(sales_price) as sales_price_total ";
    	$sql .="         ,sum(buy_price) as purchase_price_total ";
    	$sql .="     from ";
    	$sql .="          transact_details ";
    	$sql .="     where ";
    	$sql .="         deleted_at is null  ";
    	$sql .="     group by ";
    	$sql .="          transact_header_id ";
    	$sql .="         ,bunkaren_billing_code ";
    	$sql .=" ) as detail ";
    	$sql .="     on a.transact_header_id = detail.transact_header_id ";


		$sql .=" where ";
		$sql .="     1=1";
		$sql .="     and show_row=1 ";

		if (!empty($request->id)) {
			$sql .=" and a.transact_header_id=".$request->id;

		}

		// 状態
		if (!empty($request->status)) {
			$sql .= " and a.trader_confirmation_status in (".implode(',', $request->status).")";
		}

		// 対象年月
		if (!empty($request->claim_month_from)) {
			$sql .= " and a.claim_month >= '".$request->claim_month_from.'/01'."'";
		}
		// 対象年月
		if (!empty($request->claim_month_to)) {
			$sql .= " and a.claim_month <= '".$request->claim_month_to.'/01'."'";
		}

		// 供給区分
		if (!empty($request->supply_division)) {
			//データ区分名取得
			$dsql  = "select supply_division_name from user_group_supply_divisions where id=".$request->supply_division;
			$dataTypeRec = \DB::select($dsql);

			$sql .= " and a.data_type_name = '".$dataTypeRec[0]->supply_division_name."'";
		}

		$sql .=" group by ";
		$sql .="      a.claim_month ";
		$sql .="     ,a.data_type_name ";
		$sql .="     ,a.facility_name ";
		$sql .="     ,a.transact_header_id ";
		$sql .="     ,a.show_transact_sales_columns";
		$sql .="     ,a.show_transact_purchase_columns";
		$sql .="     ,detail.bunkaren_billing_code";

		return $sql;

    }

    /*
     * 施設を取得
     */
    public function facility()
    {
        return $this->belongsTo('App\Model\Facility')->first();
    }

    /*
     * 請求支払を取得
     */
    public function transactConfirmation()
    {
        return $this->hasMany('App\Model\TransactConfirmation');
    }

    /*
     * 請求支払の薬価金額合計を取得
     */
    public function medicine_price_total()
    {
        return $this->transactConfirmation()->groupBy('transact_header_id')->sum('medicine_price_total');
    }

    /*
     * 請求支払の売上金額合計を取得
     */
    public function sales_price_total()
    {
        return $this->transactConfirmation()->groupBy('transact_header_id')->sum('sales_price_total');
    }

    /*
     * 請求支払の仕入金額合計を取得
     */
    public function purchase_price_total()
    {
        return $this->transactConfirmation()->groupBy('transact_header_id')->sum('purchase_price_total');
    }

    /*
     * 供給区分一覧を取得
     */
    public function getSupplyDevisionList($user_group_id)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      user_group_supply_divisions.id ";
    	$sql .= "     ,user_group_supply_divisions.supply_division_name ";
    	$sql .= " from ";
    	$sql .= "     user_group_supply_divisions ";
    	$sql .= " where ";
    	$sql .= "     user_group_supply_divisions.deleted_at is null ";
    	$sql .= "     and user_group_supply_divisions.user_group_id = ".$user_group_id;
    	$sql .= " order by ";
    	$sql .= "     user_group_supply_divisions.disp_order ";

    	$all_rec = \DB::select($sql);
    	$result = TransactHeader::query()->hydrate($all_rec);

    	return $result;
    }

    /*
     * データ区分名を取得
     */
    public function getDataTypeName($id)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      user_group_data_types.id ";
    	$sql .= "     ,user_group_data_types.data_type_name ";
    	$sql .= " from ";
    	$sql .= "     user_group_data_types ";
    	$sql .= " where ";
    	$sql .= "     user_group_data_types.id = ".$id;

    	$all_rec = \DB::select($sql);
    	$result = TransactFileStorage::query()->hydrate($all_rec);

    	if (empty($result)) {
    		return;
    	} else {
    		return $result[0]->data_type_name;
    	}

    }

}
