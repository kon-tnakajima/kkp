<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Facility;
use App\Model\TransactFileStorage;
use App\Model\TransactDetail;
use App\Model\BaseModel;
use App\Observers\AuthorObserver;

class TransactConfirmation extends BaseModel {
    use SoftDeletes;
    protected $guarded = array('id'); // 代入を許可しないリスト

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot() {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * データ取得
     * ・取引年月
     * ・施設ID
     * ・業者ID
     * ・供給区分
     */
    public function getData($claim_month, $facility_id, $trader_id, $supply_division) {
        logger('TransactConfirmation#getData');
        return $this->where('claim_month', $claim_month)->where('facility_id', $facility_id)->where('trader_id', $trader_id)->where('supply_division', $supply_division)->first();
    }

    /*
     * データリスト取得
     */
    public function getDataList($transact_file_storage_id) {
        logger('TransactConfirmation#getDataList');
        return $this->where('transact_file_storage_id', $transact_file_storage_id)->get();
    }


    /*
     * 取引ファイル管理データIDで取得
     */
    public function getList($transact_header_id,$user, $count) {
        logger('TransactConfirmation#getList');
        $sql  = $this->getListSql($transact_header_id, $user,$count, null);

        $count_sql = " select count(*) from (".$sql.") as tmp ";
        $all_count = \DB::select($count_sql);

        $count2=$all_count[0]->count;

        $sql .= " order by ";
        $sql .= " tc.claim_month desc ";
        $sql .= " , tc.sales_user_group_id ";
        $sql .= " , tc.purchase_user_group_id ";
        $sql .= " , tc.supply_division ";

        $all_rec = \DB::select($sql);
        $result = TransactConfirmation::query()->hydrate($all_rec);
        // ページ番号が指定されていなかったら１ページ目
        $page_num = 1;
        $per_page = 100;
        // ページ番号に従い、表示するレコードを切り出す
        $disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

        // ページャーオブジェクトを生成
        $pager= new \Illuminate\Pagination\LengthAwarePaginator(
                $result, // ページ番号で指定された表示するレコード配列
                $count2, // 検索結果の全レコード総数
                $per_page, // 1ページ当りの表示数
                $page_num
                );

        return $pager;
    }

    /*
     * 請求一覧 明細別のヘッダー用(業者別明細のSQLを流用)
    */
    public function getDetailHeader($transact_header_id, $user, $confirmation_id) {
        logger('TransactConfirmation#getDetailHeader');
        $sql  = $this->getListSql($transact_header_id, $user, -1, $confirmation_id);

        $count_sql = " select count(*) from (".$sql.") as tmp ";
        $all_count = \DB::select($count_sql);

        $count2=$all_count[0]->count;

        $sql .= " order by ";
        $sql .= " tc.claim_month desc ";
        $sql .= " , tc.sales_user_group_id ";
        $sql .= " , tc.purchase_user_group_id ";
        $sql .= " , tc.supply_division ";

        $all_rec = \DB::select($sql);

        return $all_rec[0];
    }

    /*
     * 請求照会(業者別明細)
     */
    public function getListSql($transact_header_id,$user, $count, $confirmation_id) {
        logger('TransactConfirmation#getListSql');
        $sql = " select ";
        $sql .= " tc.transact_header_id";
        $sql .= " , tc.id ";
        $sql .= " , tc.sales_user_group_id ";
        $sql .= " , tc.purchase_user_group_id ";
        $sql .= " , tc.claim_month ";
        $sql .= " , trader.name as trader_name";
        $sql .= " , facility.name as facility_name ";
        $sql .= " , ugsd.supply_division_name as supply_division_name";
        $sql .= " , tc.detail_count ";
        $sql .= " , tc.medicine_price_total ";
        $sql .= " , f_show_sales_price_claim(tc.sales_price_total, p.show_sales_price) as sales_price_total ";
        $sql .= " , f_show_purchase_price(tc.purchase_price_total, p.show_purchase_price) as purchase_price_total ";
        $sql .= " , tc.facility_confirmation_status ";
        $sql .= " , ta_fa.name facility_confirmation_status_name ";
        $sql .= " , ta_tr.name trader_confirmation_status_name ";
        $sql .= " , tc.trader_confirmation_status ";
        $sql .= " , facility_user.name facility_confirmation_user_name ";
        $sql .= " , trader_user.name trader_confirmation_user_name ";
        $sql .= " , case when unread_comments.count is null then 0 else unread_comments.count end unread_comments_count ";
        $sql .= " , case when read_comments.count is null then 0 else read_comments.count end read_comments_count ";
        $sql .= " , f_claim_check_forward_facility_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_faci, arrow10_faci_at_trad_over30, p.arrow20_faci, p.arrow20_faci_at_trad_over30, p.arrow30_faci, p.arrow40_faci, p.arrow50_faci, p.arrow60_faci) AS facility_status_foward ";
        $sql .= " , ansf_f.action_name AS action_name_facility_status_foward ";
        $sql .= " , f_claim_check_remand_facility_status(tc.facility_confirmation_status, p.remand5_faci, p.remand10_faci, p.remand20_faci, p.remand30_faci, p.remand40_faci) AS facility_status_remand ";
        $sql .= " , f_claim_check_withdraw_facility_status(tc.facility_confirmation_status, p.withdraw1_faci, p.withdraw10_faci, p.withdraw20_faci, p.withdraw30_faci, p.withdraw40_faci) AS facility_status_withdraw ";
        $sql .= " , f_claim_check_forward_trader_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_trad, arrow10_trad_at_faci_over30, p.arrow20_trad, p.arrow20_trad_at_faci_over30, p.arrow30_trad, p.arrow40_trad, p.arrow50_trad, p.arrow60_trad) AS trader_status_foward ";
        $sql .= " , ansf_t.action_name AS action_name_trader_status_foward ";
        $sql .= " , f_claim_check_remand_trader_status(tc.trader_confirmation_status, p.remand5_trad, p.remand10_trad, p.remand20_trad, p.remand30_trad, p.remand40_trad) AS trader_status_remand ";
        $sql .= " , f_claim_check_withdraw_trader_status(tc.trader_confirmation_status, p.withdraw1_trad, p.withdraw10_trad, p.withdraw20_trad, p.withdraw30_trad, p.withdraw40_trad) AS trader_status_withdraw ";
        // $sql .= " , '' attachment_bunkaren ";
        // $sql .= " , '' attachment_trader ";
        // $sql .= " , '' facility_coment ";
        // $sql .= " , '' trader_comment ";
        $sql .= " , p.show_sales_price_waku ";
        $sql .= " , p.show_purchase_price_waku  ";
        $sql .= " , tc.attachment_send_file_name ";
        $sql .= " , tc.attachment_receive_file_name  ";
        $sql .= " , case when f_claim_check_forward_facility_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_faci, arrow10_faci_at_trad_over30, p.arrow20_faci, p.arrow20_faci_at_trad_over30, p.arrow30_faci, p.arrow40_faci, p.arrow50_faci, p.arrow60_faci) > 0";
        $sql .= "    then 1 else 0 end as flg_in_charge_of_transact_confirmation_facility";

        $sql .= " , case when f_claim_check_forward_trader_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_trad, arrow10_trad_at_faci_over30, p.arrow20_trad, p.arrow20_trad_at_faci_over30, p.arrow30_trad, p.arrow40_trad, p.arrow50_trad, p.arrow60_trad) > 0 ";
        $sql .= "    then 1 else 0 end as flg_in_charge_of_transact_confirmation_trader";
        $sql .= ", ansf_f.user_guide_text AS user_guide_text_facility";
        $sql .= ", ansf_t.user_guide_text AS user_guide_text_trader";

        $sql .= " from transact_confirmations tc  ";
        $sql .= " inner join f_privileges_row_claim_check(" . $user->id . ") p ";
        $sql .= "   on tc.sales_user_group_id = p.sales_user_group_id  ";
        $sql .= "   and tc.purchase_user_group_id = p.purchase_user_group_id  ";
        // 施設追加
        $sql .= " left join user_groups as facility ";
        $sql .= " on facility.id = tc.sales_user_group_id ";
        $sql .= " left join user_groups as trader  ";
        $sql .= "   on trader.id = tc.purchase_user_group_id  ";
        $sql .= " left join users as facility_user  ";
        $sql .= "   on facility_user.id = tc.facility_confirmation_account  ";
        $sql .= " left join users as trader_user  ";
        $sql .= "   on trader_user.id = tc.trader_confirmation_account  ";
        // 供給区分用に追加
        $sql .= " left join user_group_supply_divisions ugsd  ";
        $sql .= "   on tc.supply_division = ugsd.id ";

        $sql .= " left join (  ";
        $sql .= "   select ";
        $sql .= "   transact_confirmation_id ";
        $sql .= "   ,COUNT(transact_confirmation_id) as count  ";
        $sql .= "   from ";
        $sql .= "   claim_history_comments chc1  ";
        $sql .= "   where ";
        $sql .= "    chc1.read_flg = false  ";
        $sql .= "    and chc1.deleted_at is null  ";
        $sql .= "   group by ";
        $sql .= "    chc1.transact_confirmation_id ";
        $sql .= "   ) as unread_comments  ";
        $sql .= "     on tc.id = unread_comments.transact_confirmation_id  ";
        $sql .= " left join (  ";
        $sql .= "   select ";
        $sql .= "    transact_confirmation_id ";
        $sql .= "    , COUNT(transact_confirmation_id) as count  ";
        $sql .= "   from ";
        $sql .= "   claim_history_comments chc2  ";
        $sql .= "   where ";
        $sql .= "    chc2.read_flg = true  ";
        $sql .= "    and chc2.deleted_at is null  ";
        $sql .= "    group by ";
        $sql .= "    chc2.transact_confirmation_id ";
        $sql .= " ) as read_comments  ";
        $sql .= "   on tc.id = read_comments.transact_confirmation_id  ";

        $sql .= "     LEFT JOIN action_name_status_forwards ansf_f  ";
        $sql .= "      ON f_claim_check_forward_facility_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_faci, arrow10_faci_at_trad_over30, p.arrow20_faci, p.arrow20_faci_at_trad_over30, p.arrow30_faci, p.arrow40_faci, p.arrow50_faci, p.arrow60_faci) = ansf_f.status_forward   ";
        $sql .= "      AND ansf_f.apply_payment_flg = 5  ";
        $sql .= "     LEFT JOIN action_name_status_forwards ansf_t   ";
        $sql .= "      ON f_claim_check_forward_trader_status(tc.facility_confirmation_status, tc.trader_confirmation_status, arrow10_trad, arrow10_trad_at_faci_over30, p.arrow20_trad, p.arrow20_trad_at_faci_over30, p.arrow30_trad, p.arrow40_trad, p.arrow50_trad, p.arrow60_trad) = ansf_t.status_forward   ";
        $sql .= "      AND ansf_t.apply_payment_flg = 4  ";

        $sql .= "      left join tasks ta_fa ";
        $sql .= "      on  ta_fa.status = tc.facility_confirmation_status ";
        $sql .= "      and ta_fa.apply_payment_flg = 5 ";
        $sql .= "      and ta_fa.deleted_at is null ";

        $sql .= "      left join tasks ta_tr ";
        $sql .= "      on  ta_tr.status = tc.trader_confirmation_status ";
        $sql .= "      and ta_tr.apply_payment_flg = 4 ";
        $sql .= "      and ta_tr.deleted_at is null ";

        $sql .= " where ";
        $sql .= "  1 = 1  ";
        $sql .= "  and tc.deleted_at is null  ";
        $sql .= "  and p.show_claim_check_rows = 1 ";
        $sql .= "  and tc.transact_header_id = ".$transact_header_id;
        if (!empty($confirmation_id)) {
            $sql .= "  and tc.id = ".$confirmation_id;
        }

        return $sql;
    }

	/*
     * 請求照会_業者別ユーザーガイド文
     */
    public function getUserGuide($transact_header_id,$user)
    {
		$sql  = "SELECT * FROM";
        $sql .= " f_user_guide_claim_confirmation_facility('" .$user->id."','". $transact_header_id . "')";
        logger($sql);
        logger($user->id);
		$user_guide = $this->simple_execute_sql($sql);
        return $user_guide;
	}



    /*
     * 請求照会(業者別明細)
     */
    public function getListSql_old($transact_header_id,$user, $count) {
        logger('TransactConfirmation#getListSql_old');
        $sql = " select ";
        $sql .= "     * ";
        $sql .= " from ( ";
        $sql .= "     select ";
        $sql .= "          transact_confirmations.id ";
        $sql .= "         ,trader.name as trader_name ";
        $sql .= "         ,transact_confirmations.detail_count ";
        $sql .= "         ,detail.medicine_price_total ";
        $sql .= "         ,detail.sales_price_total ";
        $sql .= "         ,detail.purchase_price_total ";
        $sql .= "         ,transact_confirmations.trader_confirmation_status ";
        $sql .= "         ,transact_confirmations.reject_comment ";
        $sql .= "         ,users.name as facility_confirmation_account_name ";
        $sql .= "         ,unread_comments.count as unread_comment_count ";
        $sql .= "         ,read_comments.count as read_comment_count ";
        $sql .= "         ,transact_confirmations.transact_header_id  ";
        $sql .= "         ,vprtf.show_transact_sales_columns ";
        $sql .= "         ,vprtf.show_transact_purchase_columns ";
        $sql .= "         ,vprtf.show_row ";
        $sql .= "         ,vprtf.status_forward ";
        $sql .= "         ,vprtf.status_back ";
        $sql .= "         ,vprtf.status_back_text ";
        $sql .= "         ,detail.bunkaren_payment_code ";
        $sql .= "     from ";
        $sql .= "         transact_confirmations ";
        $sql .= "         left join v_privileges_row_transact_flow2_bystate as vprtf";
        $sql .= "             on transact_confirmations.sales_user_group_id = vprtf.user_group_id ";
        $sql .= "             and vprtf.user_id =".$user->id;
        $sql .= "             and vprtf.status = transact_confirmations.trader_confirmation_status";
        $sql .= "         left join v_privileges_row_transact_flow2_bystate as vprtfp";
        $sql .= "             on transact_confirmations.purchase_user_group_id = vprtfp.user_group_id ";
        $sql .= "             and vprtfp.user_id =".$user->id;
        $sql .= "             and vprtfp.status = transact_confirmations.trader_confirmation_status";
        $sql .= "         left join transact_headers ";
        $sql .= "             on transact_headers.id = transact_confirmations.transact_header_id ";
        $sql .= "         left join user_groups as trader ";
        $sql .= "             on trader.id = transact_confirmations.purchase_user_group_id ";
        $sql .= "         left join users as users ";
        $sql .= "             on users.id = transact_confirmations.facility_confirmation_account ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "                 ,COUNT(transact_confirmation_id) as count  ";
        $sql .= "             from ";
        $sql .= "                 claim_history_comments ";
        $sql .= "             where ";
        $sql .= "                 read_flg = false ";
        $sql .= "                 and deleted_at is null ";
        $sql .= "             group by ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "         ) as unread_comments ";
        $sql .= "             on transact_confirmations.id = unread_comments.transact_confirmation_id ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "                 ,COUNT(transact_confirmation_id) as count  ";
        $sql .= "             from ";
        $sql .= "                 claim_history_comments ";
        $sql .= "             where ";
        $sql .= "                 read_flg = true ";
        $sql .= "                 and deleted_at is null ";
        $sql .= "             group by ";
        $sql .= "                 transact_confirmation_id ";
        $sql .= "         ) as read_comments ";
        $sql .= "             on transact_confirmations.id = read_comments.transact_confirmation_id ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_header_id ";
        $sql .= "                 ,purchase_user_group_id ";
        $sql .= "                 ,bunkaren_payment_code ";
        $sql .= "                 ,sum(refund_price) as medicine_price_total ";
        $sql .= "                 ,sum(sales_price) as sales_price_total ";
        $sql .= "                 ,sum(buy_price) as purchase_price_total ";
        $sql .= "             from ";
        $sql .= "                   transact_details ";
        $sql .= "             where ";
        $sql .= "                  deleted_at is null  ";
        $sql .= "             group by ";
        $sql .= "                  transact_header_id ";
        $sql .= "                 ,purchase_user_group_id ";
        $sql .= "                 ,bunkaren_payment_code ";
        $sql .= "         ) as detail ";
        $sql .= "             on transact_confirmations.transact_header_id = detail.transact_header_id ";
        $sql .= "             and transact_confirmations.purchase_user_group_id = detail.purchase_user_group_id ";
        $sql .= "     where ";
        $sql .= "         transact_headers.id = ".$transact_header_id;
        $sql .= "         and transact_confirmations.deleted_at is null ";
        $sql .= "         and (vprtf.user_id is not null or vprtfp.user_id is not null) ";
        $sql .= " ) as a ";
        $sql .= " where ";
        $sql .= "     1=1 ";
        $sql .= "     and a.show_row=1 ";

        return $sql;
    }

