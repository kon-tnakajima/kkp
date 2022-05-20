<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;

class ActionNameStatusForward extends Model {
    use SoftDeletes;

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot() {
        parent::boot();
        self::observe(new AuthorObserver());
    }
}
