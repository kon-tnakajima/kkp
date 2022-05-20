<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @purchase_waku_kengen
 * @sales_waku_kengen
 */
?>
<tr>
    <th class="no col-w-2">No</th>
    <th class="sales-info">GS1販売<br>販売メーカー</th>
    <th class="medicine-info">商品名<br>規格容量</th>
@if (!$bunkaren_waku_kengen)
    <th class="coefficient">包装薬<br>価係数</th>
    <th class="medicine-price">単位<br>薬価</th>
@endif
    <th class="pack-unit-price">包装<br>薬価</th>
@if ($purchase_waku_kengen)
    <th class="purchase-requested-price">要望<br>仕入単価</th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-requested-nebiki-rate">要望仕入<br>値引率</th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-estimated-price">見積<br>仕入単価</th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-estimated-nebiki-rate">見積仕入<br>値引率</th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-price">仕入単価</th>
@endif
@if ($purchase_waku_kengen)
    <th class="purchase-nebiki-rate">仕入<br>値引率</th>
@endif
@if ($sales_waku_kengen)
    <th class="sales-price">納入単価</th>
@endif
@if ($sales_waku_kengen)
    <th class="sales-rate">納入<br>値引率</th>
@endif
    <!-- <th class="trader-facillity">施設<br>業者</th> -->
    <th class="trader-facillity">施設</th>
    <th class="trader-facillity">業者</th>
    <th class="status-date">ステータス<br>申請日</th>
    <th class="task">タスク</th>
@if ($bunkaren_waku_kengen)
    <th class="bunkaren-status-update">quick<br>タスク</th>
@endif
</tr>