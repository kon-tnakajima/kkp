<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class Valiation extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'price_adoption_id',
        'current_task_id',
        'next_task_id',
        'deleter',
        'creater',
        'updater',

    ];

    /*
     * boot
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * price_adoption_idからタスクを取得
     */
    public function getByPriceAdoptionID($id)
    {
        return $this->where('price_adoption_id', $id)->first();
    }

}
