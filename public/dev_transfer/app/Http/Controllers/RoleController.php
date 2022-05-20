<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Services\UserService;
use App\Services\UserGroupService;
use App\Services\RoleService;
use App\Services\PrivilegeService;
use App\Http\Controllers\Concerns\Pager;
use App\Model\Role;
use App\User;

class RoleController extends BaseController
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
        //$this->middleware('guest');
        $this->service = new RoleService();
        $this->privilege = new PrivilegeService();
        $this->user = new User();
    }

    /**
     * ロール一覧
     *
     * @param Request $request リクエスト情報
     */
    public function index(Request $request)
    {
        $list = $this->service->getRoles($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : RoleService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list'       => $list,
            'types'      => UserGroupService::getUserGroupTypes(),
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
            'privieges'    => $this->privilege->getPrivilegeList($request),
            'types'        => UserGroupService::getUserGroupTypes(),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 権限情報JSONで取得
     *
     * @param Request $request リクエスト情報
     */
    public function search(Request $request)
    {
        return response()->json($this->privilege->getPrivilegeList($request), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * ロール登録実行
     */
    public function regist(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );
        if (!$this->service->regist($request) ) {
            return redirect()->route('role.add')->withInput();
        }
        return redirect()->route('role.index');
    }

    /**
     * ロール編集画面
     */
    public function detail(Request $request)
    {
        $role = Role::find($request->id);
        $request->merge(['role_key' => $role->key_code]);
        $list = $this->service->getPrivilegeByKeyCode($request, $role);
        $page_count = !empty($request->page_count) ? $request->page_count : RoleService::DEFAULT_PAGE_COUNT;
        if(!empty($request->page)){
            $role->page = $request->page;
        }
        $this->setViewData([
            'detail'           => $role,
            'privieges'        => $this->privilege->getPrivilegeList($request, true),
            'list'             => $list,
            'page_count'       => $page_count,
            'disp_privieges'   => !empty($request->page_count) ? 'show' : '',
            'pager'            => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'errorMessage'     => $request->session()->get('errorMessage', ''),
            'message'          => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * ロール編集実行
     */
    public function edit(Request $request)
    {
        if (!$this->service->edit($request)) {
            return redirect()->route('role.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('role.detail', ['id' => $request->id]);
    }

    /**
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('role.detail', ['id' => $request->id]);
        }
        return redirect()->route('role.index');
    }

    /**
     * ロール複製画面
     */
    public function copy(Request $request)
    {
        $role = Role::find($request->id);
        $request->merge(['role_key' => $role->key_code]);
        if(!empty($request->page)){
            $role->page = $request->page;
        }
        $this->setViewData([
            'detail'           => $role,
            'privieges'        => $this->privilege->getPrivilegeList($request, true),
            'types'            => UserGroupService::getUserGroupTypes(),
            'errorMessage'     => $request->session()->get('errorMessage', ''),
            'message'          => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * ロール複製登録実行
     */
    public function copyRegist(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );
        if (!$this->service->copyRegist($request) ) {
            return redirect()->route('role.copy')->withInput();
        }
        return redirect()->route('role.index');
    }

    /**
     * validation
     */
    public function getValidation()
    {
        return [
            'name'       => 'required',
            'key_code'   => 'required',
            'group_type' => 'required',
        ];
    }
}
