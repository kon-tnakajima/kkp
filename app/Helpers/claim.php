<?php

namespace App\Helpers\Claim
{
    use App\Model\Task;
    use App\Model\TransactFileStorage;
    use App\Model\TransactDetail;

    const CLAIM_CSV_SEPARATE = "\t";

    /*
     * ステータスのバッチのクラス名取得
     */
    function getStatusBadgeClass($status)
    {
        switch($status) {
            case TASK::STATUS_APPLYING:
                return "applying";
            case TASK::STATUS_NEGOTIATING:
                return "negotiation";
            case TASK::STATUS_APPROVAL_WAITING:
                return "approval";
            case TASK::STATUS_UNCONFIRMED:
                return "unconfirmed";
            case TASK::STATUS_ADOPTABLE:
                return "adopt";
            case TASK::STATUS_DONE:
                return "adopted";
            default:
                return "not_adopted";

        }
    }

    /*
     * 売仕区分リスト
     */
    function getStockOrSaleDivision()
    {
        $division = array(
            TransactFileStorage::STOCK_OR_SALE_DIVISION_SALE=>'売上',
            TransactFileStorage::STOCK_OR_SALE_DIVISION_PURCHASE=>'仕入',
        );

        return $division;
    }

    /*
     * 売仕区分テキスト取得
     */
    function getStockOrSaleDivisionText($value)
    {
        $division = getStockOrSaleDivision();
        if (isset($division[$value])) {
        	return $division[$value];
        } else {
        	return "";
        }
        
        //return ($value) ? $division[$value] : "";
    }


    /*
     * 供給区分リスト
     */
    function getSupplyDivision()
    {
        $division = array(
            TransactFileStorage::SUPPLY_DIVISION_MEDICINE=>'薬品',
            TransactFileStorage::SUPPLY_DIVISION_MATERIAL=>'資材',
            TransactFileStorage::SUPPLY_DIVISION_REAGENT=>'試薬',
            TransactFileStorage::SUPPLY_DIVISION_INSTRUMENT=>'器械',
            TransactFileStorage::SUPPLY_DIVISION_MAINTENANCE=>'保守',
        );

        return $division;
    }

    /*
     * 供給区分テキスト取得
     */
    function getSupplyDivisionText($value)
    {
        $division = getSupplyDivision();
        return ($value) ? $division[$value] : "";
    }

    /*
     * 消費税率区分リスト
     */
    function getTaxDivision()
    {
        $division = array(
            TransactDetail::TAX_DIVISION_POPULAR=>'一般的な税率',
            TransactDetail::TAX_DIVISION_EXEMPTION=>'非課税',
            TransactDetail::TAX_DIVISION_CASHBACK=>'5%返品',
        );

        return $division;
    }

    /*
     * 消費税率区分テキスト取得
     */
    function getTaxDivisionText($value)
    {
        $division = getTaxDivision();

        if (isset($division[$value])) {
        	return $division[$value];
        } else {
        	return "";
        }
        //return ($value) ? $division[$value] : "";
    }

    /*
     * ターゲットの申請か判断する
     */
    function isTargetClaim($apply, $user_group_id)
    {
        $rtn = false;
        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        if (isHospital()) {
            // 未申請
            if ($apply->status === TASK::STATUS_UNAPPLIED || is_null($apply->status)) {
                $rtn = false;
            }

            // 申請中は「取り下げ」
            if ($apply->status === TASK::STATUS_APPLYING && $apply->sales_user_group_id == $user_group_id) {
                $rtn = false;
            }

            // 採用可は「採用」
            if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->sales_user_group_id == $user_group_id) {
                $rtn = true;
            }
            // 交渉中は催促
            if ($apply->status === TASK::STATUS_NEGOTIATING && $apply->sales_user_group_id == $user_group_id) {
                $rtn = false;
            }
        }
        // 本部の場合
        if (isHeadQuqrters()) {
            // 申請中は「許可」
            if ($apply->status === TASK::STATUS_APPLYING) {
                $rtn = true;
            }
            // 承認待は「採用承認」
            if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
                $rtn = true;
            }
            // 交渉中は「取り下げ」
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $rtn = false;
            }
        }

        // 文化連の場合
        if (isBunkaren()) {
            // 交渉中は「価格登録」
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $rtn = true;
            }
        }
        return $rtn;
    }
}
