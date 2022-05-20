<?php

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupTraderRelation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_group_id',
        'trader_user_group_id',
        'deleter',
        'creater',
        'updater'
    ];
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());

    }

    /*
     * ユーザグループ該当する業者一覧を返却
     */
    public function getGroupTraderList($user_group_id)
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     ug.id ";
    	$sql .= "    ,ug.name ";
    	$sql .= " from  ";
    	$sql .= "     group_trader_relations as gtr  ";
    	$sql .= "     inner join user_groups as ug ";
    	$sql .= "         on gtr.trader_user_group_id = ug.id ";
    	$sql .= " where  ";
		$sql .= "     gtr.user_group_id = ".$user_group_id;
		$sql .= "     and gtr.trader_user_group_id <> 0 ";
    	$sql .= "     and gtr.deleted_at is null ";
    	$sql .= " order by  ";
    	$sql .= "     ug.id ";

    	$all_rec = \DB::select($sql);

    	$result = GroupTraderRelation::query()->hydrate($all_rec);

    	return $result;

    }

    /*
     * 当テーブルに登録されている業者一覧
     */
    public function getGroupTraderDistinctList()
    {
    	$sql  = "";
    	$sql .= " select ";
    	$sql .= "     ug.id ";
    	$sql .= "    ,ug.name ";
    	$sql .= " from  ";
    	$sql .= "     group_trader_relations as gtr  ";
    	$sql .= "     inner join user_groups as ug ";
    	$sql .= "         on gtr.trader_user_group_id = ug.id ";
    	$sql .= "         and ug.deleted_at is null ";
    	$sql .= " where  ";
    	$sql .= "     gtr.deleted_at is null ";
    	$sql .= " group by  ";
    	$sql .= "     ug.id ";
    	$sql .= "    ,ug.name ";
    	$sql .= " order by  ";
    	$sql .= "     ug.id ";

    	$all_rec = \DB::select($sql);

    	$result = GroupTraderRelation::query()->hydrate($all_rec);

    	return $result;

	}

	public function getGroupTraderListByUser($user) 
	{
		$sql  = " select ";
    	$sql .= " trader_user_group_id ";
		$sql .= " , max(trader_group_name) trader_group_name ";
		$sql .= " , max(trader_role_key_code) trader_role_key_code ";
		$sql .= " from ";
		$sql .= "      f_show_records(". $user->id .")";
    	$sql .= " where  ";
    	$sql .= "     trader_user_group_id <>0 ";
		$sql .= " group by ";
		$sql .= " 	trader_user_group_id ";
    	$sql .= " order by  ";
		$sql .= "     trader_user_group_id ";
		
		$all_rec = \DB::select($sql);

    	$result = GroupTraderRelation::query()->hydrate($all_rec);

        return $result;
	}

	
}
