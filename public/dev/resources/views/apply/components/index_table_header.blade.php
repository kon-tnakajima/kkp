<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @purchase_waku_kengen
 * @sales_waku_kengen
 */
?>
<tr>
    <th class="no col-w-2"><span class="clip-bord-target">No</span></th>
    <th class="sales-info">
        <span class="clip-bord-target">GS1販売</span><br>
        <span class="clip-bord-target" style="display: none;">販売区分</span>
        <span class="clip-bord-target">販売メーカー</span>
    </th>
    <th class="medicine-info">
        <span class="clip-bord-target">商品名</span><br>
        <span class="clip-bord-target">規格容量</span>
    </th>
@if (!$bunkaren_waku_kengen)
    <th class="coefficient"><span class="clip-bord-target">包装薬<br>価係数</span></th>
    <th class="medicine-price"><span class="clip-bord-target">単位<br>薬価</span></th>
@endif
    <th class="pack-unit-price"><span class="clip-bord-target">包装<br>薬価</span></th>
@if ($purchase_waku_kengen)
    <th class="purchase-requested-price"><span class="clip-bord-target">要望仕入<br>単価</span></th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-requested-nebiki-rate"><span class="clip-bord-target">要望仕入<br>値引率</span></th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-estimated-price"><span class="clip-bord-target">見積仕入<br>単価</span></th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-estimated-nebiki-rate"><span class="clip-bord-target">見積仕入<br>値引率</span></th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-price"><span class="clip-bord-target">仕入<br>単価</span></th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-nebiki-rate"><span class="clip-bord-target">仕入<br>値引率</span></th>
@endif
@if ($sales_waku_kengen)
    <th class="sales-price"><span class="clip-bord-target">納入<br>単価</span></th>
@endif
@if ($sales_waku_kengen)
    <th class="sales-rate"><span class="clip-bord-target">納入<br>値引率</span></th>
@endif
    <!-- <th class="trader-facillity">施設<br>業者</th> -->
    <th class="trader-facillity"><span class="clip-bord-target">施設</span></th>
    <th class="trader-facillity"><span class="clip-bord-target">業者</span></th>
    <th class="status-date">
        <span class="clip-bord-target">ステータス</span><br>
        <span class="clip-bord-target">申請日</span>
    </th>
    <th class="apply-task">タスク</th>
</tr>