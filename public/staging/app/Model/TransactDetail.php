<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\TransactFileStorage;
use App\Model\ClaimHistoryComment;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;

class TransactDetail extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    //売仕区分
    const TAX_DIVISION_POPULAR = 1;
    const TAX_DIVISION_EXEMPTION = 2;
    const TAX_DIVISION_CASHBACK = 3;
    const TAX_DIVISION_4 = 4;
    const TAX_DIVISION_5 = 4;
    const TAX_DIVISION_6 = 6;

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * 明細一覧
     */
    public function getListClaimDetail(Request $request, $user, $count)
    {

    	$sql  = $this->getListClaimDetailSql($request, $user, $count);

    	$count_sql = " select count(*) from (".$sql.") as tmp ";
		$all_count = \DB::select($count_sql);

		$count2=$all_count[0]->count;

		$sql .=" order by ";
		$sql .=" id asc ";

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
		$result = TransactDetail::query()->hydrate($all_rec);
		// ページ番号が指定されていなかったら１ページ目
		$page_num = isset($request->page) ? $request->page : 1;
		// ページ番号に従い、表示するレコードを切り出す
		$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

		// ページャーオブジェクトを生成 TODO ここがviewと合わせないとこけるかも
		$pager= new \Illuminate\Pagination\LengthAwarePaginator(
				$result, // ページ番号で指定された表示するレコード配列
				$count2, // 検索結果の全レコード総数
				$per_page, // 1ページ当りの表示数
				$page_num, // 表示するページ
				['path' => $request->url()] // ページャーのリンク先のURLを指定
				);

		logger('PAGERRRR');
		logger($count2);
		logger($per_page);
		logger($page_num);
		logger($disp_rec);
		//exit;

		return $pager;

    }

    /*
     * 明細一覧
     */
    public function getListClaimDetailSql(Request $request, $user, $count)
    {

       $sql ="    select ";
       $sql .="        td.created_at ";
       $sql .="        , td.id ";
       $sql .="        , td.claim_date ";
       $sql .="        , td.department_name ";
       $sql .="        , td.gtin_code ";
       $sql .="        , td.jan_code ";
       $sql .="        , td.maker_name ";
       $sql .="        , td.item_name ";
       $sql .="        , td.standard ";
       $sql .="        , td.item_code ";
       $sql .="        , td.unit_name ";
       $sql .="        , td.quantity ";
       $sql .="        , td.is_stock_or_sale ";
       $sql .="        , td.tax_division ";
       $sql .="        , td.tax_rate ";
       $sql .="        , f_show_sales_price_claim(td.sales_unit_price,p.show_sales_price) as sales_unit_price ";
       $sql .="        , f_show_sales_price_claim(td.sales_price,p.show_sales_price) as sales_price ";
       $sql .="        , td.sales_comment ";
       $sql .="        , sales_slip_number ";
       $sql .="        , td.gs1_sscc ";
       $sql .="        , f_show_purchase_price(td.buy_unit_price,p.show_purchase_price) as buy_unit_price ";
       $sql .="        , f_show_purchase_price(td.buy_price,p.show_purchase_price) as buy_price ";
       $sql .="        , td.buy_comment ";
       $sql .="        , td.buy_slip_number ";
       $sql .="        , td.bunkaren_trading_history_code ";
       $sql .="        , td.bunkaren_billing_code ";
       $sql .="        , td.bunkaren_payment_code ";
       $sql .="        , td.bunkaren_item_code ";
       $sql .="        , td.facility_item_code ";
       $sql .="        , td.trader_item_code ";
       $sql .="        , td.transact_header_id ";
       $sql .="        , td.refund_price  ";
       // 施設、業者、供給区分 追加
       $sql .="        , trader.name as trader_name  ";
       $sql .="        , facility.name as facility_name  ";
       $sql .="        , ugsd.supply_division_name as supply_division_name";
       //$sql .="        , p.show_sales_price_waku ";
       //$sql .="        , p.show_purchase_price_waku ";
       // 権限用に追加
       $sql .="        , p.show_sales_price ";
       $sql .="        , p.show_purchase_price ";
       $sql .="    from ";
       $sql .="        transact_details td ";
       // 20201127 ビューから関数に変更
       //$sql .="       inner join v_privileges_row_claim_check p ";
       $sql .="         inner join f_privileges_row_claim_check(". $user->id .") p ";
       $sql .="            on td.sales_user_group_id = p.sales_user_group_id  ";
       $sql .="            and td.purchase_user_group_id = p.purchase_user_group_id  ";
       $sql .="            and p.show_claim_check_rows = 1  ";
       //$sql .="           and p.user_id = ". $user->id;
       // 施設、業者、供給区分 追加
       $sql .="       left join user_groups as trader ";
       $sql .="        on trader.id = td.purchase_user_group_id ";
       $sql .="       left join user_groups as facility ";
       $sql .="        on facility.id = td.sales_user_group_id ";
       $sql .="       left join user_group_supply_divisions ugsd  ";
       $sql .="        on td.supply_division = ugsd.id ";
       $sql .="    where ";
       $sql .="    td.transact_confirmation_id = " . $request->id;
       $sql .="    and f_show_claim_detail_row( ";
       $sql .="    td.is_stock_or_sale, ";
       $sql .="    p.show_sales_price, ";
       $sql .="    p.show_purchase_price) = 1 ";
       $sql .="    and td.deleted_at is null ";

        return $sql;
    }


    /*
     * 明細一覧
     */
    public function getListClaimDetail_old2(Request $request, $count)
    {
        $query = $this->select('transact_details.created_at'
        ,'transact_details.id'
        ,'transact_details.claim_date'
        ,'transact_details.department_name'
        ,'transact_details.gtin_code'
        ,'transact_details.jan_code'
        ,'transact_details.maker_name'
        ,'transact_details.item_name'
        ,'transact_details.standard'
        ,'transact_details.item_code'
        ,'transact_details.unit_name'
        ,'transact_details.quantity'
        ,'transact_details.is_stock_or_sale'
        ,'transact_details.tax_division'
        ,'transact_details.tax_rate'
        ,'transact_details.sales_unit_price'
        ,'transact_details.sales_price'
        ,'transact_details.sales_comment'
        ,'transact_details.sales_slip_number'
        ,'transact_details.gs1_sscc'
        ,'transact_details.buy_unit_price'
        ,'transact_details.buy_price'
        ,'transact_details.buy_comment'
        ,'transact_details.buy_slip_number'
        ,'transact_details.bunkaren_trading_history_code'
        ,'transact_details.bunkaren_billing_code'
        ,'transact_details.bunkaren_payment_code'
        ,'claim_invoices.invoice_name'
        ,'claim_payments.payee_name'
        ,'transact_details.bunkaren_item_code'
        ,'transact_details.facility_item_code'
        ,'transact_details.trader_item_code'
        ,'transact_details.transact_header_id'
        ,'transact_details.refund_price'
        )
        ->leftJoin('transact_confirmations', 'transact_details.transact_confirmation_id', '=', 'transact_confirmations.id')
        ->leftJoin('claim_invoices', 'claim_invoices.id', '=', 'transact_confirmations.claim_invoice_id')
        ->leftJoin('claim_payments', 'claim_payments.id', '=', 'transact_confirmations.claim_payment_id')
        ->where('transact_details.transact_confirmation_id',$request->id);

        //logger('MEISAIQUERYYYYY');
        //logger($query->toSql());

        return $query->orderBy('id', 'asc')->paginate($count);
    }



    /*
     * 明細一覧
     */
    public function getListClaimDetailSql_old(Request $request, $count)
    {
       $sql =" select ";
       $sql .=" transact_details.created_at ";
       $sql .=" , transact_details.id ";
       $sql .="  , transact_details.claim_date ";
       $sql .="  , transact_details.department_name ";
       $sql .=" , transact_details.gtin_code ";
       $sql .=" , transact_details.jan_code ";
       $sql .=" , transact_details.maker_name ";
       $sql .=" , transact_details.item_name ";
       $sql .=" , transact_details.standard ";
       $sql .=" , transact_details.item_code ";
       $sql .=" , transact_details.unit_name ";
       $sql .=" , transact_details.quantity ";
       $sql .=" , transact_details.is_stock_or_sale ";
       $sql .=" , transact_details.tax_division ";
       $sql .=" , transact_details.tax_rate ";
       $sql .=" , transact_details.sales_unit_price ";
       $sql .=" , transact_details.sales_price ";
       $sql .=" , transact_details.sales_comment ";
       $sql .=" , transact_details.sales_slip_number ";
       $sql .=" , transact_details.gs1_sscc ";
       $sql .=" , transact_details.buy_unit_price ";
       $sql .=" , transact_details.buy_price ";
       $sql .=" , transact_details.buy_comment ";
       $sql .=" , transact_details.buy_slip_number ";
       $sql .=" , transact_details.bunkaren_trading_history_code ";
       $sql .=" , transact_details.bunkaren_billing_code ";
       $sql .=" , transact_details.bunkaren_payment_code ";
       $sql .=" , claim_invoices.invoice_name ";
       $sql .=" , claim_payments.payee_name ";
       $sql .=" , transact_details.bunkaren_item_code ";
       $sql .=" , transact_details.facility_item_code ";
       $sql .=" , transact_details.trader_item_code ";
       $sql .=" , transact_details.transact_header_id ";
       $sql .=" , transact_details.refund_price ";
       $sql .=" from ";
       $sql .=" transact_details ";
       $sql .=" left join transact_confirmations ";
       $sql .="   on transact_details.transact_confirmation_id = transact_confirmations.id ";
       $sql .=" left join claim_invoices ";
       $sql .="   on claim_invoices.id = transact_confirmations.claim_invoice_id ";
       $sql .=" left join claim_payments ";
       $sql .="   on claim_payments.id = transact_confirmations.claim_payment_id ";
       $sql .=" where ";
       $sql .=" transact_details.transact_confirmation_id = " . $request->id ;
       $sql .=" and transact_details.deleted_at is null ";

        return $sql;
    }


    /*
     * 請求照会のCSV出力用
     */
    public function getListCsv($request, $user)
    {
        $sql  = $this->getListClaimDetailSql($request, $user, -1);

        //$count_sql = " select count(*) from (".$sql.") as tmp ";
    	//$all_count = \DB::select($count_sql);

    	//$count2=$all_count[0]->count;

    	$sql .=" order by ";
    	$sql .="     id ";

    	$all_rec = \DB::select($sql);

    	return $all_rec;
    }





    /*
     * 請求支払確認参照IDで取得
     */
    public function getList($transact_confirmation_id)
    {
        return $this->where('transact_confirmation_id', $transact_confirmation_id)->get();
    }

    /*
     * 請求支払ヘッダIDで取得
     */
    public function getListByHeaderId($transact_header_id)
    {
        return $this->where('transact_header_id', $transact_header_id)->get();
    }
}
