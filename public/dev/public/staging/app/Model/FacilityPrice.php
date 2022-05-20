<?php

namespace App\Model;
use App\Model\Facility;
use App\Model\Concerns\Facility as FacilityTrait;
use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;

class FacilityPrice extends Model
{
    use SoftDeletes;
    use FacilityTrait;

    /*
     * boot
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    protected $guraded = ['id'];
    protected $fillable = [
        'facility_medicine_id',
        'trader_id', 
        'purchase_price', 
        'sales_price',
        'start_date', 
        'end_date', 
        'deleter', 
        'creater', 
        'updater'
        ];
    
    /*
     * 施設と薬品でデータ取得
     */
    public function getData($facility_medicine_id)
    {
        return $this->where('facility_medicine_id', $facility_medicine_id)->first();
    }
 
}
