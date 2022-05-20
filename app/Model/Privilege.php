<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class Privilege extends Model
{
    use SoftDeletes;

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
     */
    public function getPrivilegeId()
    {
		return \DB::select("select nextval('privileges_id_seq') as nextid")[0]->nextid;
	}
}
