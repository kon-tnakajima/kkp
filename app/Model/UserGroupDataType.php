<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGroupDataType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_group_id', 
        'data_type_name', 
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
