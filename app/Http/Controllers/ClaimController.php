<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\ClaimService;
use App\Helpers\Apply;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;
use Carbon\Carbon;
use App\Model\Task;
use Illuminate\Support\Facades\Response;
use PDF;
use App\User;
use App\Model\UserGroup;

class ClaimController extends BaseController {
    const PAGER_DISPLAY_COUNT = 10;

    /* ビジネスロジックのオブジェクト */
    private $claimService;

    protected $function_id = 1;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct() {
        parent::__construct();
        $this->service = new ClaimService();
        $this->userGroup = new UserGroup();
    }

    /**
     * 請求登録
     *
     * @param Request $request リクエスト情報
     */
    public function regist(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            // return view("誘導画面への参照");
        }


        // if (!empty($request->page_count)) {
        //     $page_count = $request->page_count;
        // } else {
        //     $page_count = (is_null(session()->get('claim_count')) || $request->initial) ? ClaimService::DEFAULT_PAGE_COUNT : session()->get('claim_count');
        //     $request->page_count = $page_count;
        // }

        // $list = $this->service->getList($request, $user);
        $dt = new Carbon('first day of last month');
        $claim_date = $dt->format('Y/m');
        if (!empty($request->session()->get('claim_date', ''))) {
            $claim_date = $request->session()->get('claim_date', '');
        }


        // data_kbnListの取得を請求権限ありへ
        $data_kbnList = $this->service->getDataTypeList($user);

        // session()->put('claim_count', $page_count);

        // 一覧取得
        $this->setViewData([
            // 'list'          => $list,
            // 'page_count'    => $page_count,
            'set_facility'  => $user->primary_user_group_id,
            // 'pager'         => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'user_groups'  => $this->service->getUserGroupClaimRegistFacility($user),
            //'tasks'         => $this->service->getTasks(3), // TODO
            'select_data_type' => count($data_kbnList) == 1 ? $data_kbnList[0]->id : $request->session()->get('select_data_type', ''),
            'select_facility' => $request->session()->get('select_facility', ''),
            'medicine_price_total' => $request->session()->get('medicine_price_total', ''),
            'sales_price_total' => $request->session()->get('sales_price_total', ''),
            'purchase_price_total' => $request->session()->get('purchase_price_total', ''),
            'errorMessage'  => $request->session()->get('errorMessage', ''),
            'message'       => $request->session()->get('message', ''),
            'claim_month'   => $claim_date,
            'data_kbnList'  => $data_kbnList,
            'kengen'        => $kengen,
        ]);
        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 請求登録履歴
     */
    public function regist_list(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            // return view("誘導画面への参照");
        }

