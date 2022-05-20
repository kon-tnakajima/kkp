<?php

namespace App\Services;

use App\Model\Facility;
use App\Model\FacilityGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\BaseService;

class FacilityGroupService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->facilityGroup = new FacilityGroup();
    }
    /*
     * 施設グループ一覧取得
     */
    public function getFacilityGroupList(Request $request)
    {
        // 検索条件デバッグコード
        //$request->search = '小野薬品';

        // デバッグコードここまで

        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->facilityGroup->listWithFacilityGroup($request, $count);

        // リストにボタン情報付加
        if (!is_null($list)) {
            //foreach($list as $key => $apply) {
            //    $list[$key]->button = $this->button($apply);
            //}
        }
        return $list;
    }

    /*
     * 施設グループ詳細取得
     */
    public function getFacilityGroupDetail(Request $request)
    {
        $detail = $this->facilityGroup->find($request->id);
        return $detail;
    }

    /*
     * 施設グループ実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();
            $data = array();

            echo "create";
            $data['code'] = $request->code;
            $data['name'] = $request->name;
            $data['search'] = $request->code.$request->name; // 申請中からスタート
            $data['creater'] = $user->id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');

            $new_facility_group = $this->facilityGroup->create($data);

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
            $facilityGroup = $this->facilityGroup->find($request->id);
            if (is_null($facilityGroup)) {
                return false;
            }

            $facilityGroup->code = $request->code;
            $facilityGroup->name = $request->name;
            $facilityGroup->search = $request->code.$request->name;
            $facilityGroup->updater = $user->id;
            $facilityGroup->updated_at = date('Y-m-d H:i:s');
            $facilityGroup->save();

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
    public function getConditions(Request $request)
    {
        // 検索条件
        $conditions = array();
        $conditions['search'] = '';

        //検索実行の場合
        if (!empty($request->is_search)) {
            // 全文検索
            if (!empty($request->search)) {
                $conditions['search'] = $request->search;
            }

            session()->put(['facilitygroup_conditions' => $conditions]);
        }else{
            //リクエストに値を設定する。
            if( !empty(session()->get('facilitygroup_conditions')) ){
                $conditions = session()->get('facilitygroup_conditions');

                // 全文検索
                if (!empty( $conditions['search'] )) {
                    $request->search = $conditions['search'];
                }
            }
        }

        return $conditions;
    }
}
