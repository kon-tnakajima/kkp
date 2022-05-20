<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class ApplicationGroupRequest extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_group_id',
        'user_group_name',
        'name',
        'email',
        'status',
        'is_create',
        'remarks',
    ];

    /*
     * boot
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * グループ新規申請 user_group_relation
     */
    public function getGroupRequestRelation($status)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     a.user_id ";
    	$sql .= " from  ";
    	$sql .= " (select ";
    	$sql .= "      a.status_forward ";
    	$sql .= "     ,b.id as user_id ";
    	$sql .= "  from ";
    	$sql .= "     v_privileges_row_group_request_bystate as a ";
    	$sql .= "     inner join users as b ";
    	$sql .= "          on a.user_id = b.id";
    	$sql .= "          and b.deleted_at is null ";
    	$sql .= "  where ";
    	$sql .= "      a.status = ".$status;

    	$sql .= " ) as a ";
    	$sql .= " where  ";
    	$sql .= "     a.status_forward > 0 ";
    	$sql .= " group by  ";
    	$sql .= "     a.user_id ";


    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = array();
    	}

    	return $all_rec;

    }


    /*
     * グループ新規申請 メール送付先
     */
    public function getEmailGroupRequest($status)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     a.email ";
    	$sql .= " from  ";
    	$sql .= " (select ";
    	$sql .= "      a.status_forward ";
    	$sql .= "     ,b.email ";
    	$sql .= "  from ";
    	$sql .= "     v_privileges_row_groupjoin_request_bystate as a ";
    	$sql .= "     inner join users as b ";
    	$sql .= "          on a.user_id = b.id";
    	$sql .= "          and b.deleted_at is null ";
    	$sql .= "  where ";
    	$sql .= "      and a.user_group_id = ".$user_group_id;
    	$sql .= "      and a.status = ".$status;

    	$sql .= " ) as a ";
    	$sql .= " where  ";
    	$sql .= "     a.status_forward > 0 ";
    	if (env( 'ENV_TYPE' ) === 'staging') {
    		$sql .= " and (a.email like '%bunkaren.or.jp' ";
    		$sql .= "   or a.email like '%k-on.co.jp' ";
    		$sql .= "   or a.email like '%example.com') ";
    	}

    	$sql .= " group by  ";
    	$sql .= "     a.email ";


    	$all_rec = \DB::select($sql);

    	if (empty($all_rec)) {
    		$all_rec = array();
    	}

    	return $all_rec;

    }
}
