<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Concerns\Viewformat;
use App\Containers\UserContainer;

class BaseController extends Controller {
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
                View::share(array('sidebars' => $user->getSideBarPrivilege()));
                app()->instance('UserContainer', new UserContainer($user->userGroup()));
            }
            return $next($request);
        });
    }

    private function get_caller_str($add_level = 0) {
        $dbg = debug_backtrace();
        return sprintf('%s#%s', get_class($this), $dbg[2 + $add_level]['function']);
    }

    protected function start_log($add_level = 0) {
        logger(str_pad('', 140, "#"));
        logger(sprintf('# 【START】 [%s]', $this->get_caller_str($add_level)));
    }

    protected function end_log($add_level = 0) {
        logger(sprintf('# 【END  】 [%s]', $this->get_caller_str($add_level)));
        logger(str_pad('', 140, "#"));
    }

    protected function error_log($add_level = 0) {
        logger(sprintf('# 【ERROR】 [%s]', $this->get_caller_str($add_level)));
    }

    /**
     * 配列をログに整形して出力する
     *
     * @param $request リクエスト情報
     * @param $label ログに出力される文言
     * @param $add_level ログに出力したい呼出し元情報の階層が深い場合、掘り下げる呼出しの回数を指定する
     * @param $delimiter ログに出力される装飾文字（省略で'#'）
     * @param $key_length ログに出力されるパラメータ名を何文字で整形するかの指定（省略で30）
     */
    protected function array_log($request, $label = 'ラベルなし', $add_level = 0, $delimiter = '#', $key_length = 30) {
        $delimi_line = str_pad('', 80, $delimiter);

        // 呼出し元情報
        $str = $this->get_caller_str($add_level);

        logger($delimi_line);
        logger(sprintf('%3$s%3$s %1$s │%2$s', $str, $label, $delimiter));
        logger(sprintf('%2$s%2$s-%1$s-┘', preg_replace('/./', '-', $str), $delimiter));

        $log_format = sprintf('%%4$s%%4$s (%%1$-7s)::%%2$-%ds :: %%3$s', $key_length);
        foreach ($request->request as $key => $value) {
            if (gettype($value) === "array") {
                logger(sprintf($log_format, gettype($value), $key, '[' . implode(", ", $value) . ']', $delimiter));
            } else if (gettype($value) === "object") {
                logger(sprintf('%s :: %s', $key, gettype($value)));
                logger($value);
            } else {
                logger(sprintf($log_format, gettype($value), $key, $value, $delimiter));
            }
        }
        logger($delimi_line);
    }

}
