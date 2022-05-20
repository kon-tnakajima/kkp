<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\InfoService;
use App\Model\Information;
use App\Http\Controllers\Concerns\Pager;

class DashboardController extends BaseController
{
    const INFO_COUNT = 5;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('verified');
        $this->infoService = new InfoService();

    }

    /*
     * ダッシュボード
     */
    public function index(Request $request)
    {
        $infoList = $this->infoService->getDashbordList($request, self::INFO_COUNT);

        // 一覧取得
        $this->setViewData([
            'infoList' => $infoList,
            'page_count' => self::INFO_COUNT,
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

}
