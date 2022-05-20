<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @groupName
 * @groupArr
 */
?>
<div class="hospital-group flex-row-left-top">
    <div class="hospital-group-label flex-row-left-center zero" onclick="onClickHospitalGroupLabel(this); return false;">{{ $groupName }}</div>
    <div class="contents flex-row-left-center flex-wrap">
@foreach($groupArr as $group)
        <label class="generic normal">
            <input
                class="custom-control-input validate input-checkbox"
                id="hospital_{{ $group->user_group_id }}"
                name="user_group[]"
                data-search-item="user_group[]"
                data-search-type="checkbox"
                value="{{ $group->user_group_id }}"
                type="checkbox"
@if(in_array($group->user_group_id, $viewdata->get('conditions')['user_group']))
                checked
@endif
            >
            <span class="deco"></span>
            <span class="text" title="{{ $group->user_group_name }}">{{ $group->user_group_name }}</span>
        </label>
@endforeach
    </div>
</div>