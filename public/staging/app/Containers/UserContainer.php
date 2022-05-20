<?php
declare(strict_types=1);
namespace App\Containers;

use App\Model\UserGroup as UserGroupModel;

class UserContainer
{
    protected $userGroup;

    public function __construct(UserGroupModel $userGroup)
    {
        $this->userGroup = $userGroup;
    }

    /**
     * ユーザグループ取得
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * 施設名取得
     */
    public function getGroupTypeName(): string
    {
        return $this->userGroup->group_type;
    }

    /**
     * ユーザグループID取得
     */
    public function getUserGroupId(): int
    {
        return $this->userGroup->id;
    }
}
