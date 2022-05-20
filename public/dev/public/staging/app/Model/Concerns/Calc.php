<?php

namespace App\Model\Concerns;

trait Calc
{
    /* 
     * 単位薬価
     * TODO 仕様の確認
     */
    public function getUnitPrice($coefficient, $price)
    {
        if (intval($coefficient) === 0 || intval($price) === 0 ) {
            return 0;
        }
        return $price / $coefficient;
    }

    /* 
     * 単位納入価
     * TODO 仕様の確認
     */
    public function getUnitDeliveryPrice($coefficient, $price)
    {
        if (intval($coefficient) === 0 || intval($price) === 0 ) {
            return 0;
        }
        return $price / $coefficient;
    }

    /*
     * 値引率( 納入単価 / 包装薬価(小数点以下２位まで) 
     */
    public function getSalesRate($sales_price, $pack_unit_price)
    {
        if  (intval($sales_price) === 0 || intval($pack_unit_price) === 0 ) {
            return "";
        }
        //return round( ((float)$sales_price / (float)$pack_unit_price) * 100, 2);
        $result = number_format(round(((float)1 - (float)$sales_price / (float)$pack_unit_price) * 100, 2), 2);
        if($result < 0){
            $result = $result * -1;
            $result = "▲".$result;
        }
        $result .= "%";
        return $result;
    }
    
    /* 
     * 詳細画面の包装薬価
     * TODO 仕様の確認
     */
    public function getPackUnitPriceForDetail($coefficient, $price)
    {
        if (intval($coefficient) === 0 || intval($price) === 0 ) {
            return 0;
        }
        return number_format($price * $coefficient, 2);
    }
}
