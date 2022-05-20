<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Model\ApplicationUseRequest;
use App\Model\ApplicationGroupRequest;
use App\Model\GroupRoleRelation;
use App\Model\UserGroup;
use App\Model\Role;
use App\Model\UserGroupRelation;
use App\Model\RolePrivilegeRelation;
use App\Services\UserGroupService;
use App\Http\Controllers\Concerns\Pager;
use App\User;
use Carbon\Carbon;
use App\Services\BaseService;

class RoleService extends BaseService
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
        $this->role                    = new Role();
        $this->rolePrivilegeRelation   = new RolePrivilegeRelation();
        $this->datetime                = Carbon::now();
    }

    /**
     * ロール一覧
     *
     * @param Request $request リクエスト情報
     * @return ロール一覧
     */
    public function getRoles(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $name        = empty($request->name) ? null : '%'.$request->name.'%';
        $group_type  = empty($request->group_type) ? null : $request->group_type;
        return $this->role
                    ->where(function ($query) use ($name, $group_type){
                        if (!empty($name)) {
                            $query->where('name', 'LIKE', $name);
                        }
                        if (!empty($group_type)) {
                            $query->where('group_type', $group_type);
                        }
                    })
                    ->orderBy('id', 'asc')
                    ->paginate($count);
    }

    /**
     * ロール登録実行
     */
    public function regist(Request $request)
    {
        $user = Auth::user();
        \DB::beginTransaction();
        try {
            // ロール作成
            $role = new Role();
            $role->id          = $this->role->getRoleId();
            $role->name        = $request->name;
            $role->key_code    = $request->key_code;
            $role->group_type   = $request->group_type;
            $role->description = $request->description;
            $role->disp_order  = self::DEFAULT_DISP_ORDER;
            $role->save();

            // 権限登録
            $rows = [];
            $arrPrivieges = collect($request->add_privieges)->unique();
            foreach ($arrPrivieges as $value) {
                $rows[] = [
                    'role_key_code'      => $request->key_code,
                    'privilege_key_code' => $value,
                    'creater'            => $user->id,
                    'updater'            => $user->id,
                    'created_at'         => $this->datetime,
                    'updated_at'         => $this->datetime
                ];
            }
            if (count($rows)) {
                $this->rolePrivilegeRelation
                    ->insert($rows);
            }
            \DB::commit();
            $request->session()->flash('message', '登録しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * ロール更新
     */
    public function edit(Request $request)
    {
        $user = Auth::user();
        \DB::beginTransaction();
        try {
            // ロール情報更新
            $role  = $this->role->find($request->id);
            $role->name        = $request->name;
            $role->description = $request->description;
            $role->save();

            // ロール情報更新(role_privieges_relations)
            if (empty($request->add_privieges)) {
                // リクエスト情報なしなので全削除
                $this->rolePrivilegeRelation
                    ->where('role_key_code', $role->key_code)
                    ->each(function ($query) {
                        $query->delete();
                    });
            } else {
                // ロールキーコードでrole_privilege_relationsテーブルの全レコード取得
                $originalPrivileges = $this->rolePrivilegeRelation
                    ->select('privilege_key_code')
                    ->where('role_key_code', $role->key_code)
                    ->pluck('privilege_key_code')
                    ->toArray();
                // 配列0番目は削除、配列1番目は登録情報
                $privilegeValues = UserGroupService::getTargetTidy($request->add_privieges, $originalPrivileges);
                // 削除
                if (!empty($privilegeValues[0])) {
                    $this->rolePrivilegeRelation
                        ->where('role_key_code', $role->key_code)
                        ->whereIn('privilege_key_code', $privilegeValues[0])
                        ->each(function ($query) {
                            $query->delete();
                        });
                }
                // 新規
                if (!empty($privilegeValues[1])) {
                    $insertData = [];
                    foreach($privilegeValues[1] as $vaule) {
                        $insertData[] = [
                            'role_key_code'      => $role->key_code,
                            'privilege_key_code' => $vaule,
                            'creater'            => $user->id,
                            'updater'            => $user->id,
                            'created_at'         => $this->datetime,
                            'updated_at'         => $this->datetime
                        ];
                    }
                    if (count($insertData)) {
                        $this->rolePrivilegeRelation
                            ->insert($insertData);
                    }
                }
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
     * 削除処理
     */
    public function delete(Request $request)
    {
        \DB::beginTransaction();
        try {
            $role  = $this->role->find($request->id);
            $role->delete();

            $this->rolePrivilegeRelation
                ->where('role_key_code', $role->key_code)
                ->each(function ($query) {
                    $query->delete();
                });
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
    	$conditions['group_type'] = '';
    	if ($request->name) {
    		$conditions['name'] = $request->name;
    	}
    	if ($request->group_type) {
    		$conditions['group_type'] = $request->group_type;
    	}
        return $conditions;
    }

    /**
     * ロール複製実行
     */
    public function copyRegist(Request $request)
    {
        $user = Auth::user();
        \DB::beginTransaction();
        try {
            // ロール作成
            $role = new Role();
            $role->id          = $this->role->getRoleId();
            $role->name        = $request->name;
            $role->key_code    = $request->key_code;
            $role->group_type  = $request->group_type;
            $role->description = $request->description;
            $role->disp_order  = self::DEFAULT_DISP_ORDER;
            $role->save();

            // 権限登録
            $rows = [];
            $arrPrivieges = collect($request->add_privieges)->unique();
            foreach ($arrPrivieges as $value) {
                $rows[] = [
                    'role_key_code'      => $request->key_code,
                    'privilege_key_code' => $value,
                    'creater'            => $user->id,
                    'updater'            => $user->id,
                    'created_at'         => $this->datetime,
                    'updated_at'         => $this->datetime
                ];
            }
            if (count($rows)) {
                $this->rolePrivilegeRelation
                    ->insert($rows);
            }
            \DB::commit();
            $request->session()->flash('message', '登録しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 権限情報一覧取得
     *
     * @param Request $request リクエスト情報
     * @param Privilege $privilege 権限情報
     * @return 権限情報一覧
     */
    public function getPrivilegeByKeyCode(Request $request, Role $role)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        return $this->role->getPrivilegeList($request, $role->key_code, $count);
    }
}
