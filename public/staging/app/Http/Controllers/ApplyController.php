<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\BaseController;
use App\Services\ApplyService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use Illuminate\Support\Facades\Response;
use App\Model\Task;
use App\Model\Medicine;

class ApplyController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 10;
    /* ビジネスロジックのオブジェクト */
    private $applyService;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new ApplyService();
    }
    /*
     * 採用申請一覧
     */
    public function index(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);

        $conditions = $this->service->getConditions($request);

        if (!in_array((int)$request->page_count,[20,50,100],true)){
            $request->page_count = ApplyService::DEFAULT_PAGE_COUNT;
        }

        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('apply_count')) || $request->initial) ? ApplyService::DEFAULT_PAGE_COUNT : session()->get('apply_count');
            $request->page_count = $page_count;
        }
        if (is_null($request->initial)) {
            $list = $this->service->getApplyIndexList($request, $user);
        } else {
            $list= new LengthAwarePaginator(
                array(), // ページ番号で指定された表示するレコード配列
                0, // 検索結果の全レコード総数
                20, // 1ページ当りの表示数
                1, // 表示するページ
                array('path' => $request->url()) // ページャーのリンク先のURLを指定
            );
        }

        session()->put('apply_count', $page_count);
        $set_default = $this->service->isInitial($request);
        //権限取得
        // $regist_kengen = $this->service->getRegistPrivileges($user->id, $user->primary_user_group_id, Task::STATUS_UNAPPLIED, 0);

        //追加項目検索条件
        $optional_search = $this->service->getOptionalSearch($user->primary_user_group_id);
        //追加項目検索条件 プルダウン中身
        $search_list = $this->service->getOptionalSearchListBox($user->primary_user_group_id);

        //追加項目検索条件　本部
        $hp_optional_search = $this->service->getOptionalSearch($user->primary_honbu_user_group_id);
        //追加項目検索条件 本部　プルダウン中身　
        $hp_search_list = $this->service->getOptionalSearchListBox($user->primary_honbu_user_group_id);

        $tasks = $this->service->getTasks();
        // unset($tasks[Task::STATUS_ADOPTABLE]); //採用可
        // unset($tasks[Task::STATUS_DONE]); //採用済み
        $tasks_min = $this->service->getTasksMin();
   

        // 一覧取得
        $this->setViewData(array(
            'list'            => $list,
            'page_count'      => $page_count,
            'conditions'      => $conditions,
            'set_default'     => $set_default,
            'pager'           => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'user_groups'     => $this->service->getUserGroupsFacility($user),
            'tasks'           => $tasks,
            'tasks_min'        => $tasks_min,
            'optional_search' => $optional_search,
            'search_list'     => $search_list,
            'hq_optional_search' => $hp_optional_search,
            'hp_search_list'     => $hp_search_list,
            'message'         => $request->session()->get('message', ''),
            'errorMessage'    => $request->session()->get('errorMessage', ''),
            // 'regist_kengen'   => $regist_kengen,
            'kengen'          => $kengen
        ));

        // XXX ダンプ
//         var_dump("\n");
//         for ($i = 0; $i < min(1, $list->count()); $i++) {
//             $item = $list[$i];
//             var_dump($item);
//         }
$pager = $this->getPager($list, self::PAGER_DISPLAY_COUNT);

