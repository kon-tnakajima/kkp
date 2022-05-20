<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGroupRelation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 
        'user_group_id', 
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

    /** 
     * 権限キー取得
     * @param int $user_id ユーザID
     * @param int $user_group_id ユーザグループID
     * @return string ロールキー情報
     */
    public function getRoleKey(int $user_id, int $user_group_id)
    {
        return $this->select('role_key_code')->where('user_id', $user_id)->where('user_group_id', $user_group_id)->first()->role_key_code;
    }
}
