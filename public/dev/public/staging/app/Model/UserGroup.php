<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class UserGroup extends Model
{
    use SoftDeletes;

    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
                
    }

    /*
     * グループに所属しているユーザー取得
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_group_relations');
    }

    /*
     * グループに紐付いているロールの取得
     */
    public function role()
    {
        return $this->hasOne('App\Model\Role');
    }

    /* 
     * グループに紐付いている施設の取得
     */
    public function facility()
    {
        return $this->belongsTo('App\Model\Facility');
    }

    /*
     * ユーザが文化連かどうか
     */
    public function isBunkaren() 
    {
        return ($this->facility->actor_id === Actor::ACTOR_BUNKAREN);
    }
    /*
     * ユーザが本部かどうか
     */
    public function isHeadQuqrters() 
    {
        return ($this->facility->actor_id === Actor::ACTOR_HEADQUARTERS);
    }
    /*
     * ユーザが病院かどうか
     */
    public function isHospital() 
    {
        return ($this->facility->actor_id === Actor::ACTOR_HOSPITAL);
    }
}
