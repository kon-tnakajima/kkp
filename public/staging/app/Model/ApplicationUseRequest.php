<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class ApplicationUseRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_group_id',
        'user_group_name',
        'name',
        'email',
        'status',
        'remarks',
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
