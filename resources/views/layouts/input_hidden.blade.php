<?php
/**
 * 共通コンポーネント
 *
 * @parent
 * @name
 * @value
 */
// パラメータ一覧をログに出すためのラッピング
logger($parent . '::(input.hidden):: ' . $name . ' = "' . $value . '"');
?>
<input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}">