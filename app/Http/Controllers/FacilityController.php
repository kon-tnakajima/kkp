<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\FacilityService;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;

class FacilityController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 20;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->service = new FacilityService();
    }
    /*
     * 施設一覧
     */
    public function index(Request $request)
    {
        $conditions = $this->service->getConditions($request);
        $list = $this->service->getFacilityList($request);

        $page_count = self::PAGER_DISPLAY_COUNT;
        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        }

        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'page_count' => $page_count,
            'actors' => $this->service->getActors(),
            'facility_groups' => $this->service->getFacilityGroups(),
            'conditions' => $conditions,
            'prefs' => $this->service->getPrefs(),
            'pager' => $this->getPager($list, $page_count)
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
    
    /*
     * 施設詳細
     */
    public function detail(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getFacilityDetail($request),
        'relation' => $this->service->getFacilityRelation($request),
         'errorMessage' => $request->session()->get('errorMessage', ''),
         'facility_groups' => $this->service->getFacilityGroups(),
         'prefs' => $this->service->getPrefs(),
         'facilities' => $this->service->getFacilities(),
         'message' => $request->session()->get('message', ''),
        ]);
    }
    
    /*
     * 施設新規登録
     */
    public function regist(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['errorMessage' => $request->session()->get('errorMessage', ''),
        'prefs' => $this->service->getPrefs(),
        'facility_groups' => $this->service->getFacilityGroups(),
        'facilities' => $this->service->getFacilities(),
        'message' => $request->session()->get('message', ''),
        ]);
    }

    /*
     * 施設修正
     */
    public function edit(Request $request)
    {
        // TODO
        // この情報は誰が修正できるのか確認(施設 or 本部 or 文化連)

        // TODO バリデーション
        $request->validate([
            'code' => 'required',
            'name' => 'required',
        ]);

        // 実行
        if (!$this->service->edit($request)) {
            return false;
        }
        return redirect()->route('facility.detail', ['id' => $request->id]);
    }

    /*
     * 施設実行
     */
    public function regexec(Request $request)
    {
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }
        return redirect()->route('facility.index');
    }

}
