<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\Invoice;
use App\Model\InvoiceDetailComment;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;

class InvoiceDetail extends Model
{
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

    /*
     * 明細一覧
     */
    public function getListInvoiceDetail(Request $request, $count)
    {

        $query = $this->select('invoice_details.created_at' 
        ,'makers.name as maker_name'
        ,'medicines.name as medicine_name'
        ,'medicines.standard_unit'
        ,'pack_units.jan_code'
        ,'invoice_details.invoice_count'
        ,'invoice_details.sales_price'
        ,'invoice_details.sales_price_total'
        ,'invoice_detail_comments.comment'
        )
        ->leftJoin('invoices', 'invoice_details.invoice_id', '=', 'invoices.id')
        ->leftJoin('invoice_detail_comments', 'invoice_detail_comments.invoice_detail_id', '=', 'invoice_details.id')
        ->leftJoin('medicines', 'medicines.id', '=', 'invoice_details.medicine_id')
        ->leftJoin('pack_units', 'medicines.id', '=', 'pack_units.medicine_id')
        ->leftJoin('makers', 'medicines.selling_agency_code', '=', 'makers.id')
        ->where('invoices.id',$request->id);

        return $query->paginate($count);
    }

}
