<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @label
 * @property
 * @require
 *     true: 必須を表示
 *     false: 必須を非表示
 * @errors
 *     $errorsを指定すること
 */
?>
<tr>
    <th onclick="$('#{{ $property }}').focus();">
        {{ $label }}
    </th>
    <td onclick="$('#{{ $property }}').focus();">
        @if ($require)<span class="badge badge-danger ml10 mr10">必須</span>@endif
    </td>
    <td>
        <div class="error-tip">
            <div class="error-tip-inner">{{ $errors->first($property) }}</div>
        </div>
        <div class="d-flex" style="height: 100%;">
            {{ $slot }}
        </div>
    </td>
</tr>