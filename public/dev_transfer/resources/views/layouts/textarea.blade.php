<?php
/**
 * 共通コンポーネント
 *
 * @parent
 * @name
 * @value
 * @class
 * @other_attr
 */
// パラメータ一覧をログに出すためのラッピング
logger($parent . '::(textarea):: ' . $name . ' = "' . $value . '"');
?>
<textarea class="form-control {{ $class }}" name="{{ $name }}" id="{{ $name }}" {{ $other_attr }}>{{ $value }}</textarea>
