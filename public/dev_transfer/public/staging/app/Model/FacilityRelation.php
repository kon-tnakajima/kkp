<?php

namespace App\Model;
use App\Model\Facility;
use App\Model\Concerns\Facility as FacilityTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;

class FacilityRelation extends Model
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

    /*
     * メーカーの取得
     */
    public function maker()
    {
        return $this->belongsTo('App\Model\Maker');
    }

    protected $guraded = ['id'];
    protected $fillable = [
        'facility_id',
        'parent_facility_id', 
        'deleter', 
        'creater', 
        'updater'
        ];
 
}
