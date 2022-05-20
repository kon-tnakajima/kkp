<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\PaymentService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;

class PaymentController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
    /* ビジネスロジックのオブジェクト */
    private $paymentService;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new PaymentService();
    }
    /*
     * 採用申請一覧
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('apply_count')) || $request->initial) ? PaymentService::DEFAULT_PAGE_COUNT : session()->get('apply_count');
            $request->page_count = $page_count;
        }
        $list = $this->service->getList($request, $user);

        session()->put('apply_count', $page_count);

        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'page_count' => $page_count,
            'set_facility' => $user->primary_user_group_id,
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'facilities' => $this->service->getUserGroups($user),
            'tasks' => $this->service->getTasks(),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 採用申請詳細
     */
    public function detail(Request $request)
    {
        $user = Auth::user();
        $list = $this->service->getInvoiceDetail($request, $user);

        $page_count = PaymentService::DEFAULT_PAGE_COUNT;
        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        }
        
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getPaymentDetail($request),
        'page_count' => $page_count,
        'list' => (empty($list)) ? array() : $list,
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
        return redirect()->route('payment.detail', ['id' => $request->id,'flg' => $request->flg]);
    }

    /*
     * 採用申請実行
     */
    public function regist(Request $request)
    {
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }

    /*
     * 申請許可(本部)
     */
    public function confirm(Request $request)
    {
        if (!$this->service->confirm($request)) {
            // エラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }
    
    /*
     * 価格登録(文化連)
     */
    public function complete(Request $request)
    {
        if (!$this->service->payment($request)) {
            // エラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }
   
    /*
     * 採用承認(本部)
     */
    public function import(Request $request)
    {
        if (!$this->service->import($request)) {
            // エラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }

    /*
     * 取り下げ
     */
    public function withdraw(Request $request)
    {
        if (!$this->service->prev($request)) {
            // エラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }

    /*
     * 差し戻し
     */
    public function remand(Request $request)
    {
        if (!$this->service->remand($request)) {
            // エラーメッセージ
        }
        return redirect()->route('payment.index', ['page' => $request->page]);
    }
}
