<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\TableService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use Carbon\Carbon;

class TableController extends BaseController
{
    /* ビジネスロジックのオブジェクト */
    private $service;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new TableService();
    }

    /*
     * テーブルのレコード一覧取得
     */
    public function index(Request $request)
    {
        // $this->middleware('guest');
        // リクエストテーブル名のレコード取得
        $list = $this->service->getList($request);
        return response()->json($list, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /*
     * 複数ファイルダウンロード処理
     */
    public function download(Request $request)
    {
        return $this->service->putFiles($request);
    }
}
