<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Actor;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;

class FacilityGroup extends Model
{
    use SoftDeletes;
    protected $guraded = ['id'];
    protected $fillable = [
        'code',
        'name', 
        'search',
        'deleter', 
        'creater', 
        'updater'
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
     * 文化連かどうか判定
     */
    public function isBunkaren() {
        $user = Auth::user();
        return Actor::isBunkaren($user->actor_id);
    }

    /*
     * 承認申請一覧
     */
    public function listWithFacilityGroup(Request $request, $count)
    {
        //ユーザーグループ数
        $sub =  \DB::table('facilities')
            ->select( \DB::raw('facility_group_id,count(facilities.id) as cnt'))
            ->groupBy('facility_group_id');

        $query = $this->select('facility_groups.id' 
                                ,'facility_groups.code'
                                ,'facility_groups.name'
                              ,'f_cnt.cnt as cnt'
                              ,'facility_groups.updated_at'
                             )
                ->leftJoin( \DB::raw('('. $sub->toSql() .') as f_cnt'), 'facility_groups.id', '=', 'f_cnt.facility_group_id')
                //サブクエリのWHERE句の値をバインド
                ->mergeBindings($sub);
                
        // 検索条件
        // 全文検索
        if (!empty($request->search)) {
            $query->where('facility_groups.search', 'like', '%'.$request->search.'%');
        }
        return $query->paginate($count);
    }

}
