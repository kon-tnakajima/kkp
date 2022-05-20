<?php

namespace App\Services;

use App\Model\UserGroup;
use App\Model\Facility;
use App\Containers\UserContainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class UserGroupService 
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->userGroup = new UserGroup();
        $this->facility = new Facility();
    }


    /*
     * 一覧画面
     */
    public function getUserGroupList(Request $request)
    {
        $facility = app('UserContainer')->getFacility();

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        return $facility->userGroups()->orderBy('id', 'asc')->paginate($count);
    }

    
    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $userGroup = new UserGroup();
            $userGroup->name = $request->name;
            $userGroup->facility_id = app('UserContainer')->getFacility()->id;
            $userGroup->save();

            \DB::commit();
            $request->session()->flash('message', '登録しました');

            return true;
            
        } catch (\PDOException $e){
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
            $userGroup = $this->userGroup->find($request->id);
            $userGroup->name = $request->name;
            $userGroup->save();

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
     * 一覧の検索条件
     */
    public function getConditions(Request $request)
    {
        // 検索条件が決まったら実装
        return [];
    }

    /* 
     * 削除処理
     */
    public function delete(Request $request)
    {
        $userGroup = $this->userGroup->find($request->id);
        if ($userGroup->users()->exists()) {
            $request->session()->flash('errorMessage', '所属ユーザーがいるグループは削除できません');
            return false;
        }
        \DB::beginTransaction();
        try {
            $userGroup->delete();
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
