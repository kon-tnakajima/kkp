<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\InfoService;
use App\Model\Information;
use App\Http\Controllers\Concerns\Pager;
use Illuminate\Support\Facades\Auth;
use App\Services\ApplyService;

/**
 * ダッシュボードクラス
 *
 * 
 */
class DashboardController extends BaseController
{
    const INFO_COUNT = 10;
    use Pager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('verified');
        $this->infoService = new InfoService();
        $this->applyService = new ApplyService();
    }

    /**
     * ダッシュボード表示
     *
     * @param Request $request リクエスト情報
     * @return ビュー情報
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        //担当分を取得
        $request->status = $this->applyService->isInitial($request);
        //お知らせ
        $infoList = $this->infoService->getDashbordList($request, self::INFO_COUNT);
        // 一覧取得
        $this->setViewData([
            'targets'    => [],
            'infoList'   => $infoList,
            'pager'      => (empty($infoList)) ? [] : $this->getPager($infoList, self::INFO_COUNT),
            'page_count' => self::INFO_COUNT,
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 詳細画面
     *
     * @param Request $request リクエスト情報
     * @return ビュー情報
     */
    public function detail(Request $request)
    {
        $this->setViewData([
            'categories'   => Information::CATEGORY_STR,
            'detail'       => Information::find($request->id),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'page'         => $request->page,
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
}