logger('page_count: '.$page_count);
logger('current: '.$pager->current);
logger('last: '.$pager->last);

        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 採用申請
     */
    public function add(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        $user = Auth::user();

        $this->setViewData([
            'page' => $request->page,
            'applygroups' => $this->service->getApplyGroup(null,1,$user),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
            'maker_list' => $this->service->maker->all(),
            'primary_user_group_id' => $user->primary_user_group_id,
        ]);
        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 採用申請
     */
    public function addexec(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        $request->validate(array(
            'sales_packaging_code' => 'required',
            'medicine_price' => 'nullable|numeric',
            'purchase_price' => 'numeric',
            'sales_price' => 'nullable|numeric',
        ));

        $user = Auth::user();
        if (!$this->service->add($request, $user)) {
            return redirect()->route('apply.add')->withInput();
        }
        $this->end_log();
        return redirect()->route('apply.index', ['initial' => 1]);
    }

    /*
     * 薬品登録再申請
     */
    public function reentry(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        $user = Auth::user();
        $this->service->reentry($request, $user);

        $this->end_log();
        return redirect()->route('apply.index', array('page' => $request->page));
    }


    /*
     * 採用申請詳細
     */
    public function detail(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        $user = Auth::user();

        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        } else {
            $page_count = ApplyService::DEFAULT_PAGE_COUNT;
            $request->page_count = $page_count;
        }
        $list = $this->service->getApplyDetailList($request, $user);

        $page_count = ApplyService::DEFAULT_PAGE_COUNT;
        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        }

        $detail = $this->service->getApplyDetail($request);
        //追加項目検索条件 プルダウン中身
        $search_list = $this->service->getOptionalSearchListBox($user->primary_user_group_id);
        $this->medicine = new Medicine();
        $medicine = $this->medicine->find($request->id);
        // XXX ダンプ
