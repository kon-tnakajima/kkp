<?php

namespace App\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Observers\AuthorObserver;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGroupRelation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 
        'user_group_id', 
        'deleter', 
        'creater', 
        'updater'
    ];
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
                
    }
}
