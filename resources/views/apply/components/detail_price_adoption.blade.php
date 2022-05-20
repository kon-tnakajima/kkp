<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @type
 *     'purchase_apply'：仕入
 *     'purchase_requested'：要望仕入
 *     'purchase_estimated'：見積仕入
 *     'sales_apply'：納入
 * @require_flag
 *     必須ラベルを表示するかどうか
 * @decimal_point
 *     小数点以下何位までで四捨五入するか
 * @detail
 *     $detailを指定すること
 * @errors
 *     $errorsを指定すること
 */
list($waku_type, $sub_type) = explode("_", $type);
$price_name = 'pa_' . $type . '_price';
$check_name = $price_name . '_check';
$edit_kengen = 'edit_' . $price_name;
?>
<th>{{ $title }}@if ($detail->$edit_kengen > 0 && $require_flag)<span class="badge badge-danger ml-1">必須</span>
@elseif ($detail->$edit_kengen > 0)<span class="badge badge-success ml-1 optional-input">任意</span>
@endif</th>
<td>
@if ($detail->$edit_kengen > 0)
    <div class="relative-box">
        <div class="error-tip">
            <div class="error-tip-inner">{{ $errors->first($price_name) }}</div>
        </div>
        @include('layouts.input', ['parent' => '[apply.detail]', 'name' => $price_name, 'value' => isset($detail->$price_name) ? number_format($detail->$price_name, $decimal_point, '', '') : '', 'type' => 'tel', 'class' => $price_name . ($require_flag ? ' require' : ''). ' price', 'other_attr' => 'data-validate="empty zero"'])
    </div>
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '1'])
@else
    <div class="td-value-box">
    @if (isset($detail->$price_name))
        {{ number_format($detail->$price_name, $decimal_point) }} 円
    @endif
    </div>
    {{-- @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $price_name, 'value' => $detail->$price_name]) --}}
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '0'])
@endif
</td>
