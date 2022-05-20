<?php
/*
 * 全Viewで使用するHelper関数
 */

/*
 * 文化連かどうか
 */
function isBunkaren()
{
    return (App\Model\Actor::isBunkaren(app('UserContainer')->getActorID())) ;
}

/*
 * 病院かどうか
 */
function isHospital()
{
    return (App\Model\Actor::isHospital(app('UserContainer')->getActorID())) ;
}

/*
 * 本部かどうか
 */
function isHeadQuqrters()
{
    return (App\Model\Actor::isHeadQuqrters(app('UserContainer')->getActorID())) ;
}

function diffTime($start) {

    if(empty($start)) return "";

    $startSec = strtotime($start);
    $endSec   = strtotime(date('Y-m-d H:i:s'));

    // 日時差を秒数で取得
    $dif = $startSec - $endSec;//指定日ー今日

    // 日付単位の差
    $dif_days = (strtotime(date("Y-m-d", $dif)) - strtotime("1970-01-01")) / 86400;
    // 時間単位の差
    $dif_hour = (int)floor($dif / 3600) + 1;
    // 分単位の差
    $dif_minute = (int)floor($dif / 60) + 1;
    // 時間単位の差
    $dif_second = (int)$dif % 60;

    $str = "";
    if($dif_days < 0){
        $dif_days = $dif_days * -1;
        $str = "{$dif_days}日前";
    }else if($dif_hour < 0){
        $dif_hour = ((int)$dif_hour) * -1;
        $str = "{$dif_hour}時間前";
    }else if($dif_minute < 0){
        $dif_minute = ((int)$dif_minute) * -1;
        $str = "{$dif_minute}分前";
    }else if($dif_second < 0){
        $dif_second = ((int)$dif_second) * -1;
        $str = "{$dif_second}秒前";
    }

    return $str;
}

/*
 * 入力値と指定値が同じ場合はselectedを返す
 */
function selected($input, $value) 
{
    return ($input == $value) ? 'selected' : '';
}

/*
 * 入力値と指定値が同じ場合はselectedを返す
 */
function checked($input, $value) 
{
    return ($input == $value) ? 'checked' : '';
}

/*
 * 本年度から過去３年を取得する
 */
function getFiscalYearHistory(){

    $today = date('Y/m/d');
    $start_date = '04/01';

    $start_year = date('Y').'/'.$start_date;
    if(strtotime($today) >= strtotime($start_year)){
      // 2019/4/20 >= 2019/04/01 = 2019
      $year = date('Y');
    }else{
      // 2019/3/20 < 2019/04/01 = 2018
      $year = date('Y') - 1;
    }

    $tag_str = array();
    for ($i = 0; $i < 3; $i++) {
        $tag_str[] = $year.'年度(4-3)';
        $year--;
    }

    return $tag_str;
  }

?>
