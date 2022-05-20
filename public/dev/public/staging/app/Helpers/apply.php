<?php

namespace App\Helpers\Apply
{
    use App\Model\Task;
    use App\Model\Medicine;
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
     * 剤型区分リスト
     */
    function getDosage_type_division()
    {
        $dosage = array(
            Medicine::DOSAGE_TYPE_INTERNAL=>'内服',
            Medicine::DOSAGE_TYPE_EXTERNAL=>'外用',
            Medicine::DOSAGE_TYPE_INJECTION=>'注射',
            Medicine::DOSAGE_TYPE_DENTISTRY=>'歯科',
            Medicine::DOSAGE_TYPE_OTHER=>'その他');

        return $dosage;
    }

    /*
     * 剤型区分テキスト取得
     */
    function getDosageTypeDivisionText($value)
    {
        $dosage = getDosage_type_division();
        return ($value) ? $dosage[$value] : "";
    }

    /*
     * オーナー区分テキスト取得
     */
    function getOwnerClassification($value)
    {
        return ($value) ? Medicine::OWNER_CLASSIFICATION_STR[$value] : "";
    }
    
    /*
     * ターゲットの申請か判断する
     */
    function isTargetApply($apply, $facility_id)
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
            if ($apply->status === TASK::STATUS_APPLYING && $apply->facility_id == $facility_id) {
                $rtn = false;
            }

            // 採用可は「採用」
            if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $facility_id) {
                $rtn = true;
            }
            // 交渉中は催促
            if ($apply->status === TASK::STATUS_NEGOTIATING && $apply->facility_id == $facility_id) {
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
