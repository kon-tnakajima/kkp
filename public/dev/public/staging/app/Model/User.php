<?php

namespace App;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Role;
use App\Model\UserGroup;
use App\Model\Facility;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;
use App\Notifications\PasswordReset;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmail, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'facility_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
                
    }

    /*
     * アクセス権限があるかどうか
     * @param int $function_id 機能ID
     * @return boolean
     */
    public function isAccessAllowed($function_id)  
    {
        $user = $this->joinRole();
        $user = $user->where('roles.function_id', '=', $function_id);
        $user->orderBy('roles.level', 'desc'); // 一番大きいlevelを取得

        $data = $user->first();
        if (!is_null($data) ) {
            if ($data->level === Role::LEVEL_ENABLE) {
                return true;
            }
        }
        return false;
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

    /*
     * 所属するグループを取得
     */
    public function userGroups() 
    {
        return $this->belongsToMany('App\Model\UserGroup', 'user_group_relations')->withTimestamps();
    }

    /*
     * 施設を取得
     */
    public function facility()
    {
        return $this->belongsTo('App\Model\Facility');
    }

    /*
     * アクタIDを取得
     */
    public function getActorID()
    {
        return $this->userGroups()->first()->facility->actor_id;
    }

    /*
     * ユーザー一覧
     */
    public function getUserList(Request $request, Facility $facility, $count)
    {

        $query = $this->joinGroup();
        $query->select('users.id as id'
                            , 'users.name as name'
                            , 'users.email'
                            , 'facilities.name as facility_name'
                            , 'user_groups.name as user_group_name'
                            , 'users.updated_at as updated_at'
                            , 'update_user.name as updater_name'
        );

        $query->leftJoin('facilities', 'facilities.id', 'users.facility_id');
        $query->leftJoin('users as update_user', 'users.updater', 'update_user.id');

        // 所属している施設のユーザーのみ
        $query->where('users.facility_id', $facility->id);

        // 検索条件
        if (!empty($request->user_group_id)) {
            $query->where('user_groups.id', $request->user_group_id);
        }
        $query->orderBy('id', 'desc');
        return $query->paginate($count);
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
     * パスワードリセット
     */    
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new PasswordReset($token));
    }
}
