<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class Role extends Model
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
    public function getRoleId()
    {
		return \DB::select("select nextval('roles_id_seq') as nextid")[0]->nextid;
    }

    /**
     * 権限情報取得
     * 
     * @param string $key_code ロールキー
     * @param int $count ページネーション用カウント
     *
     */
    public function getPrivilegeList(Request $request, string $key_code, int $count)
    {
    	$count_sql = "select count(*) from v_user_privilege_detail_audit_list where role_key_code = '{$key_code}'";
		$all_count = \DB::select($count_sql);
		$count2=$all_count[0]->count;

        $sql = "select * from v_user_privilege_detail_audit_list where role_key_code = '{$key_code}'";
		if ($count == -1) {
			$per_page = isset($request->page_count) ? $request->page_count : 1;
		} else {
			if (!isset($request->page_count)){
				$offset=0;
			} else {
				if ($request->page == 0) {
					$offset=0;
				} else {
					$offset=($request->page - 1) * $request->page_count;
				}
			}
			$per_page = isset($request->page_count) ? $request->page_count : $count;
            $sql .=" limit ".$per_page." offset ".$offset;
		}
		$all_rec = \DB::select($sql);
		$result = Role::query()->hydrate($all_rec);
		// ページ番号が指定されていなかったら１ページ目
		$page_num = isset($request->page) ? $request->page : 1;
		// ページ番号に従い、表示するレコードを切り出す
		$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

		// ページャーオブジェクトを生成
		$pager= new LengthAwarePaginator(
                    $result, // ページ番号で指定された表示するレコード配列
                    $count2, // 検索結果の全レコード総数
                    $per_page, // 1ページ当りの表示数
                    $page_num, // 表示するページ
                    ['path' => $request->url()] // ページャーのリンク先のURLを指定
                );
		return $pager;
    }
}
