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

        $m_id                       = array_key_exists('m_id',$reqParam) ? $reqParam["m_id"] ?? null : null ;
        $fm_id                      = array_key_exists('fm_id',$reqParam) ? $reqParam["fm_id"] ?? null : null ;
        $fp_id                      = array_key_exists('fp_id',$reqParam) ? $reqParam["fp_id"] ?? null : null ;
        $pa_id                      = array_key_exists('pa_id',$reqParam) ? $reqParam["pa_id"] ?? null : null ;
        $fp_honbu_id                = array_key_exists('fp_honbu_id',$reqParam) ? $reqParam["fp_honbu_id"] ?? null : null ;
        $task_type                  = array_key_exists('task_type',$reqParam) ? $reqParam["task_type"] ?? null : null ;
        $status_forward             = array_key_exists('status_forward',$reqParam) ? $reqParam["status_forward"] ?? null : null ;
        $status_reject              = array_key_exists('status_reject',$reqParam) ? $reqParam["status_reject"] ?? null : null ;
        $status_backward            = array_key_exists('status_backward',$reqParam) ? $reqParam["status_backward"] ?? null : null ;
        $pa_updated_at              = array_key_exists('pa_updated_at',$reqParam) ? $reqParam["pa_updated_at"] ?? null : null ;
        $pa_updated_at              = $pa_updated_at === null ? null : "'".$pa_updated_at."'" ;

        $purchase_requested_price   = array_key_exists('purchase_requested_price',$reqParam) ? $reqParam["purchase_requested_price"] ?? null : null ;
        $purchase_estimated_price   = array_key_exists('purchase_estimated_price',$reqParam) ? $reqParam["purchase_estimated_price"] ?? null : null ;
        $purchase_price             = array_key_exists('purchase_price',$reqParam) ? $reqParam["purchase_price"] ?? null : null ;
        $sales_price                = array_key_exists('sales_price',$reqParam) ? $reqParam["sales_price"] ?? null : null ;

        $apply_group                = array_key_exists('apply_group',$reqParam) ? $reqParam["apply_group"] ?? null : null ;
        $purchase_user_group_id     = array_key_exists('purchase_user_group_id',$reqParam) ? $reqParam["purchase_user_group_id"] ?? null : null ;
        $sales_estimate_comment     = array_key_exists('sales_estimate_comment',$reqParam) ? $reqParam["sales_estimate_comment"] ?? null : null ;
        $purchase_estimate_comment  = array_key_exists('purchase_estimate_comment',$reqParam) ? $reqParam["purchase_estimate_comment"] ?? null : null ;
        $comment                    = array_key_exists('comment',$reqParam) ? $reqParam["comment"] ?? null : null ;
        $fm_comment                 = array_key_exists('fm_comment',$reqParam) ? $reqParam["fm_comment"] ?? null : null ;
        $priority                   = array_key_exists('priority',$reqParam) ? $reqParam["priority"] ?? null : null ;
        $start_date                 = array_key_exists('start_date',$reqParam) ? $reqParam["start_date"] ?? null : null ;
        $basis_mediicine_price_date = array_key_exists('basis_mediicine_price_date',$reqParam) ? $reqParam["basis_mediicine_price_date"] ?? null : null ;

        $sql   = " select ";   
        $sql  .= " 	 m_id ";
        $sql  .= " 	, pa_id ";
        $sql  .= " 	, fm_id ";
        $sql  .= " 	, fp_id ";
        $sql  .= " 	, fp_honbu_id ";

        $sql  .= " 	, status ";
        $sql  .= " 	, status_name ";
        $sql  .= " 	, status_color_code ";
        $sql  .= " 	, record_background_color_code ";
        $sql  .= " 	, err_comment ";

        $sql  .= " 	, sales_user_group_id ";
        $sql  .= " 	, purchase_user_group_id ";
        $sql  .= " 	, start_date ";

        $sql  .= " 	, purchase_requested_price ";
        $sql  .= " 	, purchase_estimated_price ";
        $sql  .= " 	, purchase_price ";
        $sql  .= " 	, sales_price ";

        $sql  .= " 	, edit_pa_purchase_requested_price ";
        $sql  .= " 	, edit_pa_purchase_estimated_price ";
        $sql  .= " 	, edit_pa_purchase_price ";
        $sql  .= " 	, edit_pa_sales_price ";

        $sql  .= " 	, forward_task_button_color_code ";
        $sql  .= " 	, reject_task_button_color_code ";
        $sql  .= " 	, backward_task_button_color_code ";
        $sql  .= " 	, forward_task_button_text ";
        $sql  .= " 	, reject_task_button_text ";
        $sql  .= " 	, backward_task_button_text ";
        $sql  .= " 	, forward_task_button_task_type ";
        $sql  .= " 	, reject_task_button_task_type ";
        $sql  .= " 	, backward_task_button_task_type ";
        $sql  .= " 	, facility_name ";

        if ($task_type == 0){ //申請への正常処理であれば
            $sql  .= " from f_price_adoptions_update_forward (".$user->id.",".$m_id.",".$fm_id.",".$fp_id.",".$pa_id.",".$fp_honbu_id.",".$task_type.",".$status_forward .",".($pa_updated_at ?? 'null').",".($purchase_requested_price ?? 'null').",".($purchase_estimated_price ?? 'null').",".($purchase_price ?? 'null').",".($sales_price ?? 'null').",".($apply_group ?? 'null').",".($purchase_user_group_id ?? 'null').",".($sales_estimate_comment ?? 'null').",".($purchase_estimate_comment ?? 'null').",".($comment ?? 'null').",".($fm_comment ?? 'null').",".($priority ?? 'null').",".($start_date ?? 'null').",".($basis_mediicine_price_date ?? 'null').")";
        } elseif (in_array($task_type ,[1,2,3])){ //申請の取り下げ、差し戻しであれば
            $sql  .= " from f_price_adoptions_update_backward(".$user->id.",".$m_id.",".$fm_id.",".$fp_id.",".$pa_id.",".$fp_honbu_id.",".$task_type.",".($task_type == 3 ? $status_reject : $status_backward).",".($pa_updated_at ?? 'null').",".($purchase_requested_price ?? 'null').",".($purchase_estimated_price ?? 'null').",".($purchase_price ?? 'null').",".($sales_price ?? 'null').",".($apply_group ?? 'null').",".($purchase_user_group_id ?? 'null').",".($sales_estimate_comment ?? 'null').",".($purchase_estimate_comment ?? 'null').",".($comment ?? 'null').",".($fm_comment ?? 'null').",".($priority ?? 'null').",".($start_date ?? 'null').",".($basis_mediicine_price_date ?? 'null').")";
        }
        
        logger($sql);
        $rec = \DB::select($sql);

    	if (empty($rec)) {
    		$rec = array();
    	}

    	return $rec;

    }

    /*
     * 連想配列に対して、キーの存在をチェックし、値を取得する
     */
    public function getValueFromArray($array,$key)
    {
        return array_key_exists($key,$array) ? isset($array[$key]) ?? null : null ;
    }
}
