<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @type
 *     'forward'
 *     'remand'
 *     'reject'
 *     'forward'
 * @apply_control
 * @detail
 *     $detailを指定すること
 */
$root_id = 'modal-' . $type . $detail->id;
$label_name = 'status_' . $type . '_label';
$url_name = 'url_' . $type;
$color_code_name = 'status_' . $type . '_color_code';
$parent = '[apply.detail][#' . $root_id . ']';
?>
<div class="fade modal" id="{{ $root_id }}" role="dialog" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">採用申請</h5><button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="tac text">この薬品を{{ $detail->$label_name }}してよろしいでしょうか？</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">閉じる</button>
                <form style="margin: 0;" action="{{ $detail->$url_name }}" name="apply-action{{ $detail->id }}" id="apply-action{{ $detail->id }}">
                    <button
                        class="btn2"
                        style="background-color: {{ $detail->$color_code_name }}; color: white;" 
                        type="submit"
                        data-form-action="{{ $detail->$url_name }}"
                        data-form-method="get"
                        data-form="#apply-edit"
                        onClick="setFormValue('{{ $detail->$label_name }}', '{{ $apply_control }}');"
                    >
                        <div class="fas fa-pen mr10"></div>{{ $detail->$label_name }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>