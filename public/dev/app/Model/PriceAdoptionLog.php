<?php

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Model\Concerns\Calc as CalcTrait;
use App\Observers\AuthorObserver;
use App\Model\UserGroup;
use App\Model\Concerns\UserGroup as UserGroupTrait;

class PriceAdoptionLog extends Model
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

}
