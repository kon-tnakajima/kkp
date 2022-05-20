<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;
use Carbon\Carbon;

class Agreement extends Model
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
    public function getAgreementId()
    {
		return \DB::select("select nextval('agreements_id_seq') as nextid")[0]->nextid;
    }
    
    /**
     * 規約取得
     */
    public function getAgreement()
    {
        $now = Carbon::now();
        $result = $this->select('body')->where('from_date', '<=', $now)->where('to_date', '>', $now)->first();
        return $result->body;
    }
}
