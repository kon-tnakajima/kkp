<?php
namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class AuthorObserver
{
    const DEFAULT_ID = 0;
    public function creating(Model $model){
        if(empty($model->creater)) $model->creater = $this->getID();
    }
    public function updating(Model $model){
        if (is_null($model->deleter)) {
            if(empty($model->updater)) $model->updater = $this->getID();
        }
    }
    public function saving(Model $model){
        if (is_null($model->deleter)) {
            if(empty($model->updater)) $model->updater = $this->getID();
        }
    }
    public function deleting(Model $model){
        $model->deleter = $this->getID();
        $model->timestamps = false;
        $model->save();
    }
    protected function getID() {
        return (\Auth::check()) ? \Auth::user()->id : self::DEFAULT_ID;
    }
}
