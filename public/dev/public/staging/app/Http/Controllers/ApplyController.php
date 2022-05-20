<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller as BaseController;
use App\Services\ApplyService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;

class ApplyController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
    /* ビジネスロジックのオブジェクト */
    private $applyService;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new ApplyService();
    }
    /*
     * 採用申請一覧
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $conditions = $this->service->getConditions($request);

        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('apply_count')) || $request->initial) ? ApplyService::DEFAULT_PAGE_COUNT : session()->get('apply_count');
            $request->page_count = $page_count;
        }
        $list = $this->service->getApplyList($request, $user);

        session()->put('apply_count', $page_count);
        $set_default = $this->service->isInitial($request);

        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'page_count' => $page_count,
            'conditions' => $conditions,
            'set_facility' => $user->facility->id,
            'set_default' => $set_default,
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'facilities' => $this->service->getFacilities($user),
            'tasks' => $this->service->getTasks(),
            'message' => $request->session()->get('message', ''),
            'errorMessage' => $request->session()->get('errorMessage', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
    
    /*
     * 採用申請
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'page' => $request->page,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
            'maker_list' => $this->service->maker->all(),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
    
    /*
     * 採用申請
     */
    public function addexec(Request $request)
    {
        $request->validate([
            'jan_code' => 'required',
            'medicine_price' => 'nullable|numeric',
            'purchase_price' => 'numeric',
            'sales_price' => 'nullable|numeric',
        ]);

        $user = Auth::user();
        if (!$this->service->add($request, $user)) {
            return redirect()->route('apply.add')->withInput();
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 薬品登録再申請
     */
    public function reentry(Request $request)
    {
        $user = Auth::user();
        $this->service->reentry($request, $user);
        return redirect()->route('apply.index', ['page' => $request->page]);
    }
    

    /*
     * 採用申請詳細
     */
    public function detail(Request $request)
    {
        $user = Auth::user();
        $list = $this->service->getApplyList2($request, $user);

        $page_count = ApplyService::DEFAULT_PAGE_COUNT;
        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        }
        
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getApplyDetail($request),
        'page_count' => $page_count,
        'list' => (empty($list)) ? array() : $list,
        'sellers' => $this->service->getTraders(),
        'pager' => (empty($list)) ? array() : $this->getPager($list, self::PAGER_DISPLAY_COUNT),
        'errorMessage' => $request->session()->get('errorMessage', ''),
        'message' => $request->session()->get('message', ''),
        ]);
    }

    /*
     * 採用申請修正
     */
    public function edit(Request $request)
    {
        // TODO
        // この情報は誰が修正できるのか確認(施設 or 本部 or 文化連)

        // TODO バリデーション
        $request->validate([
//            'purchase_price' => 'required|numeric',
//            'sales_price' => 'required|numeric',
        ]);

        // 実行
        $this->service->edit($request);
        return redirect()->route('apply.detail', ['id' => $request->id,'flg' => $request->flg]);
    }

    /*
     * 採用申請実行
     */
    public function regist(Request $request)
    {
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 申請許可(本部)
     */
    public function allow(Request $request)
    {
        if (!$this->service->allow($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }
    
    /*
     * 価格登録(文化連)
     */
    public function confirm(Request $request)
    {
        if (!$this->service->confirm($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }
   
    /*
     * 採用承認(本部)
     */
    public function approval(Request $request)
    {
        if (!$this->service->approval($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 採用(施設)
     */
    public function adopt(Request $request)
    {
        if (!$this->service->adopt($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 採用可(施設)
     */
    public function adopt2(Request $request)
    {
        if (!$this->service->adopt2($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 取り下げ
     */
    public function withdraw(Request $request)
    {
        if (!$this->service->prev($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }

    /*
     * 差し戻し
     */
    public function remand(Request $request)
    {
        if (!$this->service->remand($request)) {
            // エラーメッセージ
        }
        return redirect()->route('apply.index', ['page' => $request->page]);
    }
}
