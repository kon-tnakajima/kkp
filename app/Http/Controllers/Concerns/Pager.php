<?php
declare(strict_types=1);
namespace App\Http\Controllers\Concerns;

trait Pager 
{
    /*
     * Pagerの情報を取得
     */
    public function getPager($list, $display_count)
    {
        $half = floor($display_count / 2);

        $pager = new \stdClass();
        $pager->current = $list->currentPage();
        $pager->max = $list->lastPage();
        $pager->display_count = $display_count;

        // ページ数がdisplay_count以下
        if ($pager->max <= $display_count) {
            $pager->first = 1;
            $pager->last = $pager->max;
        } else if ($pager->current - $half < 0) {
            $pager->first =  1;
            $pager->last =  $display_count;
        } else if ($pager->current + $half > $pager->max){
            $pager->last = $pager->max;
            $pager->first = $pager->last - $display_count + 1; 
        } else {
            $pager->first = $pager->current - $half;
            $pager->last = $pager->current + $half;
        }
        return $pager;
    }

}