//         var_dump($detail);

        $sellers = $this->service->getTraders($request,$user);
        $applygroups = $this->service->getApplyGroup($detail->facility_id,$detail->status,$user);
        $pager = $this->getPager($list, self::PAGER_DISPLAY_COUNT);

        $this->end_log();
        return view(\Route::currentRouteName(), array(
            'detail' => $detail,
            'page_count' => $page_count,
            'list' => (empty($list)) ? array() : $list,
            'sellers' => $sellers,
            'applygroups' => $applygroups,
            'search_list' => $search_list,
            'pager' => (empty($list)) ? array() : $pager,
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message' => $request->session()->get('message', ''),
            'medicine' => $medicine
        ));
    }

    /*
     * 採用申請修正
     */
    public function edit(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        // TODO
        // この情報は誰が修正できるのか確認(施設 or 本部 or 文化連)

        // TODO バリデーション
        $request->validate(array(
//            'purchase_price' => 'required|numeric',
//            'sales_price' => 'required|numeric',
        ));

        // 実行
        $this->service->edit($request);
        $this->end_log();
        return redirect()->route('apply.detail', array('id' => $request->id,'fp_id' => $request->fp_id, 'pa_id'=> $request->pa_id, 'fm_id'=>$request->fm_id, 'fp_honbu_id'=>$request->fp_honbu_id, 'page'=>$request->page, 'page_count' => $request->page_count))->withInput();
    }

    private function simple_status_update_request(Request $request, $service_func_name) {
        $add_level = 1;
        $this->start_log($add_level);
        $this->array_log($request, 'リクエスト情報', $add_level);

        if (!$this->service->$service_func_name($request)) {
            // エラーメッセージ
            $this->error_log($add_level);
        }

        $this->end_log($add_level);
        return redirect()->route('apply.index', array('page' => $request->page, 'page_count' => $request->page_count));
    }

    /*
     * 採用申請実行
     */
    public function regist(Request $request) {
        return $this->simple_status_update_request($request, 'regist');
    }


    /*
     * 申請許可(本部)
     */
    public function allow(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        if (!$this->service->allow($request)) {
            // エラーメッセージ
            $this->error_log();
            if($request->isCheck == "1") {
                return redirect()->route('apply.index', array('page' => $request->page, 'page_count' => $request->page_count));
            } else if ($request->isCheck == "2") {
                return redirect()->route('apply.detail', array('id' => $request->id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'pa_id' => $request->pa_id, 'fp_honbu_id'=>$request->fp_honbu_id, 'page'=>$request->page, 'page_count' => $request->page_count ))->withInput();
            }
        }

        $this->end_log();
        return redirect()->route('apply.index', array('page' => $request->page, 'page_count' => $request->page_count));
    }

    /*
     * 価格登録(文化連)
     */
    public function confirm(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        if (!$this->service->confirm($request)) {
            // エラーメッセージ
            $this->error_log();
            if($request->isCheck == "1") {
                return redirect()->route('apply.index', array('page' => $request->page,'page_count' => $request->page_count));
            } else if ($request->isCheck == "2") {
                return redirect()->route('apply.detail', array('id' => $request->id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'pa_id' => $request->pa_id, 'fp_honbu_id' => $request->fp_honbu_id, 'page' => $request->page, 'page_count' => $request->page_count))->withInput();
            }
        }

        $this->end_log();
        return redirect()->route('apply.index', array('page' => $request->page, 'page_count' => $request->page_count));
    }



    /*
     * registArrow(文化連)
     */
    public function registArrow(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        if (!$this->service->registArrow($request)) {
            // エラーメッセージ
            $this->error_log();
            if($request->isCheck == "1") {
                return redirect()->route('apply.index', array('page' => $request->page,'page_count' => $request->page_count));
            } else if ($request->isCheck == "2") {
                return redirect()->route('apply.detail', array('id' => $request->id, 'fm_id' => $request->fm_id, 'fp_id' => $request->fp_id, 'pa_id' => $request->pa_id, 'fp_honbu_id' => $request->fp_honbu_id, 'page' => $request->page, 'page_count' => $request->page_count))->withInput();
            }
        }

        $this->end_log();
        return redirect()->route('apply.index', array('page' => $request->page, 'page_count' => $request->page_count));
    }

    /*
     * 採用承認(本部)
     */
    public function approval(Request $request) {
        return $this->simple_status_update_request($request, 'approval');
    }

    /*
     * 採用(施設)
     */
    public function adopt(Request $request) {
        return $this->simple_status_update_request($request, 'adopt');
    }

    /*
     * 採用可(施設)
     */
    public function adopt2(Request $request) {
        return $this->simple_status_update_request($request, 'adopt2');
    }

    /*
     * 取り下げ
     */
    public function withdraw(Request $request) {
        return $this->simple_status_update_request($request, 'prev');
    }

    /*
     * 差し戻し
     */
    public function remand(Request $request) {
        return $this->simple_status_update_request($request, 'remand');
    }

    /*
     * 見送り
     */
    public function reject(Request $request) {
        return $this->simple_status_update_request($request, 'reject');
    }


    /*
     * 採用品一覧CSV
     */
    public function download(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $conditions = $this->service->getConditions($request);
        $data = $this->service->getApplayListForCSV($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode('医薬品_採用品検索_').date('YmdHis').'.csv',
        );

        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 採用品一覧SQL
     */
    public function download2(Request $request) {
        $this->start_log();
        $this->array_log($request, 'リクエスト情報');

        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $conditions = $this->service->getConditions($request);
        $data = $this->service->getApplayListForSQL($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"','', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode('採用申請SQL_').date('YmdHis').'.txt',
        );

        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * ajaxStatusChangeBunkaren(文化連専用)
     */
    public function ajaxStatusChangeBunkaren(Request $request) {
        try {
            $result = $this->service->ajaxStatusChangeBunkaren($request);
            return json_decode(json_encode($result), true)[0];
        } catch (\Exception $e) {
            //ストアドf_price_adoptions_update_ajax()からのエラーメッセージであれば、必要部分のみを抽出する。それ以外のエラーであれば何もしない。
            $pattern = "/KKP_PA_UPDATE_ERROR_MESSAGE[\s\S]*KKP_PA_UPDATE_ERROR_MESSAGE/";
            preg_match($pattern, $e->getMessage(), $match); 
            $spErrorMessage = str_replace("KKP_PA_UPDATE_ERROR_MESSAGE", "",$match);
            $errorMessage = empty($spErrorMessage) ? $e->getMessage() : $spErrorMessage[0];
            
            return response()->json([
                'errorMessage' => $errorMessage
            ], 500);
        }
    }
}
