<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\Viewformat;
use App\Containers\UserContainer;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ViewFormat;
    protected $function_id = 0;
    /**
     * 初期処理
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // TODO roleの実装までコメントアウト
            //if (Auth::user()->isAccessAllowed($this->function_id) === false) {
            //    return response('Forbidden', 403);
            //}

            // ログイン中の場合ユーザコンテナを登録
            if (Auth::check()) {
                $user = Auth::user();
                $user = new UserContainer($user->userGroups()->first(), $user->facility);
                app()->instance('UserContainer', $user);
            }
            return $next($request);
        });


    }

}
