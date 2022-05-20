<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @viewdata
 * @count
 */
?>
<label
    class="btn btn-default btn-search-count @if($viewdata->get('page_count') == $count) active @endif"
    tabindex="0"
    data-search-count="{{ $count }}"
>
    {{ $count }}件表示
</label>