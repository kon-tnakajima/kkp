<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGroupSupplyDivision extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_group_id', 
        'supply_division_name', 
        'disp_order', 
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
}
