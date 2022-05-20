<?php

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Model\Concerns\Facility as FacilityTrait;
use App\Model\Concerns\Calc as CalcTrait;
use App\Observers\AuthorObserver;

class PriceAdoption extends Model
{
    use SoftDeletes;
    use FacilityTrait;
    use CalcTrait;
    protected $fillable = [
        'facility_id',
        'medicine_id', 
        'user_id', 
        'application_date', 
        'status', 
        'approval_user_id', 
        'purchase_price', 
        'sales_price', 
        'search',
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
     * 施設を取得
     */
    public function facility()
    {
        return $this->belongsTo('App\Model\Facility');
    }

    /*
     * 施設を取得
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
    /*
     * 施設と薬品でデータ取得
     */
    public function getData($facility_id, $medicine_id)
    {
        return $this->where('facility_id', $facility_id)->where('medicine_id', $medicine_id)->first();
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
     * 登録された薬品かどうか
     */
    public function isRegisted()
    {
        return (is_null($this->medicine_id)) ? true : false;
    }

}
