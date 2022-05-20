<?php

namespace App\Model\Concerns;
use App\Model\UserGroup as UserGroupModel;

trait UserGroup
{
    /**
     * ユーザグループのグループ区分によりwhere句を変更
     */
    public function scopeGetUserGroupScope($query, UserGroupModel $userGroup, $columnName = 'user_group_id', $parent = false)
    {
        $ids = [];
        if ($userGroup->isBunkaren() === true)  {
            // 文化連以外
            $result = $userGroup->where('group_type','!=', \Config::get('const.bunkaren_name'))->get();
            foreach($result as $child) {
                // 業者以外
                if($child->group_type !== \Config::get('const.trader_name')){
                    $ids[] = $child->id;
                }
            }
        } else {
            // 自身が病院・本部の場合
            if ($parent === true) {
                // 親に関しては複数の可能性があるため。
                foreach($userGroup->parent() as $parent) {
                    $ids[] = $parent->id;
                }
            }
            foreach($userGroup->children() as $child) {
                $ids[] = $child->id;
            }
        }
        if (!empty($ids)) {
            $query->whereIn($columnName, $ids);
        }
        return $query;
    }
}
