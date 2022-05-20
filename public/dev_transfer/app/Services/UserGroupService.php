<?php
declare(strict_types=1);
namespace App\Services;

use App\User;
use App\Model\UserGroup;
use App\Model\GroupRelation;
use App\Model\UserGroupRelation;
use App\Model\UserGroupDataType;
use App\Model\UserGroupSupplyDivision;
use App\Model\GroupTraderRelation;
use App\Model\GroupRoleRelation;
use App\Model\OptionalMedicineKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\BaseService;

class UserGroupService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->carbon = Carbon::now();
        $this->user                    = new User();
        $this->userGroup               = new UserGroup();
        $this->userGroupRelation       = new UserGroupRelation();
        $this->userGroupDataType       = new UserGroupDataType();
        $this->groupRoleRelation       = new GroupRoleRelation();
        $this->groupRelation           = new GroupRelation();
        $this->groupTraderRelation     = new GroupTraderRelation();
        $this->userGroupSupplyDivision = new UserGroupSupplyDivision();
    }


    /**
     * 一覧画面
     *
     * @param Request $request リクエスト情報
     * @param User   $user ユーザ情報
     */
    public function getUserGroupList(Request $request, $user)
    {
        // ロールキー取得
        $role_key = $this->userGroupRelation->getRoleKey($user->id, $user->primary_user_group_id);
    	$count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        return $this->userGroup->getUserGroupListById($request, $role_key, $user->primary_user_group_id)->paginate($count);
    }

    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $userGroup = new UserGroup();
            $userGroup->id = $userGroup->getUserGroupId();
            $userGroup->name = $request->name;
            $userGroup->facility_id = -1;
            $userGroup->formal_name = $request->formal_name;
            $userGroup->group_type = $request->group_type;
            $userGroup->zip = $request->zip ?? null;
            $userGroup->address1 = $request->address1 ?? null;
            $userGroup->address2 = $request->address2 ?? null;
            $userGroup->tel = $request->tel ?? null;
            $userGroup->fax = $request->fax ?? null;
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
        $user = Auth::user();
        \DB::beginTransaction();
        try {

            // ユーザグループ情報更新
            $userGroup = $this->userGroup->find($request->id);
            $userGroup->name        = $request->name;
            $userGroup->formal_name = $request->formal_name;
            $userGroup->zip         = $request->zip ?? null;
            $userGroup->address1    = $request->address1 ?? null;
            $userGroup->address2    = $request->address2 ?? null;
            $userGroup->tel         = $request->tel ?? null;
            $userGroup->fax         = $request->fax ?? null;
            $userGroup->optional_key1_label          = $request->optional_key1_label ?? null;
            $userGroup->optional_key1_is_search_disp = $request->optional_key1_is_search_disp ?? false;
            $userGroup->optional_key2_label          = $request->optional_key2_label ?? null;
            $userGroup->optional_key2_is_search_disp = $request->optional_key2_is_search_disp ?? false;
            $userGroup->optional_key3_label          = $request->optional_key3_label ?? null;
            $userGroup->optional_key3_is_search_disp = $request->optional_key3_is_search_disp ?? false;
            $userGroup->optional_key4_label          = $request->optional_key4_label ?? null;
            $userGroup->optional_key4_is_search_disp = $request->optional_key4_is_search_disp ?? false;
            $userGroup->optional_key5_label          = $request->optional_key5_label ?? null;
            $userGroup->optional_key5_is_search_disp = $request->optional_key5_is_search_disp ?? false;
            $userGroup->optional_key6_label          = $request->optional_key6_label ?? null;
            $userGroup->optional_key6_is_search_disp = $request->optional_key6_is_search_disp ?? false;
            $userGroup->optional_key7_label          = $request->optional_key7_label ?? null;
            $userGroup->optional_key7_is_search_disp = $request->optional_key7_is_search_disp ?? false;
            $userGroup->optional_key8_label          = $request->optional_key8_label ?? null;
            $userGroup->optional_key8_is_search_disp = $request->optional_key8_is_search_disp ?? false;
            if ($request->hasFile('file')) {
                $userGroup->file_name = $request->file('file')->getClientOriginalName();
                $userGroup->attachment = encodeByteaData($request->file('file'));
            }
            $userGroup->save();

            // ロール情報更新(group_role_relations)
            if (empty($request->roles)) {
                // リクエスト情報なしなので全削除
                $this->groupRoleRelation
                    ->where('user_group_id', $request->id)
                    ->each(function ($query) {
                        $query->delete();
                    });
            } else {
                // ユーザグループIDでgroup_role_relationsテーブルの全レコード取得
                $originalRoles = $this->groupRoleRelation
                    ->select('role_key_code')
                    ->where('user_group_id', $request->id)
                    ->pluck('role_key_code')
                    ->toArray();
                // 配列0番目は削除、配列1番目は登録情報
                $roleValues = $this->getTargetTidy($request->roles, $originalRoles);
                // 削除
                if (!empty($roleValues[0])) {
                    $this->groupRoleRelation
                        ->where('user_group_id', $request->id)
                        ->whereIn('role_key_code', $roleValues[0])
                        ->each(function ($query) {
                            $query->delete();
                        });
                    }
                // 新規
                if (!empty($roleValues[1])) {
                    $insertData = [];
                    foreach($roleValues[1] as $vaule) {
                        $insertData[] = [
                            'user_group_id' => $request->id,
                            'role_key_code' => $vaule,
                            'creater'       => $user->id,
                            'updater'       => $user->id,
                            'created_at'    => $this->carbon,
                            'updated_at'    => $this->carbon
                        ];
                    }
                    if (count($insertData)) {
                        $this->groupRoleRelation
                            ->insert($insertData);
                    }
                }
            }
            // 業者情報更新(group_trader_relations)
            if (empty($request->add_traders)) {
                // リクエスト情報なしなので全削除
                $this->groupTraderRelation
                    ->where('user_group_id', $request->id)
                    ->each(function ($query) {
                        $query->delete();
                    });
            } else {
                // ユーザグループIDでgroup_trader_relationsテーブルの全レコード取得
                $originalTraders = $this->groupTraderRelation
                    ->select('trader_user_group_id')
                    ->where('user_group_id', $request->id)
                    ->pluck('trader_user_group_id')
                    ->toArray();
                // 配列0番目は削除、配列1番目は登録情報
                $traderValues = $this->getTargetTidy($request->add_traders, $originalTraders);
                // 削除
                if (!empty($traderValues[0])) {
                    $this->groupTraderRelation
                        ->where('user_group_id', $request->id)
                        ->whereIn('trader_user_group_id', $traderValues[0])
                        ->each(function ($query) {
                            $query->delete();
                        });
                    }
                // 新規
                if (!empty($traderValues[1])) {
                    $insertData = [];
                    foreach($traderValues[1] as $vaule) {
                        $insertData[] = [
                            'user_group_id'        => $request->id,
                            'trader_user_group_id' => $vaule,
                            'creater'              => $user->id,
                            'updater'              => $user->id,
                            'created_at'           => $this->carbon,
                            'updated_at'           => $this->carbon
                        ];
                    }
                    if (count($insertData)) {
                        $this->groupTraderRelation
                            ->insert($insertData);
                    }
                }
            }
            // データ区分更新(user_group_data_types)
            if (empty($request->types)) {
                // リクエスト情報なしなので全削除
                $this->userGroupDataType
                    ->where('user_group_id', $request->id)
                    ->each(function ($query) {
                        $query->delete();
                    });
            } else {
                // ユーザグループIDでuser_group_data_typesテーブルの全レコード取得
                $originalTypes = $this->userGroupDataType
                    ->select('id')
                    ->where('user_group_id', $request->id)
                    ->pluck('id')
                    ->toArray();
                // 配列0番目は削除、配列1番目は登録情報
                $typeValues = $this->getTargetTidy($request->types, $originalTypes);
                // 削除
                if (!empty($typeValues[0])) {
                    $this->userGroupDataType
                        ->where('user_group_id', $request->id)
                        ->whereIn('id', $typeValues[0])
                        ->each(function ($query) {
                            $query->delete();
                        });
                    }
                // 新規
                if (!empty($typeValues[1])) {
                    $insertData = [];
                    foreach($typeValues[1] as $vaule) {
                        $insertData[] = [
                            'user_group_id'        => $request->id,
                            'data_type_name'       => $vaule,
                            'creater'              => $user->id,
                            'updater'              => $user->id,
                            'created_at'           => $this->carbon,
                            'updated_at'           => $this->carbon
                        ];
                    }
                    if (count($insertData)) {
                        $this->userGroupDataType
                            ->insert($insertData);
                    }
                }
            }
            // 供給区分更新(user_group_supply_divisions)
            if (empty($request->supplies)) {
                // リクエスト情報なしなので全削除
                $this->userGroupSupplyDivision
                    ->where('user_group_id', $request->id)
                    ->each(function ($query) {
                        $query->delete();
                    });
            } else {
                // ユーザグループIDでuser_group_supply_divisionsテーブルの全レコード取得
                $originalSupplies = $this->userGroupSupplyDivision
                    ->select('id')
                    ->where('user_group_id', $request->id)
                    ->pluck('id')
                    ->toArray();
                // 配列0番目は削除、配列1番目は登録情報
                $supplyValues = $this->getTargetTidy($request->supplies, $originalSupplies);
                // 削除
                if (!empty($supplyValues[0])) {
                    $this->userGroupSupplyDivision
                        ->where('user_group_id', $request->id)
                        ->whereIn('id', $supplyValues[0])
                        ->each(function ($query) {
                            $query->delete();
                        });
                    }
                // 新規
                if (!empty($supplyValues[1])) {
                    $insertData = [];
                    foreach($supplyValues[1] as $vaule) {
                        $insertData[] = [
                            'user_group_id'        => $request->id,
                            'supply_division_name' => $vaule,
                            'creater'              => $user->id,
                            'updater'              => $user->id,
                            'created_at'           => $this->carbon,
                            'updated_at'           => $this->carbon
                        ];
                    }
                    if (count($insertData)) {
                        $this->userGroupSupplyDivision
                            ->insert($insertData);
                    }
                }
            }
            // オプション関連
            for ($loop=1; $loop < 9; $loop++) {
                $requestOptionName = sprintf("optional_medicine_key%d", $loop);
                $model = new OptionalMedicineKey();
                $model->setTable(sprintf('optional_medicine_key%d', $loop));
                if (empty($request->$requestOptionName)) {
                    $model->where('user_group_id', $request->id)
                        ->each(function ($query) {
                            $query->delete();
                        });
                } else {
                    $originalOptions = $model
                        ->select('id')
                        ->where('user_group_id', $request->id)
                        ->pluck('id')
                        ->toArray();
                    $optionValues = $this->getTargetTidy($request->$requestOptionName, $originalOptions);
                    // 削除
                    if (!empty($optionValues[0])) {
                        $model->where('user_group_id', $request->id)
                                ->whereIn('id', $optionValues[0])
                                ->each(function ($query) {
                                    $query->delete();
                                });
                        }
                    // 新規
                    if (!empty($optionValues[1])) {
                        $insertData = [];
                        $dispOrder = $model->getSortNo($loop, (int)$request->id) + 1;
                        foreach($optionValues[1] as $vaule) {
                            $insertData[] = [
                                'user_group_id' => $request->id,
                                'value'         => $vaule,
                                'disp_order'    => $dispOrder,
                                'creater'       => $user->id,
                                'updater'       => $user->id,
                                'created_at'    => $this->carbon,
                                'updated_at'    => $this->carbon
                            ];
                            $dispOrder++;
                        }
                        if (count($insertData)) {
                            $model->insert($insertData);
                        }
                    }
                }
            }

            // 病院の紐づけ処理
            if (!empty($request->add_hospitals)) {

            	// 初期状態に戻す処理
            	$this->shokiHospital($request);

            	// 紐づけ対象病院の更新処理
            	foreach ($request->add_hospitals as $hospital) {

            		// 病院で初期のデータを更新する
            	    $group = $this->groupRelation->where('user_group_id',$hospital)->where('partner_user_group_id',1)->get();
            	    foreach($group as $gp) {
            			$gp->user_group_id = $hospital; //病院ID
            			$gp->partner_user_group_id =$request->id; // 本部ID
            			$gp->save();
            	    }

            		//追加病院に該当するユーザ
            		$updateUser = $this->user->where('primary_user_group_id',$hospital)->get();
            		foreach($updateUser as $u) {
            			$u->primary_honbu_user_group_id = $request->id; // 本部ID
            			$u->save();
            		}

            		// TODO role_key_codeの本部管理者は要確認
            		//本部ユーザ　病院データを参照可能とする。
            		$updateUser = $this->user->where('primary_honbu_user_group_id',$request->id)->get();

            		foreach($updateUser as $u) {
            			$check = $this->userGroupRelation->where('user_id',$u->id)->where('user_group_id',$hospital)->get();

            			if (count($check)==0) {
            				$ugr = new UserGroupRelation();

            				$ugr->user_id       = $u->id;
            				$ugr->user_group_id = $hospital;
            				$ugr->role_key_code = "本部管理者";
            				$ugr->save();

            			}
            		}

            		//病院ユーザを参照可能に
            		$updateUser = $this->user->where('primary_user_group_id',$hospital)->get();
            		foreach($updateUser as $u) {
            			$check = $this->userGroupRelation->where('user_id',$u->id)->where('user_group_id',$request->id)->get();

            			if (count($check)==0) {
            				$ugr = new UserGroupRelation();

            				$ugr->user_id       = $u->id;
            				$ugr->user_group_id = $request->id;
            				$ugr->role_key_code = "参照";
            				$ugr->save();

            			}
            		}
            	}
            } else {
            	// 全ての病院紐づけの状態をなくす場合
            	$this->shokiHospital($request);
            }

            /*
            // 所属化処理
            $role_key = $this->userGroupRelation->getRoleKey($user->id, $user->primary_user_group_id);
            $userGroup = $this->userGroup->find($user->primary_user_group_id);
            if ($role_key === \Config::get('const.user_attribute.GROUP_AUTHORIZED') &&
                $user->primary_user_group_id === (int)$request->id &&
                $userGroup->group_type === \Config::get('const.headquarters_name')) {
                $childrenIds = $this->userGroup->children((int)$request->id)->pluck('id')->toArray();
                foreach ($childrenIds as $key => $id) {
                    // 認証しているユーザグループIDが一致したら排除
                    if ((int)$id === $user->primary_user_group_id) {
                        unset($childrenIds[$key]);
                    }
                }
                if (!empty($request->add_hospitals)) {
                    // リクエストでの病院登録あり
                    foreach ($request->add_hospitals as $hospital) {
                        $status = array_search($hospital, $childrenIds);
                        if ($status === false) {
                            // 所属ユーザグループではないので、所属更新処理
                            $group = $this->groupRelation->where('user_group_id', $hospital)->where('partner_user_group_id', $hospital)->first();
                            $group->partner_user_group_id = $user->primary_user_group_id;
                            $group->save();
                        }
                    }
                    // DB側
                    foreach ($childrenIds as $children) {
                        $status = array_search($children, $request->add_hospitals);
                        if ($status === false) {
                            // 所属ユーザグループではないので、所属更新処理
                            $group = $this->groupRelation->where('user_group_id', $children)->where('partner_user_group_id', $user->primary_user_group_id)->first();
                            $group->partner_user_group_id = $children;
                            $group->save();
                        }
                    }
                } else {
                    // リクエストでの病院登録なし
                    foreach ($childrenIds as $children) {
                        // 所属ユーザグループではないので、所属更新処理
                        $group = $this->groupRelation->where('user_group_id', $children)->where('partner_user_group_id', $user->primary_user_group_id)->first();
                        if (!empty($group)) {
                            $group->partner_user_group_id = $children;
                            $group->save();
                        }
                    }
                }
            }
            */
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
     * 病院紐づけ時の初期化処理
     *
     *
     */
    public function shokiHospital(Request $request)
    {
    	$hospitalUser = $this->groupRelation->where('partner_user_group_id',$request->id)->get();
    	//logger('初期化');
    	//\Log::debug($hospitalUser);
    	foreach($hospitalUser as $hu) {
    		// group_relationの初期化
    		$group = $this->groupRelation->find($hu->id);
    		$group->user_group_id = $hu->user_group_id; //病院ID
    		$group->partner_user_group_id =1; // 文化連
    		$group->save();
    		// userの初期化
    		$updateUser = $this->user->where('primary_user_group_id', $hu->user_group_id)->get();
    		foreach ($updateUser as $u) {
    			$u->primary_honbu_user_group_id = $hu->user_group_id; // 病院ID
    			$u->save();
    		}
    		// group_role_relationsnsは初期化しない

    	}
    	//\Log::debug('成功');
    }

    /**
     * ユーザグループ種別一覧
     *
     * @return object ユーザグループ種別一覧情報
     */
    public static function getUserGroupTypes()
    {
        $stdArray = [];
        $obj = new \stdClass();
        $obj->name = '文化連';
        $stdArray[] = $obj;
        $obj = new \stdClass();
        $obj->name = '本部';
        $stdArray[] = $obj;
        $obj = new \stdClass();
        $obj->name = '病院';
        $stdArray[] = $obj;
        $obj = new \stdClass();
        $obj->name = '業者';
        $stdArray[] = $obj;
        $obj = new \stdClass();
        $obj->name = 'その他';
        $stdArray[] = $obj;
        $obj = new \stdClass();
        $obj->name = '個人';
        $stdArray[] = $obj;

        return $stdArray;
    }

    /*
     * 一覧の検索条件
     */
    public function getConditions(Request $request)
    {
        $conditions = [];
        $conditions['user_group_name'] = $conditions['user_group_type'] = '';
        if ($request->user_group_name) {
            $conditions['user_group_name'] = $request->user_group_name;
        }
        if ($request->user_group_type) {
            $conditions['user_group_type'] = $request->user_group_type;
        }
        return $conditions;
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

    /**
     * ロール情報取得
     *
     * @param int $user_group_id ユーザグループID
     * @return objet ロール情報
     */
    public function getRoles(int $user_group_id)
    {
        // 存在しない場合エラー
        if (empty($user_group_id)) {
            return [];
        }
        if (!is_numeric($user_group_id)) {
            return [];
        }
        // 返却する情報
        $list = [];
        // ユーザグループで登録されているロール全て取得
        $result = $this->getUserGroupRoleList($user_group_id);
        // ユーザロールリレーションにある利用しているロール情報取得
        $diffs   = $this->getGroupRoleRelationList($user_group_id);
        foreach($result as $value) {
            $checked = '';
            foreach ($diffs as $diff) {
                if ($diff === $value['name']) {
                    // 両半角スペースは、直接htmlに表示させている為
                    $checked = ' checked ';
                    break;
                }
            }
            $list[] = ['name' => $value['name'], 'use' => $checked];
        }
        return $list;
    }


    /**
     * 病院情報取得
     *
     * @param int $user_group_id ユーザグループID
     * @param string $group_type グループ区分
     * @return objet ロール情報
     */
    public function getHospitals(int $user_group_id, string $role_key)
    {
        $user = Auth::user();
        // グループ管理者以外は処理しない
        //if ($role_key !== \Config::get('const.user_attribute.GROUP_AUTHORIZED')) {
        //    return [];
        //}
        // 本部配下の所属ユーザグループ（病院）のみ取得
        $result = $this->userGroup->getHonbuHospital($user_group_id);
        foreach($result as $key => $value) {
            if ($value->group_type !== \Config::get('const.hospital_name')) {
                unset($result[$key]);
            }
        }

        // 現状の紐づいていない病院を取得
        $hospitals = $user->getSingleHospitals();
        // ユーザからのアプローチで優先本部ユーザグループIDと優先ユーザグループIDが一致しているもの取得
        //$hospitals=$this->userGroup->nonJoinHosipital(); //元のロジック
        $all = [];
        if (!count($result)) {
            foreach($hospitals as $value) {
                $all[] = [
                    'id'   => $value->id,
                    'name' => $value->name,
                    'use'  => ''
                ];
            }
        } else {
            // 所属のみ
            foreach($result as $value) {
                $all[] = [
                    'id'   => $value->id,
                    'name' => $value->name,
                    'use'  => ' selected'
                ];
            }
            // 病院のみ
            foreach($hospitals as $value) {
                // 不一致
                if ($this->searchArrayById((int)$value->id, $all) === false){
                    $all[] = [
                        'id'   => $value->id,
                        'name' => $value->name,
                        'use'  => ''
                    ];
                }
            }
        }
        return $all;
    }

    /**
     * ユーザグループで登録されているロール全て取得
     *
     * @param int $user_group_id ユーザグループID
     * @return objet ロール情報
     */
    private function getUserGroupRoleList(int $user_group_id)
    {
        return $this->userGroup
            ->select('roles.name')
            ->join('roles', 'user_groups.group_type', '=', 'roles.group_type')
            ->whereNull('roles.deleted_at')
            ->where('user_groups.id', $user_group_id)
            ->orderBy('roles.id')
            ->get();
    }

    /**
     * 利用しているロール情報取得
     *
     * @param int $user_group_id ユーザグループID
     * @return objet ロール情報
     */
    private function getGroupRoleRelationList(int $user_group_id)
    {
        return $this->userGroup
            ->select('group_role_relations.role_key_code')
            ->join('group_role_relations', 'user_groups.id', '=', 'group_role_relations.user_group_id')
            ->whereNull('group_role_relations.deleted_at')
            ->where('user_groups.id', $user_group_id)
            ->orderBy('group_role_relations.id')
            ->pluck('role_key_code');
    }

    /**
     * 業者情報取得
     *
     * @param int $user_group_id ユーザグループID
     * @return objet 業者情報
     */
    public function getTraders(int $user_group_id)
    {
        // 存在しない場合エラー
        if (empty($user_group_id)) {
            return [];
        }
        if (!is_numeric($user_group_id)) {
            return [];
        }
        // 返却する情報
        $list = [];
        // ユーザグループで登録されている業者全て取得
        $result = $this->getUserGroupTraderList();
        // ユーザトレーダーリレーションにある利用している業者情報取得
        $diffs   = $this->getGroupTraderRelationList($user_group_id);
        foreach($result as $value) {
            $checked = '';
            foreach ($diffs as $diff) {
                if ($diff->trader_user_group_id === $value->id) {
                    // 両半角スペースは、直接htmlに表示させている為
                    $checked = ' checked ';
                    break;
                }
            }
            $list[] = ['trader_id' => $value->id, 'name' => $value->name, 'address' => $value->address1, 'use' => $checked];
        }
        return $this->setTraders($list);
    }

    private function str_pad(int $len)
    {
        $str = '';
        for($loop=0; $loop < $len; $loop++) {
            $str .= '　';
        }
        return $str;
    }

    /**
     * 業者情報を加工
     * @param array $arrList 業者情報
     * @return array 業者情報
     */
    private function setTraders(array $arrList): array
    {
        $maxLength = 0;
        foreach($arrList as $key => $row) {
            $arrList[$key]['name'] = mb_convert_kana($arrList[$key]['name'], 'KVAS');
            if ($maxLength < mb_strlen($arrList[$key]['name'])) {
                $maxLength = mb_strlen($arrList[$key]['name']);
            }
        }
        // 1文字空ける為
        $maxLength++;
        foreach($arrList as $key => $row) {
            $len = mb_strlen($arrList[$key]['name']);
            $arrList[$key]['name'] = sprintf("%s%s%s", $arrList[$key]['name'], $this->str_pad($maxLength - $len), $arrList[$key]['address']);
        }
        return $arrList;
    }

    /**
     * ユーザグループで登録されている業者全て取得
     *
     * @return objet 業者情報
     */
    private function getUserGroupTraderList()
    {
        return $this->userGroup
            ->select('id', 'name', 'address1')
            ->where('group_type', \Config::get('const.trader_name'))
            ->orderBy('disp_order')
            ->get();
    }

    /**
     * 利用しているロール情報取得
     *
     * @params int $user_group_id ユーザグループID
     * @return objet ロール情報
     */
    private function getGroupTraderRelationList(int $user_group_id)
    {
        return $this->groupTraderRelation
            ->select('trader_user_group_id')
            ->where('user_group_id', $user_group_id)
            ->get();
    }

    /**
     * 使用データ区分取得
     *
     * @param int $user_group_id ユーザグループID
     * @return データ区分情報
     */
    public function getTypeList(int $user_group_id)
    {
        return $this->userGroupDataType->select('id', 'data_type_name')->where('user_group_id', $user_group_id)->orderBy('disp_order')->get();
    }

    /**
     * 使用供給区分取得
     *
     * @param int $user_group_id ユーザグループID
     * @return 供給区分情報
     */
    public function getSupplyList(int $user_group_id)
    {
        return $this->userGroupSupplyDivision->select('id', 'supply_division_name')->where('user_group_id', $user_group_id)->orderBy('disp_order')->get();
    }

    /**
     * リクエスト情報とDB情報の整理
     * @param array $tagets リクエスト情報
     * @param array $originals 元情報
     * @return array ユーザグループテーブルの[0]削除対象,[1]新規作成対象
     */
    public static function getTargetTidy(array $tagets, array $originals): array
    {
        $inserts = [];
        foreach($tagets as $taget) {
            $result = array_search($taget, $originals);
            if ($result !== false) {
                // 一致は更新不要なので除外
                unset($originals[$result]);
                continue;
            }
            $inserts[] = $taget;
        }
        // 削除対象,新規作成対象
        return [$originals ?? [], $inserts];
    }

    /**
     * 業者一覧取得
     */
    public function searchTraders(Request $request)
    {
        $name = empty($request->name) ? null : '%'.$request->name.'%';
        $list = $this->userGroup
            ->select('id', 'name', 'address1 as address')
            ->where('group_type', \Config::get('const.trader_name'))
            ->where(function ($query) use ($name) {
                if (!empty($name)) {
                    $query->where('name', 'LIKE', $name);
                }
            })
            ->orderBy('disp_order')
            ->get()
            ->toArray();
        return $this->setTraders($list);
    }

    /**
     * 病院一覧取得
     */
    public function searchHospitals(Request $request)
    {
    	$user = Auth::user();
    	$name = empty($request->name) ? null : '%'.$request->name.'%';
    	// 単一と認証しているユーザグループIDの条件のものを取得する。
    	//$result = $user->getSingleHospitals($user->primary_user_group_id, $name);
    	$result = $user->getSingleHospitals(null, $name);
    	return collect($result)->toArray();
    }

    /**
     * オプション情報取得
     *
     * @param int $no 番号
     * @param int $user_group_id ユーザグループID
     * @return オプション情報
     */
    public function getOptionList(int $no, int $user_group_id)
    {
        return \DB::select("
                    select
                        id, value
                    from
                        optional_medicine_key{$no}
                    where
                        deleted_at is null and
                        user_group_id = ?
                    order by disp_order",
                [$user_group_id]);
    }

    /**
     * 配列のIDが一致確認
     * @param int $id ユーザグループID
     * @param array $arr 配列
     * @return bool true=一致, false=不一致
     */
    public function searchArrayById(int $id, array $arr): bool
    {
        foreach ($arr as $value) {
            if ((int)$value['id'] === $id) {
                return true;
            }
        }
        return false;
    }
}
