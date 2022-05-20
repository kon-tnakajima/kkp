<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @type
 *     'purchase'：(単価情報)仕入単価の
 *     'sales'：(単価情報)納入単価の
 * @detail
 *     $detailを指定すること
 * @medicine
 *     $medicineを指定すること
 */
$calc = $title == '掛率' ? 'getMarkUp' : 'getSalesRate';
$price_name = 'fp_' . $type . '_price';
?>
<th>{{ $title }}</th>
<td>
    <div class="td-value-box">
    @if (isset($detail->$price_name))
        {{ $medicine->$calc($detail->$price_name, $detail->mp_unit_price) }}
    @endif
    </div>
</td>