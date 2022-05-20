<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Invoice;
use App\Model\InvoiceDetail;
use App\Observers\AuthorObserver;

class InvoiceDetailComment extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

}
