<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use Illuminate\Support\Facades\Response;
use App\Services\AgreementService;
use App\Model\Agreement;

class AgreementController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 20;
    use Pager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new AgreementService();
    }

    /**
     * 規約一覧
     * 
     * @param Request $request リクエスト情報
     */
    public function index(Request $request)
    {
        $list = $this->service->getAgreements($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : AgreementService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list'       => $list,
            'conditions' => $this->service->getConditions($request),
            'pager'      => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'page_count' => $page_count,
            'message'    => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 登録画面
     *
     * @param Request $request リクエスト情報
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'page'         => empty($request->page) ? null: $request->page,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 規約登録実行
     */
    public function regist(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );
        if (!$this->service->regist($request) ) {
            return redirect()->route('agreement.add', ['page' => $request->page])->withInput();
        }
        return redirect()->route('agreement.index', ['page' => $request->page]);
    }

    /**
     * 規約編集画面
     */
    public function detail(Request $request)
    {
        $agreement = Agreement::find($request->id);
        if(!empty($request->page)){
            $agreement->page = $request->page;
        }
        $this->setViewData([
            'detail'            => $agreement,
            'errorMessage'      => $request->session()->get('errorMessage', ''),
            'message'           => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 規約編集実行
     */
    public function edit(Request $request)
    {
        if (!$this->service->edit($request)) {
            return redirect()->route('agreement.detail', ['id' => $request->id, 'page' => $request->page])->withInput();
        }
        return redirect()->route('agreement.detail', ['id' => $request->id, 'page' => $request->page]);
    }

    /**
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('agreement.index', ['page' => $request->page]);
        }
        return redirect()->route('agreement.index', ['page' => $request->page]);
    }

    /**
     * validation
     */
    public function getValidation()
    {
        return [
            'body'           => 'required',
            'from_date'      => 'required',
        ];
    }

    /**
     * ファイルダウンロード
     */
    public function download(Request $request)
    {
        return $this->service->download($request);
    }

    /**
     * 利用申請側の規約ファイルダウンロード
     */
    public function agreementDownload(Request $request)
    {
        $this->middleware('guest');
        return $this->service->download($request, true);
    }
    
}
