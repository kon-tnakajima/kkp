<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Actor;
use App\Model\UserGroup;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Concerns\Facility as FacilityTrait;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;

class Facility extends Model
{
    use SoftDeletes;
    use FacilityTrait;
    protected $guraded = ['id'];
    protected $fillable = [
        'code',
        'name', 
        'formal_name', 
        'actor_id', 
        'zip', 
        'prefecture',
        'address', 
        'tel', 
        'fax', 
        'is_online',
        'deleter', 
        'creater', 
        'updater'
    ];

    const PREF_STR = [
        1 => '北海道',
        2 => '青森県',
        3 => '岩手県',
        4 => '宮城県',
        5 => '秋田県',
        6 => '山形県',
        7 => '福島県',
        8 => '茨城県',
        9 => '栃木県',
        10 => '群馬県',
        11 => '埼玉県',
        12 => '千葉県',
        13 => '東京都',
        14 => '神奈川県',
        15 => '新潟県',
        16 => '富山県',
        17 => '石川県',
        18 => '福井県',
        19 => '山梨県',
        20 => '長野県',
        21 => '岐阜県',
        22 => '静岡県',
        23 => '愛知県',
        24 => '三重県',
        25 => '滋賀県',
        26 => '京都府',
        27 => '大阪府',
        28 => '兵庫県',
        29 => '奈良県',
        30 => '和歌山県',
        31 => '鳥取県',
        32 => '島根県',
        33 => '岡山県',
        34 => '広島県',
        35 => '山口県',
        36 => '徳島県',
        37 => '香川県',
        38 => '愛媛県',
        39 => '高知県',
        40 => '福岡県',
        41 => '佐賀県',
        42 => '長崎県',
        43 => '熊本県',
        44 => '大分県',
        45 => '宮崎県',
        46 => '鹿児島県',
        47 => '沖縄県',
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
        return Actor::isBunkaren($this->actor_id);
    }

    /* 
     * 本部かどうか判定
     */
    public function isHeadQuqrters() {
        return Actor::isHeadQuqrters($this->actor_id);
    }

    /* 
     * 病院かどうか判定
     */
    public function isHospital() {
        return Actor::isHospital($this->actor_id);
    }

    /*
     * 本部の配下の施設(病院)を取得
     */
    public function children() {
        return $this->belongsToMany('App\Model\Facility', 'facility_relations', 'parent_facility_id', 'facility_id');
    }

    /*
     * 施設(病院)が所属する本部を取得
     */
    public function parent() {
       return $this->belongsToMany('App\Model\Facility', 'facility_relations', 'facility_id', 'parent_facility_id')->first();
    }

    /*
     * 所属するユーザ取得
     */
    public function users() {
        return $this->hasMany('App\User');
    }

    /*
     * scope 施設のみ
     */
    public function scopeHospital($query)
    {
        $query->where('actor_id', Actor::ACTOR_HOSPITAL);
        return $query;
    }

    /*
     * ユーザーグループ
     */
    public function userGroups()
    {
        return $this->hasMany('App\Model\UserGroup');
    }
    /*
     * scope 文化連のみ
     */
    public function scopeBunkaren($query)
    {
        $query->where('actor_id', Actor::ACTOR_BUNKAREN);
        return $query;
    }

    /*
     * 承認申請一覧
     */
    public function listWithFacility(Request $request, $count)
    {
        //サブクエリ
        //ユーザーグループ数
        $sub =  \DB::table('user_groups')
            ->select( \DB::raw('facility_id,count(user_groups.id) as cnt'))
            ->groupBy('facility_id');
        //ユーザー数
        $sub2 =  \DB::table('users')
            ->select( \DB::raw('facility_id,count(users.id) as cnt'))
            ->groupBy('facility_id');

        $query = $this->select('facilities.id' 
                                ,'facilities.code'
                                ,'facilities.name'
                                ,'facilities.address'
                                ,'facility_groups.name as group'
                                ,'group_cnt.cnt as user_group_cnt'
                                ,'user_cnt.cnt as user_cnt'
                                ,'actors.name as actor_name'
                                ,'facilities.updated_at'
                )
                ->leftJoin( \DB::raw('('. $sub->toSql() .') as group_cnt'), 'facilities.id', '=', 'group_cnt.facility_id')
                //サブクエリのWHERE句の値をバインド
                ->mergeBindings($sub)

                ->leftJoin( \DB::raw('('. $sub2->toSql() .') as user_cnt'), 'facilities.id', '=', 'user_cnt.facility_id')
                //サブクエリのWHERE句の値をバインド
                ->mergeBindings($sub2)

                ->leftJoin('facility_groups', 'facility_groups.id', '=', 'facilities.facility_group_id')
                ->leftJoin('actors', 'actors.id', '=', 'facilities.actor_id');
                
        // 検索条件
        // 全文検索
        if (!empty($request->search)) {
            $query->where('facilities.search', 'like', '%'.$request->search.'%');
        }
        // 施設グループ
        if (!empty($request->facility_group)) {
            $query->where('facilities.facility_group_id', $request->facility_group);
        }
        // アクター
        if (!empty($request->actor)) {
            $query->where('facilities.actor_id', $request->actor);
        }
        // 都道府県
        if (!empty($request->facility_prefecture)) {
            $query->where('facilities.prefecture', $request->facility_prefecture);
        }

        return $query->paginate($count);
    }
}
