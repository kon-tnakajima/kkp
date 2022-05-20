<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\TransactFileStorage;
use App\Model\TransactDetail;
use App\Observers\AuthorObserver;

class ClaimHistoryComment extends Model
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
    
    /*
     * データリスト取得
     */
    public function getDataList($transact_confirmation_id)
    {
        $query = $this->select('claim_history_comments.id' 
        ,'users.name as name'
        ,'claim_history_comments.described_user_id'
        ,'claim_history_comments.comment'
        ,'claim_history_comments.read_flg'
        ,'claim_history_comments.created_at'
        )
        ->leftJoin('users', 'users.id', '=', 'claim_history_comments.described_user_id')
        ->where('claim_history_comments.transact_confirmation_id',$transact_confirmation_id);

        return $query->orderBy('id', 'asc')->get();
    }

	//メールキューID(操作者が送信するメール群ID)を取得
    public function getCommentID()
    {
		return \DB::select("select nextval('trading_history_id_seq') as nextid")[0]->nextid;
	}
    
    /*
     * データリスト取得
     */
    public function updateReadFlg($user_id, $transact_confirmation_id)
    {
        return $this->where('claim_history_comments.transact_confirmation_id', $transact_confirmation_id)->where('claim_history_comments.described_user_id', '<>', $user_id)->update(['read_flg' => 1]);
    }
}
