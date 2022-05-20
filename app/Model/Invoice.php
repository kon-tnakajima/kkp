<?php
declare(strict_types=1);
namespace App\Model;

use App\Model\Concerns\UserGroup as UserGroupTrait;
use App\Model\Concerns\Calc as CalcTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Model\UserGroup;
use App\Model\InvoiceDetail;
use App\Model\InvoiceDetailComment;
use App\Observers\AuthorObserver;

class Invoice extends Model
{
    use SoftDeletes;
    use UserGroupTrait;

    protected $guarded = ['id'];

    const DIVISION_SALE = 1;
    const DIVISION_PURCHASE = 2;

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * 採用一覧
     */
    public function getList(Request $request, UserGroup $userGroup, $count)
    {
        $query = $this->select(
            'invoices.id' 
            ,'invoices.invoice_date'
            ,'invoices.owner_classification'
            ,'invoices.medicine_price_total'
            ,'invoices.purchase_price_total'
            ,'invoices.status'
            ,'user_groups.name as facility_name'
            ,'invoices.created_at'
            ,'trader.name as trader_name'
        )
        ->leftJoin('user_groups as trader', 'trader.id', '=', 'invoices.trader_id')
        ->leftJoin('user_groups', 'invoices.user_group_id', '=', 'user_groups.id')
        ->getUserGroupScope($userGroup, 'invoices.user_group_id', true);
        return $query->paginate($count);
    }

    /*
     * 施設を取得
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
