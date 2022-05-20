<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @detail
 *     $detailを指定すること
 */
?>
<div class="button-group mr10" style="margin-left: auto;">
    @include('apply.components.detail_button', ['type' => 'forward',  'detail' => $detail, 'apply_control' => 1])
    @include('apply.components.detail_button', ['type' => 'withdraw', 'detail' => $detail, 'apply_control' => 1])
    @include('apply.components.detail_button', ['type' => 'remand',   'detail' => $detail, 'apply_control' => 2])
    @include('apply.components.detail_button', ['type' => 'reject',   'detail' => $detail, 'apply_control' => 3])
@if ($detail->page_update_kengen > 0)
    {{-- <button class="ml20 mr20 btn btn-primary btn-lg" type="button" data-toggle="modal" data-target="#modal-action">
        更新する
    </button> --}}
    <button
      class="apply-detail-action-btn"
      style="background-color: #390; color: white;" 
      type="submit"
      onClick="ignoreDuplicateClick($(this));"
    >更新する</button>
@endif
    <a class="btn btn-default btn-lg" href="{{ route('apply.index') }}?page={{ $detail->page }}&page_count={{ $page_count }}"><i class="fas fa-chevron-left mr10"></i>一覧に戻る</a>
</div>
