<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use App\Services\UserService;

class UserController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
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
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'user_group_id' => 'required|integer',
        ]);
    }

    /*
     * ユーザー一覧
     */
    public function index(Request $request)
    {
        $list = $this->service->getUserList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : UserService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'groups' => $this->service->getGroups(),
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
            'groups' => $this->service->getGroups(),
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
            'groups' => $this->service->getGroups(),
            'detail' => User::find($request->id),
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
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('user.detail', ['id' => $request->id]);
        }
        return redirect()->route('user.index');
    }
}
