<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View; 
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
            // ログイン中の場合ユーザコンテナを登録
            if (Auth::check()) {
                $user = Auth::user();
                View::share(['sidebars' => $user->getSideBarPrivilege()]);
                app()->instance('UserContainer', new UserContainer($user->userGroup()));
            }
            return $next($request);
        });


    }

}
