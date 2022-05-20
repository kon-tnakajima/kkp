<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class OptionalMedicineKey extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'value',
        'disp_order',
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

    /**
     * DBより最大表示順値を取得
     * @param int $no テーブル番号
     * @param int $user_group_id ユーザグループID
     * @return int 最大表示順値
     */
    public function getSortNo(int $no, int $user_group_id)
    {
        $result = \DB::table("optional_medicine_key{$no}")
            ->select(\DB::raw('max(disp_order) as disp_order'))
            ->whereNull('deleted_at')
            ->where('user_group_id', $user_group_id)
            ->groupBy('user_group_id')
            ->first();
        if (empty($result)) {
            return 0;
        }
        return $result->disp_order;
	}
}
