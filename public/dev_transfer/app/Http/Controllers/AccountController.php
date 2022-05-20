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
use App\Http\Controllers\Concerns\Pager;
use App\Model\ApplicationUseRequest;
use App\Model\ApplicationGroupRequest;
use App\User;

class AccountController extends BaseController
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
        //$this->middleware('guest');
        $this->service = new AccountService();
    }

    /**
     * 利用申請メニュー表示
     *
     * @param Request $request リクエスト情報
     */
    public function menu(Request $request)
    {
        $this->middleware('guest');
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 利用申請画面
     */
    public function request(Request $request)
    {
        $this->middleware('guest');
        $this->setViewData([
            'condition'     => $request->condition,
            'agreement'     => $this->service->getAgreement() ?? null,
            'errorMessage'  => $request->session()->get('errorMessage', ''),
            'message'       => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 利用申請実行処理
     */
    public function exec(Request $request)
    {
        $this->middleware('guest');
        $validations = [];
        if ((int)$request->condition === 9) {
            // 新規条件のバリデーション
            $validations = [
                'email'           => 'required|email',
                'user_group_name' => 'required',
                'name'            => 'required',
            ];
        } else {
            // 既存条件のバリデーション
            $validations = [
                'email'     => 'required|email',
                'group_key' => 'required',
                'name'      => 'required',
            ];
        }
        $request->validate($validations);
        if (!$this->service->regist($request)) {
            return redirect()->route('account.request', ['condition' => $request->condition])->withInput();
        }
        return redirect()->route('account.done');
    }

    /**
     * 利用申請完了画面
     */
    public function done(Request $request)
    {
        $this->middleware('guest');
        $this->setViewData([
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 新規利用申請一覧
     */
    public function groups(Request $request)
    {
        $list = $this->service->getAccountGroupList($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : AccountService::DEFAULT_PAGE_COUNT;
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
     * 新規利用申請詳細画面
     */
    public function groupDetail(Request $request)
    {
        $detail = ApplicationGroupRequest::find($request->id);
        if(!empty($request->page)){
            $detail->page = $request->page;
        }
        $this->setViewData([
            'detail'       => $detail,
            'group_types' => UserGroupService::getUserGroupTypes(),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 新規利用申請許可実行
     */
    public function groupPermission(Request $request)
    {
        if (!$request->transition_state) {
            if (!$this->service->groupPermission($request)) {
                return redirect()->route('account.group.detail', ['id' => $request->id])->withInput();
            }
            return redirect()->route('account.group.index', ['page' => $request->page]);
        }
        // 却下処理
        if (!$this->service->groupRejection($request)) {
            return redirect()->route('account.group.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('account.group.index', ['page' => $request->page]);
    }

    /**
     * 新規利用申請開始画面
     */
    public function groupStart(Request $request)
    {
        $detail = ApplicationGroupRequest::find($request->id);
        if(!empty($request->page)){
            $detail->page = $request->page;
        }
        $this->setViewData([
            'detail'       => $detail,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 新規利用申請開始
     */
    public function groupStartExec(Request $request)
    {
        if (!$this->service->groupStart($request)) {
            return redirect()->route('account.group.start', ['id' => $request->id, 'page' => $request->page])->withInput();;
        }
        return redirect()->route('account.group.index', ['page' => $request->page]);
    }

    /**
     * 既存グループ利用申請一覧
     *
     * uses
     */
    public function uses(Request $request)
    {
        $user = Auth::user();

        $list = $this->service->getAccountUseList($request, $user->userGroup()->id);
        $page_count = (!empty($request->page_count)) ? $request->page_count : AccountService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'conditions' => $this->service->getConditions($request),
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'page_count' => $page_count,
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 既存グループ利用申請許可画面
     */
    public function useDetail(Request $request)
    {
        $detail = ApplicationUseRequest::find($request->id);
        if(!empty($request->page)){
            $detail->page = $request->page;
        }

        $group_name = '';
        $roles = array();
        // 個人の場合
        if ($detail->group_key === \Config::get('const.kojin_group_key')) {
           $group_name = $detail->user_group_name;
           $roles = $this->getKojinRole();
        // 既存グループの場合
        } else {
            $group_name = $this->service->getUserGroupName($detail->group_key);
            $roles = $this->service->getRoleList($detail->group_key);
        }

        $this->setViewData([
            'detail'       => $detail,
            'group_name'   => $group_name,
            'roles'        => $roles,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 個人ロール一覧
     *
     * @return array 個人ロール一覧情報
     */
    public static function getKojinRole()
    {
    	$stdArray[]['name'] = '個人利用者';
    	$stdArray[]['name'] = '権限無し';
    	$stdArray[]['name'] = '参照';

    	return $stdArray;
    }

    /*
     * 既存グループ利用申請開始画面
     */
    public function useStart(Request $request)
    {
        if (!$request->transition_state) {
                if (!$this->service->useStart($request)) {
                return redirect()->route('account.use.detail', ['id' => $request->id, 'page' => $request->page])->withInput();;
            }
            return redirect()->route('account.use.index',['page' => $request->page]);
        }
        if (!$this->service->useRejection($request)) {
            return redirect()->route('account.use.detail', ['id' => $request->id, 'page' => $request->page])->withInput();;
        }
        return redirect()->route('account.use.index',['page' => $request->page]);
    }
}
