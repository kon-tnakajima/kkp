<?php
/**
 * 共通コンポーネント
 *
 * @parent
 * @name
 * @value
 * @type
 * @class
 * @other_attr
 */
// パラメータ一覧をログに出すためのラッピング
logger($parent . '::(input):: ' . $name . ' = "' . $value . '"');
?>
<input class="form-control {{ $class }}" type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}" {{ $other_attr }}>