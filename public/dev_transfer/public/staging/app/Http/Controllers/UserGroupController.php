<?php

namespace App\Http\Controllers;

use App\Model\UserGroup;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use App\Services\UserGroupService;

class UserGroupController extends BaseController
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
        $this->service = new UserGroupService();

    }

    /*
     * ユーザー一覧
     */
    public function index(Request $request)
    {
        $list = $this->service->getUserGroupList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : UserGroupService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'conditions' => $this->service->getConditions($request),
            'page_count' => $page_count,
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 登録画面
     */
    public function add(Request $request)
    {
        $this->setViewData([
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
            'detail' => UserGroup::find($request->id),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * ユーザーグループ登録実行
     */
    public function register(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );

        if (!$this->service->regist($request) ) {
            return redirect()->route('usergroup.add')->withInput();
        }
        return redirect()->route('usergroup.index');

    }

    public function edit(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );

        if (!$this->service->edit($request)) {
            return redirect()->route('usergroup.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('usergroup.detail', ['id' => $request->id]);
    }

    /*
     * validation
     */
    public function getValidation()
    {
        return [
            'name' => 'required',
        ];
    }
    
    /*
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('usergroup.detail', ['id' => $request->id]);
        }
        return redirect()->route('usergroup.index');
    }

}
