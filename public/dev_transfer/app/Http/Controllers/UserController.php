<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
// use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use App\Services\UserService;
use App\Model\GroupRoleRelation;
use App\Model\UserGroup;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UserExport;
use PDF;
use Carbon\Carbon;

class UserController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
    // use AuthenticatesUsers;
    use RegistersUsers;
    use Pager;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/user/add';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('verified');
        $this->service = new UserService();
        $this->groupRoleRelation= new GroupRoleRelation();
        $this->userGroup = new UserGroup();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            //'email' => 'required|string|email|max:255|unique:users,email,'.$data['email'].',sub_id,'.$data['sub_id'],
        	'email' => 'required|string|email|max:255|unique:users,email,NULL,sub_id,sub_id,' . $data['sub_id'],
            'sub_id' => 'string|max:255',
            'password' => 'required|string|min:6|confirmed',
            //'user_group_id' => 'required|integer',
        ]);
    }

    /**
     * ユーザー一覧
     *
     * @param Request $request リクエスト情報
     */
    public function index(Request $request)
    {
        $list = $this->service->getUserList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : UserService::DEFAULT_PAGE_COUNT;
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

    /**
     * グループユーザー一覧取得
     *
     * @param Request $request リクエスト情報
     */
    public function groupUsers(Request $request)
    {
        $list = $this->service->getGroupUsersList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : UserService::DEFAULT_PAGE_COUNT;
        $ug_page = 1;
        if(!empty($request->ug_page)){
            $ug_page = $request->ug_page;
        }

        //ロール取得
        $group_id=$request->user_group_id;
        if (isBunkaren()) {
        	$group_id=$this->userGroup->getBunkarenUserGroupId();
        }


        // 一覧取得
        $this->setViewData([
            'user_group_id' => $request->user_group_id,
            'list'          => $list,
            'ug_page'       => $ug_page,
            'isAdmin'       => $this->service->getAdmin(),
            'roles'         => $this->groupRoleRelation->getRoles((int)$group_id),
            'pager'         => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'page_count'    => $page_count,
            'message'       => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * グループユーザーのロール更新
     *
     * @param Request $request リクエスト情報
     */
    public function userRoleUpdate(Request $request)
    {
        if (!$this->service->setRoles($request)) {
            return redirect()->route('user.group.index', ['user_group_id' => $request->user_group_id, 'ug_page' => $request->ug_page])->withInput();
        }
        return redirect()->route('user.group.index', ['user_group_id' => $request->user_group_id, 'ug_page' => $request->ug_page]);
    }

    /*
     * 登録画面
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'groups' => [], //$this->service->getGroups($request),新規はありえない
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
        $detail = $this->service->getUser($request->id);
        if(!empty($request->page)){
            $detail->page = $request->page;
        }
        $result = $this->userGroup->parent($detail->primary_user_group_id);
        $this->setViewData([
            'detail'       => $detail,
            'groups'       => $this->service->getGroups($request),
            'headquarters' => $result,
        	'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        if (!$this->service->regist($request)) {
            return redirect()->route('user.add')->withInput();
        }
        return redirect()->route('user.index');
    }

    /*
     * 編集実行
     */
    public function edit(Request $request)
    {
        $request->validate($this->service->editValidation($request));
        if (!$this->service->edit($request)) {
            return redirect()->route('user.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('user.detail', ['id' => $request->id]);
    }

    /*
     * パスワードリセット実行
     */
    public function reset(Request $request)
    {
        $user = Auth::user();
        $result = $this->service->reset($request);
        if ($result === false) {
            return redirect()->route('user.detail', ['id' => $request->id])->withInput();
        }
        // 取得したユーザと認証しているユーザが一致
        if ($result->id === $user->id) {
            Auth::logout();
        }
        return redirect()->route('user.detail', ['id' => $request->id]);
    }

    /*
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('user.detail', ['id' => $request->id]);
        }
        return redirect()->route('user.index');
    }

    /**
     * テスト
     */
    public function export(Request $request)
    {
        return Excel::download(new UserExport, 'users.xlsx');
    }
}
