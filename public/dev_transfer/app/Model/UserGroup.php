<?php

namespace App\Model;

use App\Model\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;
use App\Model\Concerns\UserGroup as UserGroupTrait;

class UserGroup extends BaseModel {
    use SoftDeletes;
    use UserGroupTrait;

    public static function boot() {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /**
     * シーケンスIDを取得
     */
    public function getUserGroupId() {
        $this->start_log();

        $result = \DB::select("select nextval('user_groups_id_seq') as nextid");

        $this->end_log();
        return $result[0]->nextid;
    }

    /*
     * グループに所属しているユーザー取得
     */
    public function users() {
        $this->start_log();

        $result = $this->belongsToMany('App\User', 'user_group_relations');

        $this->end_log();
        return $result;
    }

    /*
     * グループに紐付いているロールの取得
     */
    public function role() {
        $this->start_log();

        $result = $this->hasOne('App\Model\Role');

        $this->end_log();
        return $result;
    }

    /**
     * 文化連か確認
     */
    public function isBunkaren() {
        $this->start_log();

        $result = $this->group_type === \Config::get('const.bunkaren_name');

        $this->end_log();
        return $result;
    }

    /**
     * 病院か確認
     */
    public function isHospital() {
        $this->start_log();

        $result = $this->group_type === \Config::get('const.hospital_name');

        $this->end_log();
        return $result;
    }

    /**
     * ユーザグループテーブル（子）より情報取得
     *
     * グループリレーションテーブルからパートナーユーザグループIDの
     * ユーザグループID取得し、ユーザグループテーブルから情報取得。
     */
    public function children(int $user_group_id = null) {
        $this->start_log();

        $id = empty($user_group_id) ? $this->id : $user_group_id;

        $sql = "
                select
                    *
                from
                    user_groups
                where id in (
                        select
                            user_group_id
                        from
                            group_relations
                        where
                            partner_user_group_id in (
                                select
                                    partner_user_group_id
                                from
                                    group_relations
                                where
                                    user_group_id = {$id}
                                    and deleted_at is null limit 1
                            )
                        and deleted_at is null
                    )
                and deleted_at is null";

        $rec = \DB::select($sql);

        $result = UserGroup::query()->hydrate($rec);

        $this->end_log();
        return $result;
    }

    /**
     * ユーザグループテーブル(親)より情報取得
     *
     * グループリレーションテーブルからパートナーユーザグループIDの
     * ユーザグループID取得し、ユーザグループテーブルから情報取得。
     */
    public function parent(int $user_group_id = null) {
        $this->start_log();

        $id = empty($user_group_id) ? $this->id : $user_group_id;
        $result = $this
            ->query()
            ->where(
                'id',
                \DB::raw(" (select partner_user_group_id from group_relations where user_group_id = ({$id}) and deleted_at is null limit 1) ")
            )
            ->get();

        $this->end_log();
        return $result;
    }

    /**
     * 所属するグループ一覧取得
     *
     * @param User $user ユーザ情報
     * @return UserGroups ユーザグループ情報
     */
    public function getUserGroups($user) {
        $this->start_log();

        $result = $this
            ->query()
            ->select('user_groups.*')
            ->join('user_group_relations', function ($join) {
                $join
                    ->on('user_group_relations.user_group_id', '=', 'user_groups.id')
                    ->whereNull('user_group_relations.deleted_at');
            })
            ->where('user_group_relations.user_id', $user->id)
            ->orderBy('user_groups.id')
            ->get();

        $this->end_log();
        return $result;
    }

    /**
     * group_trader_relationsに対して、施設または業者として、所属する施設一覧を取得
     *
     * @param User $user ユーザ情報
     * @return UserGroups ユーザグループ情報
     */

    public function getUserGroupsFacility($user) {
        $this->start_log();

        $sql  = " select";
        $sql .= " fcr.user_id";
        $sql .= " , fcr.user_group_id";
        $sql .= " , fcr.user_group_name";
        $sql .= " , ug.disp_order";
        $sql .= " from ";
        $sql .= "      f_show_records_facility(". $user->id .") fcr";
        $sql .= " inner join user_groups ug ";
        $sql .= " on fcr.user_group_id = ug.id ";
        $sql .= " order by ug.disp_order,user_group_id";

        $this->end_log();
        return $this->simple_execute_sql($sql);
    }

    /**
     * 請求登録 施設一覧
     *
     * @param User $user ユーザ情報
     * @return UserGroups ユーザグループ情報
     */
    public function getUserGroupClaimRegistFacility($user) {
        $this->start_log();

        $sql  = " select ";
        $sql .= "      vprc.user_group_id as id ";
        $sql .= "     ,facility.name as name ";
        $sql .= " from ";
        $sql .= "      f_privileges_row_claim_regist(". $user->id .") as vprc ";
        $sql .= "      inner join user_groups as facility ";
        $sql .= "       on facility.id = vprc.user_group_id ";
        $sql .= " where ";
        $sql .= "      vprc.user_id = ". $user->id;
        $sql .= "      and vprc.arrow10 = 1 ";
        $sql .= " order by vprc.user_group_id asc ";

        $result = \DB::select($sql);

        $this->end_log();
        return $result;
    }

    /**
     * 請求登録履歴 施設一覧
     *
     * @param User $user ユーザ情報
     * @return UserGroups ユーザグループ情報
     */
    public function getUserGroupClaimRegist($user) {
        $sql  = " select ";
        $sql .= "      vprc.user_group_id as id ";
        $sql .= "     ,facility.name as name ";
        $sql .= " from ";
        $sql .= "      f_privileges_row_claim_regist(". $user->id .") as vprc ";
        $sql .= "      inner join user_groups as facility ";
        $sql .= "       on facility.id = vprc.user_group_id ";
        $sql .= " where ";
        $sql .= "      vprc.user_id = ". $user->id;
        $sql .= "      and vprc.show_claim_regist_rows = 1 ";
        $sql .= " order by vprc.user_group_id asc ";

        return $this->simple_execute_sql($sql);
    }

    /**
     * 請求照会 施設一覧
     *
     * @param User $user ユーザ情報
     * @return UserGroups ユーザグループ情報
     */
    public function getUserGroupClaim($user) {
        $sql  = "";
        $sql .= " select ";
        $sql .= "   distinct vprc.sales_user_group_id AS id ";
        $sql .= "   ,facility.name AS name ";
        $sql .= " from f_privileges_row_claim_check(". $user->id . ") AS vprc ";
        $sql .= " inner join user_groups AS facility ";
        $sql .= "  on facility.id = vprc.sales_user_group_id ";
        $sql .= " where ";
        $sql .= "      vprc.user_id = ". $user->id;
        $sql .= "      and vprc.show_claim_check_rows  = 1 ";
        $sql .= " order by vprc.sales_user_group_id asc ";

        return $this->simple_execute_sql($sql);
    }

    /**
     * 本部配下の病院取得
     *
     * @param  $use_grou_id 本部のグループID
     * @return UserGroups ユーザグループ情報
     */
    public function getHonbuHospital(int $user_group_id = null) {
        $this->start_log();
        $id = empty($user_group_id) ? $this->id : $user_group_id;

        $sql = "
            select
               *
            from
               user_groups
            where id in (
               select
                  user_group_id
               from
                  group_relations
               where
                  user_group_id in (
                    select
                       user_group_id
                    from
                       group_relations
                    where
                       partner_user_group_id = {$id}
                       and deleted_at is null
               )
               and deleted_at is null
        )
        and deleted_at is null";

        $rec = \DB::select($sql);
        $result = UserGroup::query()->hydrate($rec);

        $this->end_log();
        return $result;
    }

    /**
     * 所属するグループ取得
     *
     * @param int $user_group_id ユーザグループID
     * @return UserGroup ユーザグループ情報
     */
    public function getUserGroup($user_group_id) {
        $this->start_log();

        $result = $this
            ->query()
            ->where('id', $user_group_id)
            ->get();

        $this->end_log();
        return $result;
    }

    /**
     * 所属するグループ一覧取得2
     *
     * @param Request $request リクエスト情報
     * @param string $role_key ロールキー
     * @param int $user_group_id ユーザグループID
     * @return UserGroups ユーザグループ情報
     */
    public function getUserGroupListById(Request $request, string $role_key, int $user_group_id) {
        $this->start_log();

        // 文化連管理者、グループ管理者のみ
        if ($role_key !== \Config::get('const.user_attribute.ALL_AUTHORIZED') &&
            $role_key !== \Config::get('const.user_attribute.GROUP_AUTHORIZED')) {
            $result = $this
                ->select('id', 'name', 'formal_name', 'group_type', 'updated_at')
                ->where('id', -1)
                ->orderBy('disp_order');
            $this->end_log();
            return $result;
        }
        // ユーザグループ名称
        $name = $request->user_group_name ?? null;
        // グループ区分
        $group_type = $request->user_group_type ?? null;

        $user_group_ids = array();
        $myGroupType = $this->find($user_group_id)->group_type;
        if ($myGroupType === \Config::get('const.bunkaren_name')) {
            $user_group_ids = null;
        } elseif ($myGroupType === \Config::get('const.headquarters_name')) {
            //$user_group_ids = $this->children($user_group_id)->pluck('id');
            $user_group_ids = array($user_group_id);
        } else {
            $user_group_ids = array($user_group_id);
        }

        $result = $this
            ->select('user_groups.id', 'user_groups.name', 'user_groups.formal_name', 'user_groups.group_type', 'user_groups.updated_at')
            ->where(function ($query) use ($name) {
                if (!empty($name)) {
                    $query
                        ->where('user_groups.name', 'LIKE', "%$name%")
                        ->orWhere('user_groups.formal_name', 'LIKE', "%$name%");
                }
            })
            ->where(function ($query) use ($group_type) {
                if (!empty($group_type)) {
                    $query->where('user_groups.group_type', $group_type);
                }
            })
            ->where(function ($query) use ($user_group_ids) {
                if (!empty($user_group_ids)) {
                    $query->whereIn('user_groups.id', $user_group_ids);
                }
            })
            ->orderBy('user_groups.disp_order');

        $this->end_log();
        return $result;
    }


    //文化連のユーザグループID取得
    public function getBunkarenUserGroupId() {
        $result = $this->simple_execute_sql("select id from user_groups where group_type='文化連'");
        return $result[0]->id;
    }

    //グループIDのユーザ(id順1件)の本部idを取得
    public function getPrimaryHonbuId($user_group_id) {
        $sql  = " select ";
        $sql .= "     users.primary_honbu_user_group_id ";
        $sql .= " from ";
        $sql .= "     user_group_relations ";
        $sql .= "     inner join users ";
        $sql .= "         on user_group_relations.user_id = users.id ";
        $sql .= "         and users.deleted_at is null ";
        $sql .= " where ";
        $sql .= "     user_group_relations.user_group_id = ".$user_group_id;
        $sql .= "     and user_group_relations.deleted_at is null ";
        $sql .= "     and users.primary_honbu_user_group_id > 0 ";
        $sql .= " order by users.id limit 1";

        $result = $this->simple_execute_sql($sql);

        return count($result) == 0 ? null : $result[0];
    }

    /**
     * グループ区分よりユーザグループ一覧取得
     * @param string $group_type グループ区分情報
     * @return UserGroup モデル情報
     */
    public function getUserGroupByGroupType(string $group_type) {
        $this->start_log();

        $result = $this
            ->select('id','name')
            ->where('group_type', $group_type)
            ->orderBy('id')
            ->get();

        $this->end_log();
        return $result;
    }


    //紐づけされていない病院を取得
    public function nonJoinHosipital() {
        $this->start_log();

        $sql  =" select ";
        $sql .="     id ";
        $sql .="    ,name ";
        $sql .=" from ";
        $sql .="     user_groups ";
        $sql .=" where ";
        $sql .="     user_groups.group_type = ? ";
        $sql .="     and user_groups.deleted_at is null ";
        $sql .="     and user_groups.id not in ( ";
        $sql .="         select ";
        $sql .="             user_group_id ";
        $sql .="         from ";
        $sql .="             group_relations ";
        $sql .="         where ";
        $sql .="             deleted_at is null ";
        $sql .=" ) ";

        $params[] = \Config::get('const.hospital_name');

        $result = \DB::select($sql.' order by id', $params);

        $this->end_log();
        return $result;
    }
}
