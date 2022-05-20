<?php

namespace App\Services;

use App\Model\PriceAdoption;
use Illuminate\Http\Request;

/**
 * 採用機能の項目チェック
 * @author 文化連
 */
class ApplyChecker {
    public $check_result = false;
    private $request;
    private $priceAdoption;
    private $checkDict;

    /**
     * コンストラクタ
     */
    public function __construct($request, $priceAdoption, $status_type) {
        $this->request = $request;
        $this->priceAdoption = $priceAdoption;

        $status_name = 'status_' . $status_type;
        $this->checkDict = array(
            'purchase_user_group_id' => $request->$status_name >= 40,
            'start_date' => $request->$status_name >= 40,
            'purchase_estimated_price' => $request->status < 50 && $request->$status_name == 50,
            'purchase_price' => $request->$status_name >= 60,
            'sales_price' => $request->$status_name >= 80
        );
    }

    /**
     * 採用申請一覧項目チェック(共通処理)
     * @param String $pa_name priceAdoptionのカラム名
     * @param String $label ログ出力上のラベル文言
     * @return \App\Services\ApplyChecker
     */
    private function indexCheck($pa_name, $label) {
        $check_name = $this->checkDict[$pa_name];
        $logPrefix = '採用申請一覧項目チェック - ' . $label . ': ';

        // 既に条件に引っかかっている
        if ($this->check_result) {
            logger($logPrefix . '既に条件に引っかかっているためスキップ');
            return $this;
        }

        // ステータス条件に合わないのでチェックしない
        if (!$check_name) {
            logger($logPrefix . 'ステータス不一致のためチェックしない');
            return $this;
        }
        logger($this->priceAdoption->$pa_name . '一覧のPAのnullﾁｪｯｸする値');
        if (empty($this->priceAdoption->$pa_name)) $this->check_result = true;
        logger($logPrefix . $this->check_result);

        return $this;
    }

    /**
     * 採用申請一覧項目チェック - 業者チェック
     * @return \App\Services\ApplyChecker
     */
    public function indexTraderCheck() {
        return $this->indexCheck('purchase_user_group_id', '業者チェック');
    }

    /**
     * 採用申請一覧項目チェック - 申請単価有効開始日チェック
     * @return \App\Services\ApplyChecker
     */
    public function indexStartDateCheck() {
        return $this->indexCheck('start_date', '申請単価有効開始日チェック');
    }

    /**
     * 採用申請一覧項目チェック - 見積仕入チェック
     * @return \App\Services\ApplyChecker
     */
    public function indexEstimatedCheck() {
        return $this->indexCheck('purchase_estimated_price', '見積仕入チェック');
    }

    /**
     * 採用申請一覧項目チェック - 仕入チェック
     * @return \App\Services\ApplyChecker
     */
    public function indexPurchaseCheck() {
        return $this->indexCheck('purchase_price', '仕入チェック');
    }

    /**
     * 採用申請一覧項目チェック - 納入チェック
     * @return \App\Services\ApplyChecker
     */
    public function indexSalesCheck() {
        return $this->indexCheck('sales_price', '納入チェック');
    }

    /**
     * 採用申請詳細項目チェック(共通処理)
     * @param String $pa_name priceAdoptionのカラム名
     * @param String $req_name requestのパラメータ名
     * @param String $label ログ出力上のラベル文言
     * @return \App\Services\ApplyChecker
     */
    private function detailCheck($pa_name, $req_name, $label) {
        $check_name = $this->checkDict[$pa_name];
        // $view_check_name = $pa_name . '_check';
        $view_check_name = $req_name . '_check';
        $logPrefix = '採用申請詳細項目チェック - ' . $label . ': ';
        
        // 既に条件に引っかかっている
        if ($this->check_result) {
            logger($logPrefix . '既に条件に引っかかっているためスキップ');
            return $this;
        }

        // ステータス条件に合わないのでチェックしない
        if (!$check_name) {
            logger($logPrefix . 'ステータス不一致のためチェックしない');
            return $this;
        }
        // logger($this->request->$req_name . '詳細の入力値のnullﾁｪｯｸする値');
        // logger($this->priceAdoption->$pa_name . '詳細のPAのnullﾁｪｯｸする値');
        // logger('view_check_name ' .$view_check_name);
        if ($this->request->$view_check_name > 0) {
            // 画面で入力された値をチェックする
            if (empty($this->request->$req_name)) $this->check_result = true;
        } else {
            // DBの値をチェックする（流し込みパターン）
            if (empty($this->priceAdoption->$pa_name)) $this->check_result = true;
        }
        logger($logPrefix . $this->check_result);
        return $this;
    }

    /**
     * 採用申請詳細項目チェック - 業者チェック
     * @return \App\Services\ApplyChecker
     */
    public function detailTraderCheck() {
        return $this->detailCheck('purchase_user_group_id', 'pa_purchase_user_group_id', '業者チェック');
    }

    /**
     * 採用申請詳細項目チェック - 業者チェック
     * @return \App\Services\ApplyChecker
     */
    public function detailStartDateCheck() {
        return $this->detailCheck('start_date', 'pa_start_date', '申請単価有効開始日チェック');
    }


    /**
     * 採用申請詳細項目チェック - 見積仕入チェック
     * @return \App\Services\ApplyChecker
     */
    public function detailEstimatedCheck() {
        return $this->detailCheck('purchase_estimated_price', 'pa_purchase_estimated_price', '見積仕入チェック');
    }

    /**
     * 採用申請詳細項目チェック - 仕入チェック
     * @return \App\Services\ApplyChecker
     */
    public function detailPurchaseCheck() {
        return $this->detailCheck('purchase_price', 'pa_purchase_apply_price', '仕入チェック');
    }

    /**
     * 採用申請詳細項目チェック - 納入チェック
     * @return \App\Services\ApplyChecker
     */
    public function detailSalesCheck() {
        return $this->detailCheck('sales_price', 'pa_sales_apply_price', '納入チェック');
    }
}
