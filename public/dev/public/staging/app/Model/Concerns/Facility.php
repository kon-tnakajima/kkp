<?php

namespace App\Model\Concerns;
use App\Model\Facility as FacilityModel;

trait Facility
{
    /* 
     * 施設レベルによりwhere句を変更
     */
    public function scopeGetFacility($query, FacilityModel $facility, $column_name = 'facility_id', $parent = false)
    {
        // 病院
        if ($facility->isHospital() === true)  {
//            $query->where($column_name, '=', $facility->id);
            $ids = array();
            if ($parent === true) {
                $ids[] = $facility->parent()->id;
            }
            foreach($facility->parent()->children as $child) {
                $ids[] = $child->id;
            }
            $query->whereIn($column_name, $ids);
        }

        // 本部
        if ($facility->isHeadQuqrters() === true) {
            $ids = array();
            if ($parent === true) {
                $ids[] = $facility->id;
            }
            foreach($facility->children as $child) {
                $ids[] = $child->id;
            }
            $query->whereIn($column_name, $ids);
        }

        // 文化連
        if ($facility->isBunkaren() === true) {
            foreach($facility->where('actor_id','!=','3')->get() as $child) {
                if($child->actor_id != '4'){
                    $ids[] = $child->id;
                }
            }
            $query->whereIn($column_name, $ids);
        }

        return $query;
    }
}
