<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Observers\AuthorObserver;

class TransactDetailError extends Model
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

	/*
	 * 明細取込みエラー一覧
	 */
	public function getList(Request $request)
	{
		$query = $this->select(\DB::raw(
				"row_number || '行目' as row_number
				,jan_code
				,error_info"
				)
		);
		// 検索条件
		if (!empty($request->id)) {
			$query->where('transact_file_storage_id', $request->id);
		}
		return $query->orderBy('id', 'asc')->get();
	}
}
