<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @medicine_price
 *     薬価
 * @detail
 *     $detailを指定すること
 */
$check_name = $medicine_price . '_check';
?>
<th @if (!empty($htmltitle)) title = "{{ $htmltitle }}" @endif>{{ $title }}</th>
<td>
@if ($medicine_price === 'pa_basis_mediicine_price')

    <div class="td-value-box">
    @if ($detail->status >= 30)
        @if (isset($detail->$medicine_price))
            {{ number_format($detail->$medicine_price, 2) }} 円
        @endif
    @endif
    </div>
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '1'])


@else
    <div class="td-value-box">
    @if (isset($detail->$medicine_price))
        {{ number_format($detail->$medicine_price, 2) }} 円
    @endif
    </div>
    @include('layouts.input_hidden', ['parent' => '[apply.detail]', 'name' => $check_name, 'value' => '0'])
@endif
</td>