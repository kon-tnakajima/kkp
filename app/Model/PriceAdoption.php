<?php

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Model\Concerns\Calc as CalcTrait;
use App\Observers\AuthorObserver;
use App\Model\UserGroup;
use App\Model\Concerns\UserGroup as UserGroupTrait;

class PriceAdoption extends Model
{
    use SoftDeletes;
    use CalcTrait;
    use UserGroupTrait;
    protected $fillable = [
        'facility_id',
        'medicine_id',
        'user_id',
        'application_date',
        'status',
        'approval_user_id',
        'purchase_price',
        'sales_price',
        'sales_user_group_id',
        'search',
    	'priority',
    	'revert_type',
    	'basis_mediicine_price',
    	'basis_mediicine_price_date',
    	'purchase_requested_price',
    	'purchase_estimated_price',
    	'purchase_estimate_updater',
    	'purchase_estimate_updated_at',
    	'purchase_estimate_comment',
    	'sales_estimate_comment',
        'comment',
        'deleter',
        'creater',
        'updater'
        ];

    /*
     * boot
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }
    /*
     * 標準薬品を取得
     */
    public function medicine()
    {
        return $this->belongsTo('App\Model\Medicine');
    }

    /*
     * 標準薬品を取得
     */
    public function maker()
    {
        return $this->belongsTo('App\Model\Maker');
    }

    /*
     * ユーザを取得
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /*
     * グループを取得
     */
    public function userGroup()
    {
    	return $this->belongsTo('App\Model\UserGroup', 'sales_user_group_id');
    }


    /*
     * 施設と薬品でデータ取得
     */
    public function getData(int $user_group_id, int $medicine_id)
    {
        return $this->where('sales_user_group_id', $user_group_id)->where('medicine_id', $medicine_id)->first();
    }


    /*
     * 施設と薬品でデータ取得
     */
    public function getActiveData(int $user_group_id, int $medicine_id)
    {
        return $this->where('sales_user_group_id', $user_group_id)->where('medicine_id', $medicine_id)->whereNotIn('status', [10,13,23,33,43,53,63,73,83,93,100,110,120,130])->get();
    }

    /*
     * ステータスが未申請かどうか
     */
    public function isUnapply()
    {
        if ($this->status == Task::STATUS_UNAPPLIED) {
            return true;
        }
        return false;
    }

    /*
     * ステータスが採用済かどうか
     */
    public function isDone()
    {
    	if ($this->status == Task::STATUS_DONE) {
    		return true;
    	}
    	return false;
    }

    /*
     * 登録された薬品かどうか
     */
    public function isRegisted()
    {
        return is_null($this->medicine_id);
    }

    /*
     * Jancode重複チェック
     */
    public function chackJanCode($jan_code)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "      * ";
    	$sql .= " from ";
    	$sql .= "     price_adoptions as pa ";
    	$sql .= " inner join pack_units as pu ";
    	$sql .= "        on pa.medicine_id = pu.medicine_id ";
    	$sql .= " where ";
    	$sql .= "    pa.deleted_at is null ";
    	$sql .= "    and pu.jan_code = '" . $jan_code . "'";

    	$all_rec = \DB::select($sql);

    	return $all_rec;

    }

    /*
     * 申請履歴をlogsも含めて取得
     */
    public function getDataList($medicine_id,$sales_user_group_id,$user)
    {
        $sql   = " select ";
        $sql  .= " 	application_date ";
        $sql  .= " 	,status_name ";
        $sql  .= " 	,apply_user_name ";
        $sql  .= " 	,approval_user_name ";
        $sql  .= " 	,trader_name ";
        $sql  .= " 	,basis_mediicine_price ";
        $sql  .= " 	,basis_mediicine_price_date ";
        $sql  .= " 	,purchase_estimated_price ";
        $sql  .= " 	,purchase_price ";
        $sql  .= " 	,sales_price ";
        $sql  .= " 	,start_date ";
        $sql  .= " 	,end_date ";
        $sql  .= " from f_show_price_adoptions_history(".$user->id."," . $medicine_id."," . $sales_user_group_id.") ";

    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = array();
    	}

    	return $all_rec;

    }


    /*
     * ajaxで更新後の値をreturn
     */
    public function ajaxStatusChangeBunkaren($request, $user)
    {
        $reqParam = $request->all();
        $m_id           = $reqParam["m_id"];
        $fm_id          = $reqParam["fm_id"];
        $fp_id          = $reqParam["fp_id"];
        $pa_id          = $reqParam["pa_id"];
        $fp_honbu_id    = $reqParam["fp_honbu_id"];
        $task_type      = $reqParam["task_type"];
        $status_forward = $reqParam["status_forward"];
        $status_reject  = $reqParam["status_reject"];
        $pa_updated_at  = $reqParam["pa_updated_at"];

        $sql   = " select ";   
        $sql  .= " 	 pa_id ";
        $sql  .= " 	, status ";
        $sql  .= " 	, status_name ";
        $sql  .= " 	, sales_user_group_id ";
        $sql  .= " 	, purchase_user_group_id ";
        $sql  .= " 	, status_color_code ";
        $sql  .= " 	, record_background_color_code ";
        $sql  .= " 	, task_button_is_enabled ";
        $sql  .= " 	, forward_button_is_enabled ";
        $sql  .= " 	, reject_button_is_enabled ";
        $sql  .= " 	, err_comment ";
        $sql  .= " from f_price_adoptions_update_ajax(".$user->id.",".$m_id.",".$fm_id.",".$fp_id.",".$pa_id.",".$fp_honbu_id.",".$task_type.",".$status_forward.",".$status_reject.",'".$pa_updated_at."')";

        $rec = \DB::select($sql);

    	if (empty($rec)) {
    		$rec = array();
    	}

    	return $rec;

    }
}
