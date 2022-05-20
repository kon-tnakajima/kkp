<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\Model\UserGroup;
use App\Model\UserGroupRelation;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
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
        $this->userGroup = new UserGroup();
        $this->userGroupRelation = new UserGroupRelation();

    }

    /*
     * ユーザグループ一覧
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // ロールキー取得
        $role_key = $this->userGroupRelation->getRoleKey($user->id, $user->primary_user_group_id);
        // 文化連管理者、グループ管理者のみ
        if ($role_key !== \Config::get('const.user_attribute.ALL_AUTHORIZED') &&
            $role_key !== \Config::get('const.user_attribute.GROUP_AUTHORIZED')) {
            return redirect()->route('dashboard.index');
        }
        $list = $this->service->getUserGroupList($request, $user);
        $page_count = (!empty($request->page_count)) ? $request->page_count : UserGroupService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list'        => $list,
            'pager'       => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'conditions'  => $this->service->getConditions($request),
            'group_types' => $this->service->getUserGroupTypes(),
            'page_count'  => $page_count,
            'message'     => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 登録画面
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'group_types'  => $this->service->getUserGroupTypes(),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 詳細画面
     */
    public function detail(Request $request)
    {
        $user = Auth::user();
        $detail = $this->userGroup->find((int)$request->id);
        if(!empty($request->page)){
            $detail->page = $request->page;
        }
        // ロールキー取得
        $role_key = $this->userGroupRelation->getRoleKey($user->id, $user->primary_user_group_id);
        // 本部確認
        $userGroup = $this->userGroup->find($user->primary_user_group_id);
        $dispHospital = false;
        $hospitalList=array();
        if ($role_key === \Config::get('const.user_attribute.GROUP_AUTHORIZED') &&
            $user->primary_user_group_id === (int)$request->id &&
            $userGroup->group_type === \Config::get('const.headquarters_name')) {

            	//$dispHospital = true;
        }

        if (isBunkaren() && $detail->group_type === \Config::get('const.headquarters_name') ) {
        	$dispHospital = true;
        }

        $this->setViewData([
            'disp_hospital'    => $dispHospital,
            'detail'           => $detail,
            'roles'            => $this->service->getRoles((int)$request->id),
            'hospitals'        => $this->service->getHospitals((int)$request->id, $role_key),
            'traders'          => $this->service->getTraders((int)$request->id),
            'types'            => $this->service->getTypeList((int)$request->id),
            'supplies'         => $this->service->getSupplyList((int)$request->id),
            'optional_medicine_key1' => $this->service->getOptionList(1, (int)$request->id),
            'optional_medicine_key2' => $this->service->getOptionList(2, (int)$request->id),
            'optional_medicine_key3' => $this->service->getOptionList(3, (int)$request->id),
            'optional_medicine_key4' => $this->service->getOptionList(4, (int)$request->id),
            'optional_medicine_key5' => $this->service->getOptionList(5, (int)$request->id),
            'optional_medicine_key6' => $this->service->getOptionList(6, (int)$request->id),
            'optional_medicine_key7' => $this->service->getOptionList(7, (int)$request->id),
            'optional_medicine_key8' => $this->service->getOptionList(8, (int)$request->id),
            'errorMessage'     => $request->session()->get('errorMessage', ''),
            'message'          => $request->session()->get('message', ''),
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
            return redirect()->route('usergroup.detail', ['id' => $request->id, 'page' => $request->page])->withInput();
        }
        return redirect()->route('usergroup.detail', ['id' => $request->id, 'page' => $request->page]);
    }

    /*
     * validation
     */
    public function getValidation()
    {
        return [
            'name' => 'required',
            'formal_name' => 'required',
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

    /**
     * 業者検索実行
     */
    public function search(Request $request)
    {
        return response()->json($this->service->searchTraders($request), 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 病院検索実行
     */
    public function searchHospital(Request $request)
    {
        return response()->json($this->service->searchHospitals($request), 200, [], JSON_UNESCAPED_UNICODE);
    }
}
