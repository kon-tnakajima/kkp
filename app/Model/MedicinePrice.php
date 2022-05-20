<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;
use DateTime;

class MedicinePrice extends Model
{
    use SoftDeletes;
    protected $guraded = ['id'];
    protected $fillable = [
        'medicine_id',
        'start_date',
        'end_date',
        'price',
        'deleter',
        'creater',
        'updater'
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

    /*
     * 薬品で薬価を取得
     */
    public function getData($medicine_id)
    {
    	$date = new DateTime();
    	$targetDate = $date->modify('-3 year first day of this months')->format('Y-m-d');
    	return $this->where('medicine_id', $medicine_id)->where('start_date', '>=', $targetDate )->orderBy('start_date', 'desc')->take(3)->get();
    }
}
