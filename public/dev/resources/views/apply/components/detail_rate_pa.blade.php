<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @type
 *     'purchase'：(申請情報)仕入の
 *     'purchase_requested'：(申請情報)要望仕入の
 *     'purchase_estimated'：(申請情報)見積仕入の
 *     'sales'：(申請情報)納入の
 * @detail
 *     $detailを指定すること
 * @medicine
 *     $medicineを指定すること
 */
// list($waku_type) = explode("_", $type);
$calc = $title == '掛率' ? 'getMarkUp' : 'getSalesRate';
// '_'を含んでいるかどうかで接頭句を変える
$price_name = 'pa_' . $type . '_price';
$price_rate_name = $title == '掛率' ? $price_name . '_kakeritsu' : $price_name . '_nebikiritsu';
?>
<th>{{ $title }}</th>
<td>
    <div class="td-value-box" id="{{ $price_rate_name }}">
    @if (isset($detail->$price_name))
        {{ $medicine->$calc($detail->$price_name, $detail->pa_basis_mediicine_price) }}
    @endif
    </div>
</td>