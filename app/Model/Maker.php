<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class Maker extends Model
{
    use SoftDeletes;
    protected $guraded = ['id'];

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
