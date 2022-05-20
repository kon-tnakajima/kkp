<?php
/**
 * この機能はapplyに吸収されたので閉鎖
 */
// declare(strict_types=1);
// namespace App\Http\Controllers;

// use Illuminate\Support\Facades\Auth;
// use App\Http\Controllers\BaseController;
// use App\Services\AdoptionService;
// use Illuminate\Http\Request;
// use App\Http\Controllers\Concerns\Pager;
// use Illuminate\Support\Facades\Response;

// class AdoptionController extends BaseController
// {
//     const PAGER_DISPLAY_COUNT = 10;

//     /* ビジネスロジックのオブジェクト */
//     private $service;

//     protected $function_id = 1;

//     use Pager;

//     /*
//      * コンストラクタ
//      */
//     public function __construct()
//     {
//         parent::__construct();
//         $this->service = new AdoptionService();
//     }
//     /*
//      * 採用申請一覧
//      */
//     public function index(Request $request)
//     {
//         $user = Auth::user();
//         $conditions = $this->service->getConditions($request);
//         $list = $this->service->getAdoptionList($request, $user);

//         $page_count = AdoptionService::DEFAULT_PAGE_COUNT;
//         if(!empty($request->page_count)){
//             $page_count = $request->page_count;
//         }
//         // 一覧取得
//         $this->setViewData([
//             'list' => $list,
//             'page_count' => $page_count,
//             'conditions' => $conditions,
//             'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
//             'facilities' => $this->service->getUserGroups($user),
//             'message' => $request->session()->get('message', ''),
//         ]);
//         return view(\Route::currentRouteName(),  $this->getViewArray());
//     }

//     /*
//      * 採用申請詳細
//      */
//     public function detail(Request $request)
//     {
//         $detail = $this->service->getAdoptionDetail($request);

//         $user = Auth::user();
//         $pager = array();
//         if (!is_null($detail->medicine_id) ) {
//             $list = $this->service->getAdoptionList2($request, $user, $detail->medicine_id, $detail->fp_id);
//             $pager = $this->getPager($list, self::PAGER_DISPLAY_COUNT);
//         }else{
//             $list = array();
//         }
//         $page_count = AdoptionService::DEFAULT_PAGE_COUNT;
//         if(!empty($request->page_count)){
//             $page_count = $request->page_count;
//         }

//         $this->setViewData(['detail' => $detail,
//                             'list' => $list,
//                             'page_count' => $page_count,
//                             'pager' => $pager,
//                             'errorMessage' => $request->session()->get('errorMessage', ''),
//                             'message' => $request->session()->get('message', ''),
//                             'user' => app('UserContainer'),
//         ]);
//         return view(\Route::currentRouteName(), $this->getViewArray());
//     }

//     /*
//      * 採用品修正
//      */
//     public function edit(Request $request)
//     {
//         // 実行
//         $this->service->edit($request);
//         if(!empty($request->page)){
//             return redirect()->route('adoption.detail', ['id' => $request->id, 'page' => $request->page]);
//         }else{
//             return redirect()->route('adoption.detail', ['id' => $request->id]);
//         }
//     }

//     /*
//      * 採用品一覧CSV
//      */
//     public function download(Request $request)
//     {
//         $user = Auth::user();
//         $conditions = $this->service->getConditions($request);
//         $data = $this->service->getAdoptionListForCSV($request, $user);

//         $stream = fopen('php://temp', 'r+b');
//         foreach ($data as $row) {
//             fputcsv($stream, $row);
//         }
//         rewind($stream);
//         $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
//         $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
//         $headers = array(
//             'Content-Type' => 'text/csv',
//             'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode('医薬品_採用品検索_').date('YmdHis').'.csv',
//         );

//         return Response::make($csv, 200, $headers);
//     }

//     /*
//      * 採用品削除
//      * 削除は採用中止日に現在日付を入れるだけ
//      */
//     public function delete(Request $request)
//     {
//         if (!$this->service->stopAdoption($request)) {
//             return redirect()->route('adoption.detail', ['id' => $request->id]);
//         }

//         return redirect()->route('adoption.index');
//     }
// }
