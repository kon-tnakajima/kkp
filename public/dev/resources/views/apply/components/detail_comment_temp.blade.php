<?php
/**
 * detail.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @title
 *     項目名
 * @comment_name
 *     $detailを指定すること
 * @detail
 *     $detailを指定すること
 */
$edit_kengen_name = 'edit_' . $comment_name;
$textarea_row = ceil(mb_strlen($detail->$comment_name)/35) > 3 ? ceil(mb_strlen($detail->$comment_name)/35) : 3 ;
?>
<div class="comment-container scroll-area flex-stretch">
    <div class="row-box flex-stretch">
        <!-- コメント -->
        <div class="form-group card-box type-4 comment" id="{{ $comment_name }}">
            <label class = "comment-title">{{ $title }}@if ($detail->$edit_kengen_name > 0)<span class="badge badge-success ml-1">任意</span>@endif</label>
@if ($detail->$edit_kengen_name > 0)
            @include('layouts.textarea', ['parent' => '[apply.detail]', 'name' => $comment_name, 'value' => $detail->$comment_name, 'class' => 'validate', 'other_attr' => 'rows='.$textarea_row ])
@else
            <label class="label-comment">{{ $detail->$comment_name }}</label>
@endif
        </div>
    </div>
</div>