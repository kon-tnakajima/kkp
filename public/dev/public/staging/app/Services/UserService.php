<?php

namespace App\Services;

use App\User;
use App\Model\Facility;
use App\Model\UserGroupRelation;
use App\Containers\UserContainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class UserService 
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->user = new User();
        $this->facility = new Facility();
    }

    /*
     * 標準薬品情報取得
     */
    public function getMedicine($id)
    {
        return $this->medicine->find($id);
    }

    /*
     * 一覧画面
     */
    public function getUserList(Request $request)
    {
        $facility = app('UserContainer')->getFacility();

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list = $this->user->getUserList($request, $facility, $count);
        return $list;
    }

    /*
     * ログインしているユーザが所属している施設のグループ一覧
     */
    public function getGroups()
    {
        $facility = app('UserContainer')->getFacility();
        return $facility->userGroups()->get();
    }

    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->facility_id = Auth::user()->facility_id;
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->is_adoption_mail = ($request->is_adoption_mail) ? true : false;
            $user->is_claim_mail = ($request->is_claim_mail) ? true : false;
            $user->is_google_account = ($request->is_google_account) ? true : false;
            $user->save();

            // ユーザーグループ連携テーブルの作成(authorが入らないのでsyncを使うのやめた
            //$user->userGroups()->sync([$request->user_group_id]);
            $relation = new UserGroupRelation();
            $relation->user_id = $user->id;
            $relation->user_group_id = $request->user_group_id;
            $relation->save();

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

    /*
     * ユーザ情報更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = $this->user->find($request->id);
            $user->name = $request->name;
            $user->email = $request->email;
            // パスワードは入力されていれば更新
            if (!empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            $user->is_adoption_mail = ($request->is_adoption_mail) ? true : false;
            $user->is_claim_mail = ($request->is_claim_mail) ? true : false;
            $user->is_google_account = ($request->is_google_account) ? true : false;

            $user->save();

            // ユーザーグループ連携テーブルの更新
            $relation = UserGroupRelation::where('user_id', $user->id)->first();
            $relation->user_group_id = $request->user_group_id;
            $relation->save();

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
            'user_group_id' => 'required',
        ];
        $user = $this->user->find($request->id);

        // メールが変更されてたらユニークチェック
        if ($user->email !== $request->email) {
            $validate['email'] = 'required|string|email|max:255|unique:users';
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
        $conditions = ['user_group_id' => ''];
        if ($request->user_group_id) {
            $conditions['user_group_id'] = $request->user_group_id;
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
}
