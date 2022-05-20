<?php

namespace App\Services;

use App\Model\Actor;
use App\Model\Facility;
use App\Model\FacilityRelation;
use App\Model\FacilityGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\BaseService;

class FacilityService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->actor = new Actor();
        $this->facility = new Facility();
        $this->facility_relation = new FacilityRelation();
        $this->facility_group = new FacilityGroup();
    }
    /*
     * 施設一覧取得
     */
    public function getFacilityList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->facility->listWithFacility($request, $count);

        return $list;
    }

    /*
     * 施設詳細取得
     */
    public function getFacilityDetail(Request $request)
    {
        $detail = $this->facility->find($request->id);
        return $detail;
    }

    /*
     * 施設関連データ取得
     */
    public function getFacilityRelation(Request $request)
    {
        $relation = $this->facility_relation->where('facility_id', $request->id)->first();
        return $relation;
    }

    /*
     * 施設実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $data = array();

            //施設データ
            $data['code'] = $request->code;
            $data['name'] = $request->name;
            $data['formal_name'] = $request->formal_name;
            if( empty($request->facility_group_id) ){
                $data['facility_group_id'] = 0;
            }else{
                $data['facility_group_id'] = $request->facility_group_id;
            }
            $data['actor_id'] = $request->actor_id;
            $data['zip'] = $request->zip;

            $data['prefecture'] = $request->prefecture;
            $data['address'] = $request->address;
            $data['tel'] = $request->tel;
            $data['fax'] = $request->fax;
            $data['is_online'] = $request->is_online;
            $data['creater'] = $user->id;
            $data['updater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $data['search'] = $request->code
                                .$request->name
                                .$request->formal_name
                                .$request->zip
                                .$request->address
                                .$request->tel
                                .$request->prefecture
                                .$request->fax
                                .$user->name;

            $new_facility = $this->facility->create($data);

            $data = array();

            //施設データ
            $data['facility_id'] = $new_facility->id;
            $data['parent_facility_id'] = $request->parent_facility_id;
            $data['creater'] = $user->id;
            $data['updater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $this->facility_relation->create($data);

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            exit;
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $Facility = $this->facility->find($request->id);
            if (is_null($Facility)) {
                return false;
            }

            $Facility->code = $request->code;
            $Facility->name = $request->name;
            $Facility->formal_name = $request->formal_name;
            $Facility->actor_id = $request->actor_id;
            if( empty($request->facility_group_id) ){
                $Facility->facility_group_id = 0;
            }else{
                $Facility->facility_group_id = $request->facility_group_id;
            }
            $Facility->zip = $request->zip;
            $Facility->prefecture = $request->prefecture;
            $Facility->address = $request->address;
            $Facility->tel = $request->tel;
            $Facility->fax = $request->fax;
            $Facility->is_online = $request->is_online;
            $Facility->updater = $user->id;
            $Facility->updated_at = date('Y-m-d H:i:s');

            $Facility->search = $request->code
                                .$request->name
                                .$request->formal_name
                                .$request->prefecture
                                .$request->zip
                                .$request->address
                                .$request->tel
                                .$request->fax
                                .$user->name;


            $Facility->save();

            $FacilityRelation = $this->getFacilityRelation($request);
            if( !empty($FacilityRelation) ){
                if( empty($request->parent_facility_id) ){
                    $FacilityRelation->parent_facility_id = 0;
                }else{
                    $FacilityRelation->parent_facility_id = $request->parent_facility_id;
                }
                $FacilityRelation->updater = $user->id;
                $FacilityRelation->updated_at = date('Y-m-d H:i:s');
                $FacilityRelation->save();
            }else{
                $data = array();

                //施設データ
                $data['facility_id'] = $Facility->id;
                if( empty($request->parent_facility_id) ){
                    $data['parent_facility_id'] = 0;
                }else{
                    $data['parent_facility_id'] = $request->parent_facility_id;
                }
                $data['creater'] = $user->id;
                $data['updater'] = $user->id;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');

                $this->facility_relation->create($data);
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

    /*
     * 登録・詳細の施設一覧
     */
    public function getFacilities()
    {
        return $this->facility->all();
    }

    /*
     * 検索フォームのアクター一覧
     */
    public function getActors()
    {
        return $this->actor->all();
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function getFacilityGroups()
    {
        return $this->facility_group->all();
    }

    /*
     * 都道府県一覧
     */
    public function getPrefs()
    {
        return Facility::PREF_STR;
    }

    /*
     * 登録・詳細の施設一覧
     */
    public function getConditions(Request $request)
    {
        // 検索条件
        $conditions = array();
        $conditions['search'] = '';
        $conditions['facility_group'] = '';
        $conditions['actor'] = '';
        $conditions['facility_prefecture'] = '';

        //検索実行の場合
        if (!empty($request->is_search)) {
            // 全文検索
            if (!empty($request->search)) {
                $conditions['search'] = $request->search;
            }
            // 施設グループ
            if (!empty($request->facility_group)) {
                $conditions['facility_group'] = $request->facility_group;
            }
            // アクター
            if (!empty($request->actor)) {
                $conditions['actor'] = $request->actor;
            }
            // 都道府県
            if (!empty($request->facility_prefecture)) {
                $conditions['facility_prefecture'] = $request->facility_prefecture;
            }

            session()->put(['facility_conditions' => $conditions]);
        }else{
            //リクエストに値を設定する。
            if( !empty(session()->get('facility_conditions')) ){
                $conditions = session()->get('facility_conditions');

                // 全文検索
                if (!empty( $conditions['search'] )) {
                    $request->search = $conditions['search'];
                }
                // 施設グループ
                if (!empty( $conditions['facility_group'] )) {
                    $request->facility_group = $conditions['facility_group'];
                }
                // アクター
                if (!empty( $conditions['actor'] )) {
                    $request->actor = $conditions['actor'];
                }
                // 都道府県
                if (!empty( $conditions['facility_prefecture'] )) {
                    $request->facility_prefecture = $conditions['facility_prefecture'];
                }
            }
        }

        return $conditions;
    }
}
