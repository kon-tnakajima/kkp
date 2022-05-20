<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Http\Controllers\Concerns\Pager;

class AccountController extends BaseController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
        $this->service = new AccountService();

    }

    /*
     * 申請画面
     */
    public function index(Request $request)
    {
        $this->setViewData([
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 申請実行処理
     */
    public function exec(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'organization_id' => 'required|alpha_dash',
            'name' => 'required',
        ]);
        if (!$this->service->regist($request)) {
            return redirect()->route('account.index')->withInput();
        }
        return redirect()->route('account.done');
    }

    /**
     * 申請完了画面
     */
    public function done(Request $request)
    {
        $this->setViewData([
            'message' => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

}
