<?php

namespace App;

use Illuminate\Support\Facades\Schema;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\UserGroup;
use App\Model\UserGroupRelation;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;
use App\Notifications\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmail, Notifiable;
    use SoftDeletes;
    const VIEW_PRIVILEGES_MENU_TABLE_NAME = 'v_privileges_menus';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'facility_id','sub_id','last_login_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());

    }

    /**
     * シーケンスIDを取得
     */
    public function getUserId()
    {
		$result = \DB::select("select nextval('users_id_seq') as nextid");
		return $result[0]->nextid;
	}

    public function joinRole()
    {
        $user = $this->joinGroup();
        $user = $user->join('roles', 'roles.user_group_id', '=', 'user_groups.id');
        return $user;
    }

    public function joinGroup()
    {
        $user = $this->join('user_group_relations', 'users.id', '=', 'user_group_relations.user_id');
        $user = $user->join('user_groups', 'user_group_relations.user_group_id', '=', 'user_groups.id');
        return $user;
    }

    /**
     * ユーザから見た管理者IDを取得
     */
    public function admin()
    {
        return \DB::select("select user_id as id from user_group_relations where user_group_id={$this->primary_user_group_id} and role_key_code in ('グループ管理者','システム管理者','文化連管理者','本部管理者')");
    }

    /*
     * 所属するグループを取得
     */
    public function userGroup()
    {
        return UserGroup::query()->where('id', $this->primary_user_group_id)->first();
        // return $this->belongsToMany('App\Model\UserGroup', 'user_group_relations')->withTimestamps();
    }



    /*
     * ユーザーの権限を取得
     */
    public function userGroupRelation()
    {
    	return UserGroupRelation::query()->where('user_id', $this->id)->first();
    }


    /*
     * メニュー権限用ロールを取得
     */
    public function menuUserRole()
    {
    	$targetUserGroupId = $this->primary_user_group_id;
    	$res = \DB::select("select role_key_code from user_group_relations where user_id=? and user_group_id=?", [$this->id, $targetUserGroupId]);

    	return $res[0];
    }

    /*
     * 施設を取得
     */
    // public function facility()
    // {
    //     return $this->belongsTo('App\Model\Facility');
    // }

    /*
     * ユーザー一覧
     */
    public function getUserList(Request $request, $count, $user)
    {
        $user_name    = !empty($request->user_name) ? "%{$request->user_name}%" : null;
        $user_email   = !empty($request->user_email) ? "%{$request->user_email}%" : null;
        $user_sub_id  = !empty($request->user_sub_id) ? "%{$request->user_sub_id}%" : null;
        $userGroupIds = $this->getUserListAttr($user);
        return $this->withTrashed()->select(
                'users.id as id',
                'users.name',
                'users.email',
                'users.sub_id',
                'users.deleter',
                'users.updated_at as updated_at',
                'update_user.name as updater_name'
            )
            ->leftJoin('users as update_user','users.updater','update_user.id')
            ->where(function ($query) use ($userGroupIds) {
                if (!empty($userGroupIds[0])) {
                    $query->whereIn('users.primary_user_group_id', $userGroupIds[0]);
                }
            })
            ->where(function ($query) use ($userGroupIds) {
                if (!empty($userGroupIds[1])) {
                    $query->whereIn('users.id', $userGroupIds[1]);
                }
            })
            ->where(function ($query) use ($user_name){
                // ユーザ名
                if ($user_name) {
                    $query->where('users.name', 'LIKE', $user_name);
                }
            })
            ->where(function ($query) use ($user_email){
                // メールアドレス
                if ($user_email) {
                    $query->where('users.email', 'LIKE', $user_email);
                }
            })
            ->where(function ($query) use ($user_sub_id){
                // サブID
                if ($user_sub_id) {
                    $query->where('users.sub_id', 'LIKE', $user_sub_id);
                }
            })
            ->orderBy('users.id')
            ->paginate($count);
    }

    /*
     * scope 承認メールがtrue
     */
    public function scopeisAdoptionMail($query)
    {
        $query->where('is_adoption_mail', true);
        return $query;
    }

    /*
     * scope 請求メールがtrue
     */
    public function scopeisClaimMail($query)
    {
        $query->where('is_claim_mail', true);
        return $query;
    }

    /*
     * パスワードリセット
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }

    /*
     * メールアドレスで全情報をユーザから取得する
     */
    public function getUserByMailAddress(string $email, int $count)
    {
        return $this->select(
                'id', 'sub_id', 'primary_user_group_id as user_group_id', 'email_verified_at')
            ->where('email', $email)
            ->orderBy('email_verified_at', 'asc')
            ->orderBy('user_group_id', 'desc')
            ->paginate($count);
    }

    /*
     * ユーザIDと所属グループIDで権限情報取得
     */
    public function getSideBarPrivilege()
    {
        $user = Auth::user();
    	$sql = 'select * from '.self::VIEW_PRIVILEGES_MENU_TABLE_NAME.' where user_id=? and user_group_id=?';
    	$result = \DB::select($sql,[$user->id, $user->primary_user_group_id]);
        if (empty($result)) {
            // デフォルトにする。
            $tempColumns = Schema::getColumnListing(self::VIEW_PRIVILEGES_MENU_TABLE_NAME);
            $result = [];
            foreach ($tempColumns as $name) {
                // $result[$name] = 'display:none;';
                $result[$name] = '';
            }
            $result['user_group_id'] = $user->primary_user_group_id;
            $result['user_id']       = $user->id;
            return $result;
        }
        // 配列へ変更
        return json_decode(json_encode($result), true)[0];
    }

    /**
     * ユーザの「文化連管理者」、「グループ管理者」対象のユーザグループID取得
     *
     * @param User $user ユーザ情報
     */
    public function getUserListAttr($user)
    {
        $targetUserGroupId = $user->primary_user_group_id;
        $ret = [[ $targetUserGroupId ],[]];
        $res = \DB::select("select role_key_code from user_group_relations where user_id=? and user_group_id=?", [$user->id, $targetUserGroupId]);
        // グループ管理者
        if ($res[0]->role_key_code === \Config::get('const.user_attribute.GROUP_AUTHORIZED') ||
            $res[0]->role_key_code === \Config::get('const.user_attribute.HEADGUARTERS_AUTHORIZED')) {
            $userGroup = new UserGroup();
            $parent = $userGroup->find($targetUserGroupId)->parent();
            if (!empty($parent[0])) {
                // 親と認証自身のユーザグループIDが一致した場合は、所有者としてグループ内のユーザグループを取得する
                $isParent = false;
                foreach($parent as $value) {
                    if ($value->id === $targetUserGroupId) {
                        $isParent = true;
                        break;
                    }
                }
                if ($isParent) {
                    // 対象の子供取得
                    $children = $userGroup->find($targetUserGroupId)->children();
                    if (!empty($children[0])) {
                        // 一致
                        $ret[0] = $children->pluck('id');
                    }
                } else {
                    // 親と自身不一致
                    $ret = [[],[$user->id]];
                }
            }
        } elseif ($res[0]->role_key_code === \Config::get('const.user_attribute.ALL_AUTHORIZED')) {
            $ret = [[],[]];
        } else {
            // 一般ユーザと認識
            $ret = [[],[$user->id]];
        }
        return $ret;
    }

    /**
     * 単一の病院を取得
     * @param int $user_group_id 認証してるユーザグループID
     * @param string $name ユーザグループ名
     *
     */
    public function getSingleHospitals(int $user_group_id = null, string $name = null)
    {

    	// 対象のuser_group_idで検索
    	if (!empty($user_group_id)) {
    		$sql = 'SELECT
                   id, name
                FROM
                   user_groups
                WHERE
                   id IN (
                       SELECT
                          group_relations.user_group_id
                       FROM  group_relations
                       inner join user_groups
                          on user_groups.id = group_relations.user_group_id
                          and user_groups.group_type = ?
                          and user_groups.deleted_at is NULL
                       WHERE  group_relations.user_group_id = ?
                          and group_relations.partner_user_group_id = 1)';
            $params[] = \Config::get('const.hospital_name');
            $params[] = $user_group_id;

    	} else {
           // 紐づけが未設定の病院一覧を取得する
           $sql = 'SELECT
                   id, name
                FROM
                   user_groups
                WHERE
                   id IN (
                       SELECT
                          group_relations.user_group_id
                       FROM  group_relations
                       inner join user_groups
                          on user_groups.id = group_relations.user_group_id
                          and user_groups.group_type = ?
                          and user_groups.deleted_at is NULL
                       WHERE group_relations.partner_user_group_id = 1)';
            $params[] = \Config::get('const.hospital_name');
    	}

        /*
         $sql = 'select
                    id, name
                from
                    user_groups
                where
                    id in (
                        select
                            group_relations.user_group_id
                        from
                            group_relations
                            inner join user_groups
                                on user_groups.id = group_relations.user_group_id
                                and user_groups.group_type = ?
                                and user_groups.deleted_at is null
                        where
                            group_relations.partner_user_group_id = group_relations.user_group_id
                            and group_relations.deleted_at is null
                        group by
                            group_relations.user_group_id ';
        $params[] = \Config::get('const.hospital_name');
        // ユーザグループIDがあればこのIDもunionで取得
        if (!empty($user_group_id)) {
            $sql .= '
                union
                    select
                        group_relations.user_group_id
                    from
                        group_relations
                        inner join user_groups
                            on user_groups.id = group_relations.user_group_id
                            and user_groups.group_type = ?
                            and user_groups.deleted_at is null
                        where
                            group_relations.partner_user_group_id = ?
                            and group_relations.deleted_at is null
                        group by
                            group_relations.user_group_id ';
            $params[] = \Config::get('const.hospital_name');
            $params[] = $user_group_id;
        }
        $sql .= ') ';
        */
        if (!empty($name)) {
            $sql .= "and name like '%{$name}%' ";
        }
        return \DB::select($sql.' order by id', $params);
    }

    /**
     * ユーザの優先本部ユーザグループIDをユーザグループIDを軸に更新
     * @param int $id ユーザID（更新対象から除く）
     * @param int $partner_id 本部又は病院ユーザグループID
     * @param int $user_group_id 対象ユーザグループID
     * @return void
     */
    public function setPrimaryHeadquarter(int $id, int $partner_id, int $user_group_id)
    {
    	$dt = Carbon::now();
    	$sql = 'update users
                set
                    primary_honbu_user_group_id = ?,
                    updater = ?,
                    updated_at = ?
                from
                    user_group_relations
                where
                    user_group_relations.user_group_id = ?
                    and users.primary_user_group_id = ?
                    and users.id != ?
                    and user_group_relations.deleted_at is null';
    	\DB::update($sql, [$partner_id, $id, $dt, $user_group_id, $user_group_id, $id]);
    	return;
    }

	public function getMasterUsers(){
		$sql  = "";
		$sql .= " select distinct";
		$sql .= "     users.id  ";
		$sql .= " from ";
		$sql .= "     user_group_relations ";
		$sql .= "     inner join user_groups ";
		$sql .= "         on user_group_relations.user_group_id=user_groups.id ";
		$sql .= "         and user_groups.group_type = '文化連' ";
		$sql .= "         and user_groups.deleted_at is null ";
		$sql .= "     inner join users ";
		$sql .= "         on user_group_relations.user_id = users.id ";
		$sql .= "         and users.deleted_at is null ";
		$sql .= " where ";
		$sql .= "     user_group_relations.deleted_at is null ";

		$all_rec = \DB::select($sql);

		return $all_rec;

	}

    /*
     * ユーザー権限一覧取得
     */
    public function getUserKengen($user_id)
    {
        $sql  = "";
        $sql .= " select ";
        $sql .= "  user_id, privilege_key_code, privilege_value, message, action_string ";
        $sql .= " from f_user_unique_privileges(" . $user_id .")";

        $all_rec = \DB::select($sql);
        return array_column($all_rec, null, 'privilege_key_code');
    }


    /**
     * primary_user_group_idを更新する
     *
     * @param [type] $primaryUserGroupId
     * @return void
     */
    public function updatePrimaryUserGroupId($primaryUserGroupId) {
        $user_id = Auth::user()["id"];

        $sql  = "";
        $sql .= " update users set ";
        $sql .= "     primary_user_group_id =" . $primaryUserGroupId ;
        $sql .= "     ,updater =" . $user_id ;
        $sql .= "     ,updated_at = now() ";
        $sql .= " from user_group_relations ugr ";
        $sql .= " where ";
        $sql .= "     users.id =" . $user_id ;;
        $sql .= "     and ugr.user_id = users.id ";
        $sql .= "     and ugr.user_group_id =" . $primaryUserGroupId ;
        $sql .= "     and users.deleted_at is null ";
        $sql .= "     and ugr.deleted_at is null ";
            
        // \DB::update($sql, [$primaryUserGroupId, $user_id, $dt, $user_id]);
        \DB::update($sql);
    }
    
    /*
     * 所属しているユーザーグループ一覧取得
     */
    public function getSelectableUserGroup()
    {
        $user_id = Auth::user()["id"];

        $sql  = "";
        $sql .= " select ";
        $sql .= " user_id, privilege_key_code, privilege_value ";
        $sql .= " from f_user_unique_privileges(" . $user_id .")";
        $sql .= " where privilege_key_code like '医薬品採用申請_文化連枠表示' ";

        $bunkaren_kengen_rec = \DB::select($sql);

        //文化連権限がなければ、ログインユーザーのprimary_user_groupを取得する
        if($bunkaren_kengen_rec[0]->privilege_value == 'false'){
            $sql  = "";
            $sql .= " select ";
            $sql .= "     ugr.user_group_id ";
            $sql .= "     , ug.name user_group_name ";
            $sql .= "     , true is_set_as_primary ";
            $sql .= " from ";
            $sql .= "     user_group_relations ugr ";
            $sql .= "     inner join users u ";
            $sql .= "         on ugr.user_id = u.id ";
            $sql .= "     left join user_groups ug ";
            $sql .= "         on ugr.user_group_id = ug.id ";
            $sql .= " where ";
            $sql .= "     ugr.user_id =" . $user_id ;
            $sql .= "     and ugr.user_group_id = u.primary_user_group_id ";

            $all_rec = \DB::select($sql);
        }else{
            //文化連権限があれば、ログインユーザーが所属するグループ一覧を取得する
            $sql  = "";
            $sql .= " select ";
            $sql .= "     ugr.user_group_id ";
            $sql .= "     ,ug.name user_group_name ";
            $sql .= "     ,case when ugr.user_group_id = u.primary_user_group_id then true else false end is_set_as_primary ";
            $sql .= " from user_group_relations ugr ";
            $sql .= " inner join users u ";
            $sql .= "     on ugr.user_id = u.id ";
            $sql .= " left join user_groups ug ";
            $sql .= "     on ugr.user_group_id = ug.id ";
            $sql .= " where ugr.user_id =" . $user_id ;
            $sql .= "     and ugr.deleted_at is null ";
            $sql .= " order by ug.disp_order ";

           $all_rec = \DB::select($sql);
        }
        return $all_rec;

    }

}
