<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @label
 * @value
 * @is_checked
 * @hide
 */
?>
<label>
    <input
        name="mode_radios"
        type="radio"
        value="{{ $value }}"
        {{ $is_checked ? ' checked' : '' }}
        data-target="#applies"
        data-hide="{{ $hide }}" 
        data-search-item="mode_radios"
        data-search-type="radio"
    >
    <span class="text">{{ $label }}</span>
</label>