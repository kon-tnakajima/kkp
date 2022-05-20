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
     * 後発品詳細区分テキスト取得
     */
    function getGenericProductDetailDivision($value)
    {
        $rtn = "";
        //if(!empty($value) && array_key_exists($value,Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_STR)){
        if(!is_null($value) && $value != "" && array_key_exists($value, Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_STR)){
            $rtn = Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_STR[$value];
        }
        return $rtn;
//        return ($value) ? Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_STR[$value] : "";
    }

    /*
     * ターゲットの申請か判断する
     */
    function isTargetApply($apply)
    {
        $rtn = false;

        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        /*
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
        */

        //採用中止日が過去なら何もできない
        $today = date('Y/m/d');

        if ($apply->facility_price_end_date < $today) {
            // 再申請を追加した為、期限切れでも申請対象の色を変更
        	if ($apply->status > TASK::STATUS_UNAPPLIED && $apply->status_forward > 0 && $apply->status != TASK::STATUS_ADOPTABLE) {
        		$rtn = true;
        	} else {
        		$rtn = false;
        	}
        } else {
	        //採用可は色を変えない
	        if ($apply->status > TASK::STATUS_UNAPPLIED && $apply->status_forward > 0 && $apply->status != TASK::STATUS_ADOPTABLE) {
	        	$rtn = true;
	        }
        }
        return $rtn;
    }

    /*
     * 先発後発品区分リスト
     */
    function getGeneric_product_detail_devision()
    {
    	$generic = array(
                Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_NOT_APPLICABLE => '空白',
    			Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_PATENT=>'特許品',
    			Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_LONG_TERM=>'長期品',
    			Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC=>'後発品',
    			Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_ORIGINAL=>'☆先発',
    			Medicine::GENERIC_PRODUCT_DETAIL_DEVISION_GENERIC2=>'★後発'
    	);

    	return $generic;
    }

    /*
     * 中止理由区分リスト
     */
    function getDiscontinuing_division()
    {
    	$division = array(
    			Medicine::DISCONTINUING_DIVISION_NONE=>'非該当製品',
    			Medicine::DISCONTINUING_DIVISION_STOP=>'中止品',
    			Medicine::DISCONTINUING_DIVISION_MODIFER=>'変更品');

    	return $division;
    }

    /*
     * 剤型区分テキスト取得
     */
    function getDiscontinuingDivisionText($value)
    {
    	$dosage = getDiscontinuing_division();
    	return ($value) ? $dosage[$value] : "非該当製品";
    }


}
