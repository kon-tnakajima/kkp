<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    const ACTOR_HOSPITAL = 1;
    const ACTOR_HEADQUARTERS = 2;
    const ACTOR_BUNKAREN = 3;
    const ACTOR_TRADER = 4;

    /*
     * 文化連かどうかチェック
     */
    public static function isBunkaren($actor_id) 
    {
        return (self::ACTOR_BUNKAREN === $actor_id);
    }

    /*
     * 本部かどうかチェック
     */
    public static function isHeadQuqrters($actor_id) 
    {
        return (self::ACTOR_HEADQUARTERS === $actor_id);
    }

    /*
     * 病院かどうかチェック
     */
    public static function isHospital($actor_id) 
    {
        return (self::ACTOR_HOSPITAL === $actor_id);
    }

}
