<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\User;
use App\Model\UserGroupRelation;
use App\Model\UserGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\BaseService;

class UserService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->user = new User();
        $this->userGroup = new UserGroup();
        $this->userGroupRelation = new UserGroupRelation();
    }

    /*
     * 標準薬品情報取得
     */
    public function getMedicine($id)
    {
        return $this->medicine->find($id);
    }

    public function getUser($id)
    {
    	return $this->user->withTrashed()->find($id);
    }

    /*
     * 一覧画面
     */
    public function getUserList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        return $this->user->getUserList($request, $count, Auth::user());
    }

    /**
     * グループに所属するユーザ一覧
     */
    public function getGroupUsersList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;

        //文化連とその他で一覧表示を分ける
        if (isBunkaren()) {
        	$id = $request->user_group_id;
/*
        	$sql  = "";
        	$sql .= " select ";
        	$sql .= "      base.id";
        	$sql .= "     ,base.name";
        	$sql .= "     ,base.email";
        	$sql .= "     ,base.sub_id";
        	$sql .= "     ,base.updated_at";
        	$sql .= "     ,group.role_key_code";
        	$sql .= " from ( ";
        	$sql .= "     select  ";
        	$sql .= "          users.id ";
        	$sql .= "         ,users.name ";
        	$sql .= "         ,users.email ";
        	$sql .= "         ,users.sub_id ";
        	$sql .= "         ,users.updated_at ";
        	$sql .= "     from  ";
        	$sql .= "         users  ";
        	$sql .= "         inner join user_group_relations  ";
        	$sql .= "             on user_group_relations.user_id= users.id ";
        	$sql .= "         inner join user_groups  ";
        	$sql .= "             on user_group_relations.user_group_id = user_groups.id";
        	$sql .= "             and user_groups.group_type = '文化連'";
        	$sql .= "     where  ";
        	$sql .= "          user_group_relations.deleted_at  ";
        	$sql .= " ) as base ";
        	$sql .= " left join (";
        	$sql .= "     select  ";
        	$sql .= "          users.id ";
        	$sql .= "         ,user_group_relations.role_key_code ";
        	$sql .= "     from  ";
        	$sql .= "         users  ";
        	$sql .= "         inner join user_group_relations  ";
        	$sql .= "             on user_group_relations.user_id= users.id ";
        	$sql .= "         inner join user_groups  ";
        	$sql .= "             on user_group_relations.user_group_id = user_groups.id";
        	$sql .= "             and user_group_relations.user_group_id = ".$id;
        	$sql .= "     where  ";
        	$sql .= "          user_group_relations.deleted_at  ";
        	$sql .= " ) as group";
        	$sql .= "     on base.id=group.id ";


        	$all_rec = \DB::select($sql);
        	$count2=$all_count[0]->count;

        	$sql .= " order by id";

        	$result = User::query()->hydrate($all_rec);

        	// ページ番号が指定されていなかったら１ページ目
        	$page_num = isset($request->page) ? $request->page : 1;
        	// ページ番号に従い、表示するレコードを切り出す
        	$disp_rec = array_slice($result->all(), ($page_num-1) * $per_page, $per_page);

        	// ページャーオブジェクトを生成
        	$pager= new \Illuminate\Pagination\LengthAwarePaginator(
        			$result, // ページ番号で指定された表示するレコード配列
        			$count2, // 検索結果の全レコード総数
        			$per_page, // 1ページ当りの表示数
        			$page_num, // 表示するページ
        			['path' => $request->url()] // ページャーのリンク先のURLを指定
        			);
        	return $list = \DB::raw($sql);
        	*/

        	return $list = $this->user
        	->select('users.id','users.name','users.email','users.sub_id','users.updated_at','user_group_relations.role_key_code')
        	->join('user_group_relations', function ($join) {
        		$join->on('user_group_relations.user_id', 'users.id');
        	})->join('user_groups',function ($join) {
        		$join->on('user_group_relations.user_group_id', 'user_groups.id')
        		->on('user_groups.group_type', \DB::raw("'文化連'"));
        	})
        	->whereNull('user_group_relations.deleted_at')
        	->orderBy('users.id')
        	->paginate($count);


        } else {
        	$id = $request->user_group_id;
        	return $list = $this->user
        	->select('users.id','users.name','users.email','users.sub_id','users.updated_at','user_group_relations.role_key_code')
        	->join('user_group_relations', function ($join) use ($id) {
        		$join->on('user_group_relations.user_id', 'users.id')
        		->on('user_group_relations.user_group_id', \DB::raw("{$id}"));
        	})->join('roles',function ($join) {
        		$join->on('user_group_relations.role_key_code', 'roles.key_code');
        	})
        	->whereNull('user_group_relations.deleted_at')
        	->where('roles.group_type','<>',\DB::raw("'文化連'"))
        	->distinct() // TODO 件数の全件(total)がおかしくなる件
        	->orderBy('users.id')
        	->paginate($count);

        }

    }

    /**
     * ユーザグループリレーションからグループ情報取得
     *
     * @param Request $request リクエスト情報
     * @return ユーザグループ情報
     */
    public function getGroups(Request $request)
    {
        $result = $this->getUser($request->id);
        return $this->userGroup
            ->select('user_groups.id', 'user_groups.name')
            ->join('user_group_relations','user_group_relations.user_group_id','user_groups.id')
            ->where('user_group_relations.user_id', $result->id)
            ->get();
    }

    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = new User();

            $last_id = $this->user->orderBy('id', 'desc')->first()->id;
            $user->id = $last_id + 1;

            $user->name = $request->name;
            $user->email = $request->email;
            $user->sub_id = $request->sub_id;
            $user->password = Hash::make($request->password);
            $user->primary_user_group_id = Auth::user()->primary_user_group_id;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->is_adoption_mail = ($request->is_adoption_mail) ? true : false;
            $user->is_claim_mail = ($request->is_claim_mail) ? true : false;
            //$user->is_google_account = ($request->is_google_account) ? true : false;
            $user->save();

            // ユーザーグループ連携テーブルの作成(authorが入らないのでsyncを使うのやめた
            //$user->userGroups()->sync([$request->user_group_id]);
            /*
            $relation = new UserGroupRelation();
            $relation->user_id = $user->id;
            $relation->user_group_id = $request->user_group_id;
            $relation->save();
            */
            \DB::commit();
            $request->session()->flash('message', '登録しました');

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * ロール更新
     */
    public function setRoles(Request $request)
    {
        $roles = [];
        \DB::beginTransaction();
        try {
            foreach($request->roles as $key => $value) {
                $result = $this->userGroupRelation
                    ->where('user_group_id', $request->user_group_id)
                    ->where('user_id', $key)
                    ->first();
                if ($result->role_key_code !== $value) {
                    $result->role_key_code = $value;
                    $result->save();
                };
            }
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * パスワードリセットのメール送信
     *
     * @param Request $request リクエスト情報
     * @param User $user ユーザ情報
     */
    private function sendMail(Request $request, $user)
    {
        $email = $user->email;
        // トークン取得
        $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($user);
        // メール送信
        Mail::send('mail.passwordreset',
            [
                'sub_id' => $user->sub_id,
                'actionUrl' => $request->root() . '/password/reset/' . $token
            ], function($message) use ($email) {
                $message
                    ->from(env('MAIL_ADMIN_ADDRESS'))
                    //->from(\Config::get('mail.from.admin_address'))
                    ->to($email)
                    ->subject("パスワードリセットしましたので担当者様パスワード変更をお願いいたします。");
            });
    }

    /*
     * ユーザ情報更新
     */
    public function edit(Request $request)
    {
        $editUser = Auth::User();
        \DB::beginTransaction();
        try {
            $user = $this->user->withTrashed()->find($request->id);
            $user->name = $request->name;
            $user->email = $request->email;
            // パスワードは入力されていれば更新
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->primary_user_group_id = $request->user_group_id;
            $user->sub_id = $request->sub_id;
            $user->is_adoption_mail = ($request->is_adoption_mail) ? true : false;
            $user->is_claim_mail = ($request->is_claim_mail) ? true : false;
            $user->is_google_account = ($request->is_google_account) ? true : false;

            // ユーザ復旧
            if (!is_null($user->deleter) && empty($request->stop)) {
                $user->deleter = null;
                $user->deleted_at = null;
                // ここでパスワードリセット処理
                $this->sendMail($request, $user);
            } elseif (is_null($user->deleter) && !empty($request->stop)) {
                // ユーザ停止
                $user->deleter = $editUser->id;
                $user->deleted_at = Carbon::now();
            }
            $user->save();

            $relation = UserGroupRelation::where('user_id', $user->id)->first();
            if (empty($relation)){
            	$relation = new UserGroupRelation();
            	$relation->user_id = $user->id;
            	$relation->user_group_id = $request->user_group_id;
            	$relation->save();
            }
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            return true;

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 編集用のバリデーション取得
     */
    public function editValidation(Request $request)
    {
        $validate = [
            'name' => 'required|string|max:255',
            //'user_group_id' => 'required',
        ];
        $user = $this->user->withTrashed()->find($request->id);

        // メールが変更されてたらユニークチェック
        if ($user->email !== $request->email || $user->sub_id !== $request->sub_id) {
            $validate['email'] = 'required|string|email|max:255|unique:users,email,NULL,sub_id,sub_id,' . $request->sub_id;
        }
        if ($request->password) {
            $validate['password'] = 'string|min:6|confirmed';
        }
        return $validate;
    }

    /*
     * 一覧の検索条件
     */
    public function getConditions(Request $request)
    {
    	$conditions = [];
    	$conditions['user_name'] = '';
    	$conditions['user_email'] = '';
    	$conditions['user_sub_id'] = '';

    	if ($request->user_name) {
    		$conditions['user_name'] = $request->user_name;
    	}
    	if ($request->user_email) {
    		$conditions['user_email'] = $request->user_email;
    	}
    	if ($request->user_sub_id) {
    		$conditions['user_sub_id'] = $request->user_sub_id;
    	}
        return $conditions;
    }

    /*
     * 削除処理
     */
    public function delete(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = $this->user->find($request->id);
            $user->delete();
            \DB::commit();
            $request->session()->flash('message', '削除しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * パスワードリセット実行
     */
    public function reset(Request $request)
    {
        // ユーザ情報取得
        $user = $this->user->withTrashed()->find($request->id);
        \DB::beginTransaction();
        try {
            if (!is_null($user->deleter)) {
                // 論理削除を解除
                $user->deleter = null;
                $user->deleted_at = null;
                $user->save();
            }
            // ここでパスワードリセット処理
            $this->sendMail($request, $user);
            \DB::commit();
            $request->session()->flash('message', 'パスワードリセットしました');
            return $user;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 管理者確認
     */
    public function getAdmin()
    {
        $user = Auth::user();
        $adminIds = $user->admin();
        if (!empty($adminIds)) {
            $adminIds = collect($adminIds)->pluck('id');
        }
        $isAdmin = false;
        foreach($adminIds as $value) {
            if ($user->id === (int)$value) {
                $isAdmin = true;
                break;
            };
        }
        return $isAdmin;
    }
}
