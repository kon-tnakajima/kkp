<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @type
 *     'honbu'
 *     'shisetsu'
 * @key
 *     'optional_key1'
 *     'optional_key2'
 *     'optional_key3'
 *     'optional_key4'
 *     'optional_key5'
 *     'optional_key6'
 *     'optional_key7'
 *     'optional_key8'
 * @detail
 *     $detailを指定すること
 * @search_list
 *     $search_listを指定すること
 */
$flag_name = 'fm_' . $type . '_' . $key . '_is_search_disp';
$label_name = 'fm_' . $type . '_' . $key . '_label';
$value_name = 'fm_' . $type . '_' . $key;
$combo_name = 'combo_' . $key;
?>
@if ($detail->$flag_name == 'true')
<label>
    <span>{{ $detail->$label_name }}&nbsp;</span>
    <span>
    @if ($type == 'shisetsu' && $detail->edit_fm_optional_key > 0)
        <input class="form-control" type="search" data-search-item="{{ $key }}" name="{{ $key }}" id="{{ $key }}" list="{{ $combo_name }}" value="{{ $detail->$value_name }}" placeholder="">
        @if (array_key_exists($key, $search_list))
        <datalist id="{{ $combo_name }}">
            @foreach($search_list[$key] as $value2)
            <option value="{{ $value2->value }}">{{ $value2->value }}</option>
            @endforeach
        </datalist>
        @endif
    @else
        {{ $detail->$value_name }}
    @endif
    </span>
</label>
@endif