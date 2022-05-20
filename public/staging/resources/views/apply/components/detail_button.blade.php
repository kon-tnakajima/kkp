<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @type
 *     'forward'
 *     'remand'
 *     'reject'
 *     'forward'
 * @detail
 *     $detailを指定すること
 */
$status_name = 'status_'. $type;
$color_code_name = $status_name . '_color_code';
$label_name = $status_name . '_label';
$target = '#modal-' . $type . $detail->id;
$url_name = 'url_' . $type;
?>
@if ($detail->$status_name > 0)
    {{-- <button class="ml5 btn apply" 
    style="background-color: {{ $detail->$color_code_name }}; color: white;" 
    type="button" 
    data-toggle="modal" 
    data-target="{{ $target }}">{{ $detail->$label_name }}</button>
@endif --}}


    <button
        class="apply-detail-action-btn"
        style="background-color: {{ $detail->$color_code_name }}; color: white;" 
        type="submit"
        onClick="setFormInfo($(this), '{{ $detail->$url_name }}', 'GET', '{{ $detail->$label_name }}', '{{ $apply_control }}');"
    >
        {{ $detail->$label_name }}
    </button>

@endif