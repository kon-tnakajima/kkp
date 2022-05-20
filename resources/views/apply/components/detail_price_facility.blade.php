<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @type
 *     'purchase'：仕入
 *     'sales'：売り
 * @require_flag
 *     必須ラベルを表示するかどうか
 * @detail
 *     $detailを指定すること
 */
$price_name = 'fp_' . $type . '_price';
$decimal_point = 0;
$check_name = $price_name . '_check';
?>
<th>{{ $title }}@if ($require_flag)<span class="badge badge-danger ml-1">必須</span>@endif</th>
<td>
@if ($detail->edit_facility_price_kengen > 0)
    <div class="relative-box">
        <div class="error-tip">
            <div class="error-tip-inner">{{ $errors->first($price_name) }}</div>
        </div>
        @include('layouts.input', ['parent' => '[apply.detail]', 'name' => $price_name, 'value' => isset($detail->$price_name) ? number_format($detail->$price_name, $decimal_point, '', '') : '', 'type' => 'tel', 'class' => $price_name. ' fp-edit', 'other_attr' => 'data-validate="empty zero"'])
        @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '1'])
    </div>
@else
    <div class="td-value-box">
    @if (isset($detail->$price_name))
        {{ number_format($detail->$price_name, $decimal_point) }} 円
    @endif
    </div>
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $price_name, 'value' => $detail->$price_name])
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '0'])
@endif
</td>