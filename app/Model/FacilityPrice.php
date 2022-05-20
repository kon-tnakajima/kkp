<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use DateTime;

class FacilityPrice extends Model
{
    use SoftDeletes;

    protected $guraded = ['id'];
    protected $fillable = [
        'facility_medicine_id',
        'purchase_price',
        'sales_price',
        'start_date',
        'end_date',
        'deleter',
        'creater',
        'updater',
    	'purchase_user_group_id'
        ];

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    // facility_prices.facility_medicine_idは今後使用しない、medicine_idとsales_user_group_idに置き換える
    // /*
    //  * 施設と薬品でデータ取得
    //  */
    // public function getData($facility_medicine_id)
    // {
    //     return $this->where('facility_medicine_id', $facility_medicine_id)->where('end_date', '>=', date('Y-m-d H:i:s'))->where('start_date', '<=', date('Y-m-d H:i:s'))->first();
    // }

    // /*
    //  * 施設と業者でデータ取得
    //  */
    // public function getDataByTrader($facility_medicine_id,$trader)
    // {
    // 	return $this->where('facility_medicine_id', $facility_medicine_id)->where('purchase_user_group_id', $trader)->where('end_date', '>=', date('Y-m-d H:i:s'))->where('start_date', '<=', date('Y-m-d H:i:s'))->first();
    // }

    // /*
    //  * 最新データを取得
    //  */
    // public function getLastData($facility_medicine_id)
    // {
    // 	return $this->where('facility_medicine_id', $facility_medicine_id)->orderBy('end_date', 'desc')->first();
    // }
    

    /*
     * 論理キー(施設・業者・薬品・単価開始日)でデータ取得
     */
    public function getDataByKey($facility_id,$trader_id,$medicine_id,$start_date)
    {
    	return $this->where('sales_user_group_id', $facility_id)->where('purchase_user_group_id', $trader_id)->where('medicine_id', $medicine_id)->where('start_date', $start_date)->first();
    }

    /*
     * 納入価履歴を取得
     */
    public function getDataList($medicine_id,$sales_user_group_id,$user)
    {
    	// 日付条件を作成
    	$date = new DateTime();
    	$targetDate = $date->modify('-3 year first day of this months')->format('Y-m-d H:i:s');

        $sql  = " select ";
        $sql .= "     fp.purchase_price ";
        $sql .= "     , fp.sales_price ";
        $sql .= "     , fp.start_date ";
        $sql .= "     , fp.end_date ";
        $sql .= "     , fp.purchase_user_group_id ";
        $sql .= "     , ug.name as trader_name ";
        $sql .= " from ";
        $sql .= " facility_prices fp ";
        $sql .= " inner join f_show_records(".$user->id.") as fsr ";
        $sql .= "     on fp.sales_user_group_id = fsr.user_group_id ";
        $sql .= "     and fp.purchase_user_group_id = fsr.trader_user_group_id ";
        $sql .= " left join user_groups ug ";
        $sql .= "     on fp.purchase_user_group_id = ug.id ";
        $sql .= " where ";
        $sql .= "   fp.medicine_id =" . $medicine_id;
        $sql .= "   and fp.sales_user_group_id =" . $sales_user_group_id;
        $sql .= "   and fp.start_date >= '" . $targetDate . "' ";
        $sql .= "   and fp.deleted_at is null ";


        $sql .= " order by ";
        $sql .= "     fp.start_date desc";
        $sql .= "     , fp.purchase_user_group_id asc";


    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = array();
    	}

    	return $all_rec;

    }
}
