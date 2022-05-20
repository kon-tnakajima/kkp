<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class OtherStorage extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /**
     * シーケンスIDを取得
     * @return int id
     */
    public function getOtherSequence()
    {
    	return \DB::select("select nextval('other_storages_id_seq') as id")[0]->id;
    }
}
