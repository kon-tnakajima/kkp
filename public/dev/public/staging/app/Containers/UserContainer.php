<?php

namespace App\Containers;

use App\Model\UserGroup as UserGroupModel;
use App\Model\Facility as FacilityModel;
class UserContainer
{
    protected $group;
    protected $facility;

    public function __construct(UserGroupModel $group,  FacilityModel $facility)
    {
        $this->group = $group;
        $this->facility = $facility;
    }

    /* 
     * 所属グループの取得
     */
    public function getGroup()
    {
        return $this->group;
    }

    /* 
     * 所属施設の取得
     */
    public function getFacility()
    {
        return $this->facility;
    }

    /*
     * アクターID取得
     */
    public function getActorID()
    {
        return $this->facility->actor_id;
    }
}
