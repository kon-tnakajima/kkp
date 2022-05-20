<?php
/**
 * index.blade.phpの中からinclude参照で利用されるコンポーネント
 *
 * @hospitalGroups
 * @isDefault
 * @hospitalGroupsDefaultCount
 */
?>
<div class="check-box-container flex-column-left-center flex-wrap">
<?php
$hospitalGroupsCount = 0;
foreach($hospitalGroups as $groupName => $groupArr) {
    $hospitalGroupsCount++;
    if ($isDefault ? $hospitalGroupsCount <= $hospitalGroupsDefaultCount : $hospitalGroupsCount > $hospitalGroupsDefaultCount) {
?>
    @include('apply.components.index_hospital_group_check', ['groupName' => $groupName, 'groupArr' => $groupArr])
<?php
    }
}
?>
</div>