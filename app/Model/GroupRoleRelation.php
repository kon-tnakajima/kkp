<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupRoleRelation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_group_id',
        'role_key_code',
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
     * ユーザグループIDの登録されているロール情報取得
     *
     * @param int $user_group_id ユーザグループID
     * @return ロール一覧情報
     */
    public function getRoles(int $user_group_id)
    {
        return $this->select('role_key_code')->where('user_group_id', $user_group_id)->get();
    }
}
