<?php
/**
 * 共通コンポーネント
 * コメントを表示する
 *
 * @title
 *     項目名
 * @id
 *     tableタグのid属性
 * @list
 *     コメントのリスト
 *     @date
 *         日付
 *     @who
 *         発言者
 *     @text
 *         コメントの内容
 */
?>
<h3 class="title">{{ $title }}</h3>
<div class="send-comment-block d-print-none">
    <?php /* TODO */?>
    @include('layouts.textarea', ['parent' => 'common', 'name' => '', 'value' => '', 'class' => '', 'other_attr' => ''])
</div>
<table class="comment-container comment-table" id="{{ $id }}">
    <tbody>
@foreach($list as $comment)
        <tr>
            <td class="date">{{ $comment->date }}</td>
            <td class="who">{{ $comment->who }}</td>
            <td class="comment">{!! nl2br(e($comment->text)) !!}</td>
        </tr>
@endforeach
    </tbody>
</table>