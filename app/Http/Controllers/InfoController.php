<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\InfoService;
use App\Model\Information;
use App\Http\Controllers\Concerns\Pager;

class InfoController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
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
        $this->service = new InfoService();

    }

    /*
     * お知らせ一覧
     */
    public function index(Request $request)
    {
        $list = $this->service->getInfoList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : InfoService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'categories' => Information::CATEGORY_STR,
            'conditions' => $this->service->getConditions($request),
            'page_count' => $page_count,
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 登録画面
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'categories' => Information::CATEGORY_STR,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 詳細画面
     */
    public function detail(Request $request)
    {
        $this->setViewData([
            'categories' => Information::CATEGORY_STR,
            'detail' => Information::find($request->id),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 登録処理
     */
    public function regist(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );
        if (!$this->service->regist($request)) {
            return redirect()->route('info.add');
        }
        return redirect()->route('info.index');

    }

    /*
     * 更新処理
     */
    public function edit(Request $request)
    {
        $request->validate($this->getValidation());

        if (!$this->service->edit($request)) {
            return redirect()->route('info.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('info.detail', ['id' => $request->id]);
    }

    /*
     * validation
     */
    public function getValidation()
    {
        return [
            'title' => 'required',
            'contents' => 'required',
            'category' => 'required',
        ];
    }

    /*
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('info.detail', ['id' => $request->id]);
        }
        return redirect()->route('info.index');
    }

}
