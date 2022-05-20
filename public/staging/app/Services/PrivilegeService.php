<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Model\ApplicationUseRequest;
use App\Model\ApplicationGroupRequest;
use App\Model\GroupRoleRelation;
use App\Model\UserGroup;
use App\Model\Privilege;
use App\Model\RolePrivilegeRelation;
use App\Model\UserGroupRelation;
use App\Http\Controllers\Concerns\Pager;
use App\User;
use App\Services\BaseService;

class PrivilegeService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;
    const DEFAULT_DISP_ORDER = 1;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->applicationUseRequest   = new ApplicationUseRequest();
        $this->applicationGroupRequest = new ApplicationGroupRequest();
        $this->userGroup               = new UserGroup();
        $this->user                    = new User();
        $this->userGroupRelation       = new UserGroupRelation();
        $this->groupRoleRelation       = new GroupRoleRelation();
        $this->privilege               = new Privilege();
        $this->rolePrivilegeRelation   = new RolePrivilegeRelation();
    }

    /**
     * 権限一覧
     *
     * @param Request $request リクエスト情報
     * @return 権限一覧
     */
    public function getPrivileges(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $name       = empty($request->name) ? null : '%'.$request->name.'%';
        return $this->privilege
                    ->where(function ($query) use ($name) {
                        if (!empty($name)) {
                            $query->where('name', 'LIKE', $name);
                        }
                    })
                    ->orderBy('id', 'asc')
                    ->paginate($count);
    }

    /**
     * CSV用権限一覧
     *
     * @param Request $request リクエスト情報
     * @return 権限一覧
     */
    public function getPrivilegesCsv(Request $request)
    {
        $list = \DB::select('select * from v_user_privilege_detail_audit_list');
        $list = json_decode(json_encode($list), true);
        $csvHeader = ['ユーザID','メールアドレス','サブID','ユーザ名','最終ログイン日時','作成日時','ユーザグループID','ユーザグループ名','グループキー','グループ区分','ロールキー','権限キー'];
        array_unshift($list, $csvHeader);
        return $list;
    }

    /**
     * JSON出力用権限一覧
     *
     * @param Request $request リクエスト情報
     * @param bool $isEdit true=編集, false=新規
     * @return 権限一覧
     */
    public function getPrivilegeList(Request $request, bool $isEdit = false)
    {
        $name       = empty($request->name) ? null : '%'.$request->name.'%';
        $list = $this->privilege
                    ->where(function ($query) use ($name) {
                        if (!empty($name)) {
                            $query->where('name', 'LIKE', $name);
                        }
                    })
                    ->orderBy('id', 'asc')
                    ->get();
        if ($isEdit === false) {
            return $list;
        }
        // 編集なので、既に登録されている情報をマージする
        $diff = $this->rolePrivilegeRelation
            ->select('privilege_key_code as key_code')
            ->where('role_key_code', $request->role_key)
            ->pluck('key_code')
            ->toArray();
        $result = [];
        foreach ($list as $value) {
            $checked = '';
            if (in_array($value->key_code, $diff)) {
                $checked = ' checked ';
            }
            $result[] = ['key_code' => $value->key_code, 'use' => $checked];
        }
        return $result;
    }

    /*
     * 権限登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $privilege = new Privilege();
            $privilege->id             = $privilege->getPrivilegeId();
            $privilege->name           = $request->name;
            $privilege->key_code       = $request->key_code;
            $privilege->privilege_type = $request->privilege_type;
            $privilege->description    = $request->description;
            $privilege->disp_order     = self::DEFAULT_DISP_ORDER;
            $privilege->save();
            \DB::commit();
            $request->session()->flash('message', '登録しました');
            return true;
        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            return false;
        }
    }

    /**
     * 権限更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            // 権限情報更新
            $privilege = $this->privilege->find($request->id);
            $privilege->name        = $request->name;
            $privilege->description = $request->description;
            $privilege->save();
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
     * 削除処理
     */
    public function delete(Request $request)
    {
        \DB::beginTransaction();
        try {
            $privilege = $this->privilege->find($request->id);
            $privilege->delete();
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
     * 一覧の検索条件
     *
     * @param Request $request リクエスト情報
     */
    public function getConditions(Request $request)
    {
    	$conditions = [];
    	$conditions['name'] = '';
    	if ($request->name) {
    		$conditions['name'] = $request->name;
    	}
        return $conditions;
    }
}
