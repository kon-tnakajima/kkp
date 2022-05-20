<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\FacilityGroupService;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;

class FacilityGroupController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 20;
    /* ビジネスロジックのオブジェクト */
    private $facilityService;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->service = new FacilityGroupService();
    }
    /*
     * 施設グループ一覧
     */
    public function index(Request $request)
    {
        $conditions = $this->service->getConditions($request);
        $list = $this->service->getFacilityGroupList($request);

        $page_count = self::PAGER_DISPLAY_COUNT;
        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        }

        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'page_count' => $page_count,
            'conditions' => $conditions,
            'pager' => $this->getPager($list, $page_count)
        ]);
        
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
    
    /*
     * 施設グループ詳細
     */
    public function detail(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getFacilityGroupDetail($request),
         'errorMessage' => $request->session()->get('errorMessage', ''),
         'message' => $request->session()->get('message', ''),
        ]);
    }
    
    /*
     * 施設グループ詳細
     */
    public function regist(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['errorMessage' => $request->session()->get('errorMessage', ''),
         'message' => $request->session()->get('message', ''),
        ]);
    }

    /*
     * 施設グループ修正
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
        return redirect()->route('facility.group.detail', ['id' => $request->id]);
    }

    /*
     * 施設グループ実行
     */
    public function regexec(Request $request)
    {
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }
        return redirect()->route('facility.group.index');
    }

}