        $conditions = $this->service->getConditions($request);

        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('claim_count')) || $request->initial) ? ClaimService::DEFAULT_PAGE_COUNT : session()->get('claim_count');
            $request->page_count = $page_count;
        }
        $list = $this->service->getList($request, $user);

        $user_guide = $this->service->getRegistListUserGuide($user);

        $data_kbnList = $this->service->getDataTypeListClaimRegistList($user);
        $set_default = $this->service->isInitial($request);
        session()->put('claim_count', $page_count);

        // 一覧取得
        $this->setViewData([
            'list'         => $list,
            'page_count'   => $page_count,
            'conditions'   => $conditions,
            'set_facility' => $user->primary_user_group_id,
            'pager'        => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'user_groups'  => $this->service->getUserGroupClaimRegist($user),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
            'data_kbnList' => $data_kbnList,
            'kengen'       => $kengen,
            'user_guide'   => $user_guide
        ]);
        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 請求登録履歴SQL
     */
    public function regist_sql_download(Request $request) {
        logger('SQLrequestTTTTTT');
        logger($request);

        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $conditions = $this->service->getConditions($request);
        $data = $this->service->getClaimListForSQL($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求登録履歴SQL_') . date('YmdHis') . '.txt',
        );

        $this->end_log();
        return Response::make($csv, 200, $headers);
    }



    /*
     * 請求一覧
     */
    public function index(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            // return view("誘導画面への参照");
        }
        $conditions = $this->service->getConditions($request);

        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('claim_count')) || $request->initial) ? ClaimService::DEFAULT_PAGE_COUNT : session()->get('claim_count');
            $request->page_count = $page_count;
        }
        $list = $this->service->getDetailHeaders($request, $user);
        session()->put('claim_count', $page_count);
        $set_default = $this->service->isInitial($request);

        $data_kbnList = $this->service->getSupplyDevisionList($user);

        // 業者とそれ以外
        // $apply_payment_flg = 5;
        // $userGroup = new UserGroup();
        // $user_group = $userGroup->getUserGroup($user->primary_user_group_id);
        // $user_group_type = $user_group[0]->group_type;
        // if ($user_group_type == "業者") {
        //     $apply_payment_flg = 4;
        // }
        $pager = $this->getPager($list, self::PAGER_DISPLAY_COUNT);

        // 一覧取得
        $this->setViewData([
            'list'         => $list,
            'page_count'   => $page_count,
            'conditions'   => $conditions,
            'set_facility' => $user->primary_user_group_id,
            'set_default'  => $set_default,
            'pager'        => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'user_groups'  => $this->service->getUserGroupClaim($user),
            'message'      => $request->session()->get('message', ''),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'data_kbnList' => $data_kbnList,
            'kengen'       => $kengen
        ]);
        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 請求照会SQL
     */
    public function sql_download2(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $conditions = $this->service->getConditions($request);
        $data = $this->service->getClaimIndexForSQL($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求照会SQL_') . date('YmdHis') . '.txt',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 取引明細
     */
    public function detail(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            // return view("誘導画面への参照");
        }

        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        } else {
            $page_count = (is_null(session()->get('claim_detail_count')) || $request->initial) ? ClaimService::DEFAULT_PAGE_COUNT : session()->get('claim_detail_count');
            $request->page_count = $page_count;
        }


        //請求支払確認
        $list = $this->service->getTransactDetailsList($request);

        session()->put('claim_detail_count', $page_count);
        $transact_header_id = $list[0]->transact_header_id;

        $transactHeaders = $this->service->getMeisaiHeader($transact_header_id, $user, $request);//ここで取得しているのは明細別画面のconfirmations 1レコード
        
        $this->end_log();
        return view(
            \Route::currentRouteName(),
            [
                'transact_header' => $transactHeaders,
                'transact_header_id' => $transact_header_id,
                'page_count' => $page_count,
                'list' => (empty($list)) ? array() : $list,
                'pager' => (empty($list)) ? array() : $this->getPager($list, ClaimService::DEFAULT_PAGE_COUNT),
                'errorMessage' => $request->session()->get('errorMessage', ''),
                'message' => $request->session()->get('message', ''),
                'kengen'  => $kengen,
                'page' => $request->page ? $request->page : ''
            ]
        );
    }


    /*
     * 請求照会SQL(明細別 detail header)
     */
    public function sql_download5(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $list = $this->service->getTransactDetailsList($request);
        $transact_header_id = $list[0]->transact_header_id;
        $data = $this->service->getDetailHeaderForSQL($transact_header_id, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求照会明細別ヘッダーSQL_') . date('YmdHis') . '.txt',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 請求照会SQL(明細別  detail)
     */
    public function sql_download6(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $data = $this->service->getDetailListForSQL($request, $user,  -1);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求照会明細別明細SQL_') . date('YmdHis') . '.txt',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }


    /*
     * 請求支払確認
     */
    public function trader(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            // return view("誘導画面への参照");
        }

        $page_count = ClaimService::DEFAULT_PAGE_COUNT2;
        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        }
        $list = $this->service->getDetailTraders($request->id, $page_count);
        $user_guide = $this->service->getTraderUserGuide($request->id,$user);
        //$transact_file_storage_id = $list[0]->transact_file_storage_id;

        $transactHeaders = $this->service->getDetailHeaders($request, $user);

        $this->end_log();
        return view(
            \Route::currentRouteName(),
            [
                'detail' =>  $transactHeaders[0],
                'page_count' => $page_count,
                'list' => (empty($list)) ? array() : $list,
                'pager' => (empty($list)) ? array() : $this->getPager($list, self::PAGER_DISPLAY_COUNT),
                'errorMessage' => $request->session()->get('errorMessage', ''),
                'message' => $request->session()->get('message', ''),
                'kengen' => $kengen,
                'user_guide'   => $user_guide,
                'page' => $request->page ? $request->page : ''

            ]
        );
    }

    /*
     * 請求照会SQL(業者別 trader header)
     */
    public function sql_download3(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $data = $this->service->getTraderHeaderForSQL($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求照会業者別ヘッダーSQL_') . date('YmdHis') . '.txt',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 請求照会SQL(業者別 trader detail)
     */
    public function sql_download4(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();
        $data = $this->service->getTraderDetailForSQL($request->id, -1);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求照会業者別明細SQL_') . date('YmdHis') . '.txt',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 取引ヘッダ
     */
    public function supply(Request $request) {
        $this->start_log();
        $user = Auth::user();

        $page_count = ClaimService::DEFAULT_PAGE_COUNT2;
        if (!empty($request->page_count)) {
            $page_count = $request->page_count;
        }
        $list = $this->service->getDetailHeaders($request->id, $page_count);
        //$transact_file_storage_id = $list[0]->transact_file_storage_id;
        $this->end_log();
        return view(
            \Route::currentRouteName(),
            [
                'detail' => $this->service->getTransactFileStorage($request, $request->id),
                'page_count' => $page_count,
                'list' => (empty($list)) ? array() : $list,
                'pager' => (empty($list)) ? array() : $this->getPager($list, self::PAGER_DISPLAY_COUNT),
                'errorMessage' => $request->session()->get('errorMessage', ''),
                'message' => $request->session()->get('message', ''),
            ]
        );
    }

    /*
     * 請求修正
     */
    public function edit(Request $request) {
        $this->start_log();
        // TODO バリデーション
        //$request->validate([
        //    'sales_comment.*' => 'required',
        //]);

        // 実行
        $this->service->edit($request);
        $this->end_log();
        return redirect()->route('claim.detail', ['id' => $request->id, 'flg' => $request->flg]);
    }

    /*
     * 請求登録
     */
    public function regexec(Request $request) {
        $this->start_log();
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }

        // TODO 遷移先を登録画面に変更
        //return redirect()->route('claim.regist_list', ['page' => $request->page,'reginitial' => 1]);
        $this->end_log();
        return redirect()->route('claim.regist');
    }

    /*
     * 請求再実行
     */
    public function regexec_retry(Request $request) {
        $this->start_log();
        if (!$this->service->regist_retry($request)) {
            // TODOエラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.regist_list', ['page' => $request->page, 'reginitial' => 1]);
    }

    /*
     * 請求受付
     */
    public function recept(Request $request) {
        $this->start_log();
        if (!$this->service->recept($request)) {
            // TODOエラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.regist_list', ['page' => $request->page, 'reginitial' => 1]);
    }


    /*
     * 申請許可(本部)
     */
    public function confirm(Request $request) {
        $this->start_log();
        if (!$this->service->confirm($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.trader', ['id' => $request->head_id, 'page' => $request->page]);
    }

    /*
     * 支払確認
     */
    public function payconf(Request $request) {
        $this->start_log();
        if (!$this->service->payconf($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        if (empty($request->isDetail)) {
            return redirect()->route('claim.detail', ['id' => $request->id]);
        } else {
            return redirect()->route('claim.trader', ['id' => $request->head_id]);
        }
    }

    /*
     * 価格登録(文化連)
     */
    public function complete(Request $request) {
        $this->start_log();
        if (!$this->service->complete($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.trader', ['id' => $request->head_id, 'page' => $request->page]);
    }

    /*
     * 採用承認(本部)
     */
    public function import(Request $request) {
        $this->start_log();
        if (!$this->service->import($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.regist_detail');
    }

    /*
     * 取り下げ
     */
    public function withdraw(Request $request) {
        $this->start_log();
        if (!$this->service->prev($request)) {
            // エラーメッセージ
        }

        $this->end_log();
        if (empty($request->isDetail)) {
            return redirect()->route('claim.detail', ['id' => $request->id]);
        } else {
            return redirect()->route('claim.trader', ['id' => $request->head_id]);
        }
    }

    /*
     * 差し戻し
     */
    public function remand(Request $request) {
        $this->start_log();
        if (!$this->service->remand($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        if (empty($request->isDetail)) {
            return redirect()->route('claim.detail', ['id' => $request->id]);
        } else {
            return redirect()->route('claim.trader', ['id' => $request->head_id]);
        }
    }

    /*
     * 却下
     */
    public function reject(Request $request) {
        $this->start_log();
        if (!$this->service->reject($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.regist_list', ['page' => $request->page, 'reginitial' => 1]);
    }

    /*
     * コメント取得
     */
    public function comment(Request $request) {
        $this->start_log();
        //コメントを取得
        $list = $this->service->getClaimHistoryComment($request);

        //未読を既読に更新
        if (!empty($list) && count($list) > 0) {
            $this->service->updateReadFlg($request);
        }
        $this->end_log();
        return response()->json($list);
    }

    /*
     * 未読を既読に更新
     */
    public function updateReadFlg(Request $request) {
        $this->start_log();
        //未読を既読に更新
        if (!$this->service->updateReadFlg($request)) {
            return false;
        }
        $this->end_log();
        return true;
    }

    /*
     * コメント登録
     */
    public function registComment(Request $request) {
        $this->start_log();
        if (!$this->service->registComment($request)) {
            // エラーメッセージ
            return "false";
        }
        $this->end_log();
        return "true";
    }

    /*
     * ファイルダウンロード
     */
    public function download(Request $request) {
        $this->start_log();
        $this->end_log();
        return $this->service->download($request);
    }

    /*
     * 請求明細アップロード
     */
    public function regist_detail(Request $request) {
        $this->start_log();
        $user = Auth::user();

        // 一覧取得
        $this->setViewData([
            'message' => $request->session()->get('message', ''),
            'errorMessage' => $request->session()->get('errorMessage', ''),

        ]);
        $this->end_log();
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /*
     * 請求明細ダウンロード
     */
    public function download_detail(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '512M');
        $user = Auth::user();

        $data = $this->service->downloadDetail($request, $user);

        $stream = fopen('php://temp', 'r+b');
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求_請求明細_') . date('YmdHis') . '.csv',
        );
        $this->end_log();
        return Response::make($csv, 200, $headers);
    }

    /*
     * 請求照会(業者別) 施設ファイルアップロード
     */
    public function sendh_upload(Request $request) {
        $this->start_log();
        //logger('HEADCONTROLLER');
        //exit;
        if (!$this->service->sendh_upload($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.trader', ['id' => $request->head_id]);
    }

    /*
     * 請求照会(業者別) 押印済み施設ファイルアップロード
     */
    public function receiveh_upload(Request $request) {
        $this->start_log();
        //logger('HEADCONTROLLER');
        //exit;
        if (!$this->service->receiveh_upload($request)) {
            // エラーメッセージ
        }
        $this->end_log();
        return redirect()->route('claim.trader', ['id' => $request->head_id]);
    }

    /*
     * 請求照会(明細別) 業者ファイルアップロード
     */
    public function send_upload(Request $request) {
        $this->start_log();
        //logger('HEADCONTROLLER');
        //exit;
        if (!$this->service->send_upload($request)) {
            // エラーメッセージ
        } else {
            $this->service->payconf($request);
        }
        $this->end_log();
        return redirect()->route('claim.detail', ['id' => $request->id]);
    }

    /*
     * 請求照会(明細別) 押印済み業者ファイルアップロード
     */
    public function receive_upload(Request $request) {
        $this->start_log();
        if (!$this->service->receive_upload($request)) {
            // エラーメッセージ
        } else {
            $this->service->payconf($request);
        }
        $this->end_log();
        return redirect()->route('claim.detail', ['id' => $request->id]);
    }

    /*
     * 請求照会(業者別) 施設ファイルダウンロード
     */
    public function sendh_download(Request $request) {
        $this->start_log();
        $result = $this->service->sendh_download($request);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(業者別) 押印済み施設ファイルダウンロード
     */
    public function receiveh_download(Request $request) {
        $this->start_log();
        $result = $this->service->receiveh_download($request);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(明細別) 業者ファイルダウンロード
     */
    public function send_download(Request $request) {
        $this->start_log();
        $result = $this->service->send_download($request);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(明細別) 押印済み業者ファイルダウンロード
     */
    public function receive_download(Request $request) {
        $this->start_log();
        $result = $this->service->receive_download($request);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(業者別) 施設ファイルアップロード削除
     */
    public function sendh_delete(Request $request) {
        $this->start_log();
        if (!$this->service->sendh_delete($request)) {
            // エラーメッセージ
        }
        $result = redirect()->route('claim.trader', ['id' => $request->head_id]);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(業者別) 押印済み施設ファイルアップロード削除
    */
    public function receiveh_delete(Request $request) {
        $this->start_log();
        if (!$this->service->receiveh_delete($request)) {
            // エラーメッセージ
        }
        $result = redirect()->route('claim.trader', ['id' => $request->head_id]);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(明細別) 業者ファイルアップロード削除
     */
    public function send_delete(Request $request) {
        $this->start_log();
        if (!$this->service->send_delete($request)) {
            // エラーメッセージ
        } else {
            // 差し戻し
            if ($request->delete_control == "1") {
                $this->service->remand($request);

                // 取り下げ
            } else if ($request->delete_control == "2") {
                $this->service->prev($request);
            }
        }
        $result = redirect()->route('claim.detail', ['id' => $request->id]);
        $this->end_log();
        return $result;
    }

    /*
     * 請求照会(明細別) 押印済み業者ファイルアップロード削除
    */
    public function receive_delete(Request $request) {
        $this->start_log();
        if (!$this->service->receive_delete($request)) {
            // エラーメッセージ
        } else {
            // 差し戻し
            if ($request->delete_control == "1") {
                $this->service->remand($request);
                // 取り下げ
            } else if ($request->delete_control == "2") {
                $this->service->prev($request);
            }
        }
        $result = redirect()->route('claim.detail', ['id' => $request->id]);
        $this->end_log();
        return $result;
    }




    /*
     * 請求明細削除
     */
    public function delete_detail(Request $request) {
        $this->start_log();
        $ret = $this->service->delete_detail($request);
        if (empty($ret)) {
            $result = redirect()->route('claim.trader', ['id' => $request->head_id, 'page' => $request->page]);
        } else {
            $result = redirect()->route('claim.index', ['page' => $request->page]);
        }
        $this->end_log();
        return $result;
    }

    /**
     * 請求データ検索画面
     */
    public function search(Request $request) {
        $this->start_log();
        $user = Auth::user();
        $kengen = $user->getUserKengen($user->id);
        if ($kengen["請求管理メニュー表示"]->privilege_value != 'true') {
            // TODO 誘導画面への遷移
            //  return view("誘導画面への参照");
        }

        $list = $this->service->getBelongUserGroups($user);

        $this->setViewData([
            'conditions'   => ['user_group' => null, 'past_month' => null, 'supply' => null, 'trader' => null, 'maker' => null],
            'user_groups'  => $list,
            'past_dates'   => $this->service->getPast3Year(),
            'supplies'     => $this->service->getSupplies(collect($list)->pluck('user_group_id')),
            'traders'      => $this->service->getGroupTraderListByUser($user),
            'makers'       => $this->service->getMakers(collect($list)->pluck('user_group_id')),
            'message'      => $request->session()->get('message', ''),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'kengen'       => $kengen
        ]);
        $result = view(\Route::currentRouteName(),  $this->getViewArray());
        $this->end_log();
        return $result;
    }

    /**
     * 請求データPDF出力
     */
    public function export(Request $request) {
        $this->start_log();
        ini_set('memory_limit', '1G');
        $output_datetime = Carbon::now();
        $user = Auth::user();
        if (empty($request->output_type)) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . '出力帳票区分の指定していません。');
            $result = redirect()->route('claim.search')->withInput();
            $this->end_log();
            return $result;
        }
        $title = ClaimService::OUTPUT_TYPE_ARRARY[(int)$request->output_type];
        $output_type_name = (int)$request->output_type === ClaimService::TOTAL_TABLE_BY_TRADER ?
            ClaimService::TITLE_TRADER_NAME : ClaimService::TITLE_MAKER_NAME;
        $result = $this->service->getInvoiceData($request, $user);
        if (empty($result)) {
            $result = redirect()->route('claim.search')->withInput();
            $this->end_log();
            return $result;
        }

        $pdf = PDF::loadView('claim/invoice', compact('output_type_name', 'result', 'output_datetime', 'title'));
        $pdf->setPaper('A4', 'portrait');
        $fname = mb_convert_encoding($title . $output_datetime->format('Y年m月d日') . '.pdf', 'SJIS-win', 'UTF-8');

        $result = $pdf->download($fname);
        $this->end_log();
        return $result;
    }

    /**
     *  請求データPDF出力SQL
     */
    public function export_sql_download7(Request $request)
    {
        $this->start_log();

        ini_set('memory_limit', '1G');
        $user = Auth::user();
        $output_datetime = Carbon::now();
        $user = Auth::user();
        if (empty($request->output_type)) {
            $request->session()->flash('errorMessage', '処理に失敗しました' . '出力帳票区分の指定していません。');
            $result = redirect()->route('claim.search')->withInput();
            $this->end_log();
            return $result;
        }
        $title = ClaimService::OUTPUT_TYPE_ARRARY[(int)$request->output_type];
        $output_type_name = (int)$request->output_type === ClaimService::TOTAL_TABLE_BY_TRADER ?
            ClaimService::TITLE_TRADER_NAME : ClaimService::TITLE_MAKER_NAME;
        //TODO
        $data = $this->service->getInvoiceDataForSQL($request, $user);
        $sql_data = array();
        if (!empty($data)) {
            $sql_data = str_replace("\"", "", $data);
        }

        $stream = fopen('php://temp', 'r+b');
        foreach ($sql_data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv_temp = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = str_replace('"', '', $csv_temp);
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . urlencode('請求データPDF出力用SQL_') . date('YmdHis') . '.txt',
        );
        $result = Response::make($csv, 200, $headers);
        $this->end_log();
        return $result;
    }
}