    /*
     * 取引ファイル管理データIDで取得
     */
    public function getListById($id,$user) {
        logger('TransactConfirmation#getListById');
        $sql = " select ";
        $sql .= "     * ";
        $sql .= " from ( ";
        $sql .= "     select ";
        $sql .= "          transact_confirmations.id ";
        $sql .= "         ,trader.name as trader_name ";
        $sql .= "         ,transact_confirmations.detail_count ";
        $sql .= "         ,transact_confirmations.medicine_price_total ";
        $sql .= "         ,transact_confirmations.sales_price_total ";
        $sql .= "         ,transact_confirmations.purchase_price_total ";
        $sql .= "         ,transact_confirmations.trader_confirmation_status ";
        $sql .= "         ,transact_confirmations.reject_comment ";
        $sql .= "         ,users.name as facility_confirmation_account_name ";
        $sql .= "         ,unread_comments.count as unread_comment_count ";
        $sql .= "         ,read_comments.count as read_comment_count ";
        $sql .= "         ,transact_confirmations.transact_header_id  ";
        $sql .= "         ,vprtf.show_transact_sales_columns ";
        $sql .= "         ,vprtf.show_transact_purchase_columns ";
        $sql .= "         ,detail.bunkaren_payment_code ";
        $sql .= "         ,f_show_transact_row(transact_confirmations.trader_confirmation_status,vprtf.show_transact_rows_over3,vprtf.show_transact_rows_over5,vprtf.show_transact_rows_over6) as show_row ";
        $sql .= "         ,f_transact_task_next(transact_confirmations.trader_confirmation_status,coalesce(transact_confirmations.detail_count,0),vprtf.transact_allow2,vprtf.transact_allow3,vprtf.transact_allow4,vprtf.transact_allow5,vprtf.transact_allow6,vprtf.transact_allow7,vprtf.transact_allow8) as next_status  ";
        $sql .= "         ,f_transact_task_reject(transact_confirmations.trader_confirmation_status,coalesce(transact_confirmations.detail_count,0),vprtf.transact_reject1,vprtf.transact_reject5,vprtf.transact_reject6) as reject_status  ";
        $sql .= "         ,f_transact_task_withdraw(transact_confirmations.trader_confirmation_status,coalesce(transact_confirmations.detail_count,0),vprtf.transact_withdraw1,vprtf.transact_withdraw3,vprtf.transact_withdraw4,vprtf.transact_withdraw5) as withdraw_status  ";
        $sql .= "     from ";
        $sql .= "         transact_confirmations ";
        $sql .= "         inner join v_privileges_row_transact_flow as vprtf";
        $sql .= "             on transact_confirmations.sales_user_group_id = vprtf.user_group_id ";
        $sql .= "             and vprtf.user_id =".$user->id;
        $sql .= "         left join transact_headers ";
        $sql .= "             on transact_headers.id = transact_confirmations.transact_header_id ";
        $sql .= "         left join user_groups as trader ";
        $sql .= "             on trader.id = transact_confirmations.purchase_user_group_id ";
        $sql .= "         left join users as users ";
        $sql .= "             on users.id = transact_confirmations.facility_confirmation_account ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "                 ,COUNT(transact_confirmation_id) as count  ";
        $sql .= "             from ";
        $sql .= "                 claim_history_comments ";
        $sql .= "             where ";
        $sql .= "                 read_flg = false ";
        $sql .= "                 and deleted_at is null ";
        $sql .= "             group by ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "         ) as unread_comments ";
        $sql .= "             on transact_confirmations.id = unread_comments.transact_confirmation_id ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_confirmation_id ";
        $sql .= "                 ,COUNT(transact_confirmation_id) as count  ";
        $sql .= "             from ";
        $sql .= "                 claim_history_comments ";
        $sql .= "             where ";
        $sql .= "                 read_flg = true ";
        $sql .= "                 and deleted_at is null ";
        $sql .= "             group by ";
        $sql .= "                 transact_confirmation_id ";
        $sql .= "         ) as read_comments ";
        $sql .= "             on transact_confirmations.id = read_comments.transact_confirmation_id ";
        $sql .= "         left join ( ";
        $sql .= "             select ";
        $sql .= "                  transact_header_id ";
        $sql .= "                 ,purchase_user_group_id ";
        $sql .= "                 ,bunkaren_payment_code ";
        $sql .= "             from ";
        $sql .= "                   transact_details ";
        $sql .= "             where ";
        $sql .= "                  deleted_at is null  ";
        $sql .= "             group by ";
        $sql .= "                  transact_header_id ";
        $sql .= "                 ,purchase_user_group_id ";
        $sql .= "                 ,bunkaren_payment_code ";
        $sql .= "         ) as detail ";
        $sql .= "             on transact_confirmations.transact_header_id = detail.transact_header_id ";
        $sql .= "             and transact_confirmations.purchase_user_group_id = detail.purchase_user_group_id ";

        $sql .= "     where ";
        $sql .= "         transact_confirmations.id = ".$id;
        $sql .= "         and transact_confirmations.deleted_at is null ";
        $sql .= " ) as a ";
        $sql .= " where ";
        $sql .= "     a.show_row=1 ";

        $count_sql = " select count(*) from (".$sql.") as tmp ";
        $all_count = \DB::select($count_sql);

        $count2=$all_count[0]->count;

        $sql .= " order by ";
        $sql .= "     id ";

        $all_rec = \DB::select($sql);

        return $all_rec[0];

    }
}
