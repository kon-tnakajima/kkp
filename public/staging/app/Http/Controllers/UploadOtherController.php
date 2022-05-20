<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\BaseController;
use App\Services\UploadOtherService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UploadOtherController extends BaseController
{
    /* ビジネスロジックのオブジェクト */
    private $service;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new UploadOtherService();
    }

    public function index(){
        // \Log::debug(Schema::getColumnListing('medicine_temporaries'));
    	return view('other');
    }

    /*
     * テーブルのレコード一覧取得
     */
    public function store(Request $request)
    {
        // $this->middleware('guest');
        // リクエストテーブル名のレコード取得
        $result = $this->service->store($request);
        return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
    }
}
