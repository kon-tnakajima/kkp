<?php
declare(strict_types=1);
namespace App\Services;

use App\Model\Medicine;
use App\Model\MedicinePrice;
use App\Model\PackUnit;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Model\Valiation;
use App\Model\Facility;
use App\Model\FacilityMedicine;
use App\Model\FacilityPrice;
use App\Model\Maker;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Mail\AdoptionMail;
use Illuminate\Support\Facades\Mail;
use App\Model\Invoice;
use App\Model\InvoiceDetail;
use App\Model\InvoiceDetailComment;
use App\Model\UserGroup;
use App\Services\BaseService;

class PaymentService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;
    const MAIL_STR = [
        Task::STATUS_UNCLAIMED => '未登録',
        Task::STATUS_INVOICING => '登録中',
        Task::STATUS_CONFIRM => '確認待ち',
        Task::STATUS_INVOICED => '確認済',
        ];

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->medicine = new Medicine();
        $this->priceAdoption = new PriceAdoption();
        $this->valiation = new Valiation();
        $this->facilityMedicine = new FacilityMedicine();
        $this->facility = new Facility();
        $this->userGroup = new UserGroup();
        $this->maker = new Maker();
        $this->tasks = new Task();
        $this->facilityPrice = new FacilityPrice();
        $this->medicinePrice = new MedicinePrice();
        $this->packUnit = new PackUnit();

        $this->invoice = new Invoice();
        $this->invoiceDetail = new InvoiceDetail();
        $this->invoiceDetailComment = new InvoiceDetailComment();
        $this->user = new User();
    }

    /*
     * 採用申請一覧取得
     */
    public function getList(Request $request, $user)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list =  $this->invoice->getList($request, $user->userGroup(), $count);

        // リストにボタン情報付加
        if (!is_null($list)) {
            foreach($list as $key => $payment) {
                $list[$key]->button = $this->button($payment);
                $list[$key]->url = route('payment.detail',['id' => $payment->id ]);
            }
        }
        return $list;
    }

    /*
     * 請求詳細取得
     */
    public function getPaymentDetail(Request $request)
    {
        $detail = $this->invoice->find($request->id);
        $invoice_detail = $this->invoiceDetail->where('invoice_id', $request->id)->first();
        if(!empty($invoice_detail)){
            $detail->division = $invoice_detail->division;
        }
        //薬価履歴
        $detail->medicine_price_list = array();

        //登録者
        if ( !is_null( $detail->file_upload_user_id ) ) {
            $reg_user = $this->user->find($detail->file_upload_user_id);
            $detail->user_name = $reg_user->name;
        }

        //施設名
        $facility = $this->facility->find($detail->facility_id);
        if(!empty($facility)){
            $detail->facility_name = $facility->name;
        }else{
            $detail->facility_name = "";
        }

        //業者
        $trader = $this->getTrader($detail->trader_id);
        if(!empty($trader)){
            $detail->trader_name = $trader->name;
        }else{
            $detail->trader_name = "";
        }

        // リストにボタン情報付加
        $detail->button = $this->button($detail);
        $detail->buttonRemand = $this->buttonRemand($detail);
        $detail->flg = $request->flg;
        if(!empty($request->page)){
            $detail->page = $request->page;
        }

        return $detail;
    }

    /*
     * 標準薬品情報取得
     */
    public function getMedicine($id)
    {
        return $this->medicine->find($id);
    }

    /*
     * 検索フォームの施設チェックボックスに使用する施設一覧
     */
    public function getUserGroups($user)
    {
        return $this->userGroup->getUserGroups($user);
    }

    /*
     * 業者一覧
     */
    public function getTraders()
    {
        return $this->userGroup->where('group_type', \Config::get('const.trader_name'))->get();
    }

    /*
     * 業者を取得
     */
    public function getTrader($trader_id)
    {
        return $this->userGroup->where('id', $trader_id)->where('group_type', \Config::get('const.trader_name'))->first();
    }

    /*
     * 採用申請実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $user = Auth::user();

            $file_name = $request->file('file')->getClientOriginalName();
            $request->file('file')->storeAs('public/', $file_name);
//dd($request->file('file'));

            //請求情報の登録
            $this->invoice->facility_id = $user->facility->id;
            $this->invoice->invoice_date = $request->invoice_date;
            $this->invoice->status = Task::STATUS_INVOICING;
            $this->invoice->file_name = $file_name;
            $this->invoice->file_upload_user_id = $user->id;
            $this->invoice->owner_classification = $request->owner_classification;
            $this->invoice->save();

            //請求情報明細の登録
            //$this->invoiceDetail->invoice_id = $this->invoice->id;
            //$this->invoiceDetail->division = $request->division;
            //$this->invoiceDetail->save();

            //請求情報明細コメントの登録
            //$this->invoiceDetailComment->invoice_detail_id = $this->invoiceDetail->id;
            //$this->invoiceDetailComment->save();

            $this->makeValiation($this->invoice);

            // 送信先は本部へ送信is_claim_mail
            $mail_users = $user->facility->parent()->users()->isClaimMail()->get();

            foreach($mail_users as $mail_user) {
                $this->sendMail($mail_user->email, $priceAdoption);
            }

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
            \DB::rollBack();
            return false;
        }
    }

    /*
     * バリエーション作成
     */
    protected function makeValiation($invoice)
    {
        // valiationデータ作成
        $data = array();
        $valiation = $this->valiation->getByInvoiceID($invoice->id);

        $data['current_task_id'] = Task::getByStatusForPayment($invoice->status)->id;
        $data['next_task_id'] = Task::nextForPayment($data['current_task_id'])->id;

        if (is_null($valiation)) {
            $data['price_adoption_id'] = 0;
            $data['invoice_id'] = $invoice->id;
            $this->valiation->create($data);
        } else {
            $valiation->update($data);
        }

    }

    /*
     * 採用許可
     */
    public function confirm(Request $request)
    {
        try {
            \DB::beginTransaction();
            $this->next($request);
            $mail_users = $this->facility->bunkaren()->first()->users()->isClaimMail()->get();
            // 送信先は文化連
            foreach($mail_users as $mail_user) {
                $this->sendMail($mail_user->email, $pa);
            }
            \DB::commit();

        } catch (\PDOException $e){
            \DB::rollBack();
            return false;
        }
        return true;
    }

    /*
     * 価格確定
     */
    public function complete(Request $request)
    {
        try {
            \DB::beginTransaction();
            $this->next($request);
            $pa = $this->priceAdoption->find($request->id);
            $mail_users = $pa->facility->bunkaren()->first()->users()->isClaimMail()->get();
            // 送信先は文化連
            foreach($mail_users as $mail_user) {
                $this->sendMail($mail_user->email, $pa);
            }
            \DB::commit();

        } catch (\PDOException $e){
            \DB::rollBack();
            return false;
        }
        return true;
    }

    /*
     * 売買登録
     */
    public function import(Request $request)
    {
        // 下記いずれかがnullの場合、詳細にリダイレクト
        if ( is_null( $request->file('file') ) || empty( $request->file('file') ) ) {
            \Redirect::to(route('payment.detail', ['id' => $request->id]))->send();
            exit;
        }

        try {
            \DB::beginTransaction();
            $this->next($request);

            $user = Auth::user();

            $file_name = $request->file('file')->getClientOriginalName();

            $row = 1;
            if (($handle = fopen($request->file('file'), "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                    if($row > 1){
                        //請求情報明細の登録
                        $invoice = $this->invoice->find($request->id);
                        $this->invoiceDetail->invoice_id = $invoice->id;
                        $this->invoiceDetail->medicine_id = $data[0];
                        $this->invoiceDetail->division = $data[1];
                        $this->invoiceDetail->invoice_count = $data[2];
                        $this->invoiceDetail->tax_rate = $data[3];
                        $this->invoiceDetail->sales_price = $data[4];
                        $this->invoiceDetail->sales_price_total = $data[5];
                        $this->invoiceDetail->sales_tax = $data[6];
                        $this->invoiceDetail->purchase_price = $data[7];
                        $this->invoiceDetail->purchase_price_total = $data[8];
                        $this->invoiceDetail->purchase_tax = $data[9];
                        $this->invoiceDetail->save();

                        //請求情報明細コメントの登録
                        $this->invoiceDetailComment->invoice_detail_id = $this->invoiceDetail->id;
                        $this->invoiceDetailComment->comment = mb_convert_encoding($data[10], 'UTF-8', 'SJIS-win');
                        $this->invoiceDetailComment->save();
                    }
                    $row++;
                }
                fclose($handle);
            }

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            echo $e->getMessage();
echo $test;
            \DB::rollBack();
            return false;
        }

    }

    /*
     * next
     */
    public function next(Request $request)
    {
        try {
            // valation取得
            $valiation = $this->valiation->where('invoice_id', $request->id)->first();

            // invoice取得
            $invoice = $this->invoice->find($request->id);
            $next = Task::nextForPayment($valiation->current_task_id);

            $user = Auth::user();
            $invoice->deal_regist_user_id = $user->id;
            $invoice->status = $next->status; // 現在のステータスをセット
            if( !empty($request->comment) ){
                $invoice->comment = $request->comment;
            }
            $invoice->save();

            // valiationデータ更新
            $valiation->current_task_id = $valiation->next_task_id;
            $next = Task::nextForPayment($valiation->current_task_id);
            if (is_null($next)) {
                $valiation->next_task_id = 0;
            } else {
                $valiation->next_task_id = $next->id;
            }
            $valiation->save();

            return true;

        } catch (\PDOException $e){
            throw $e;
        }
    }
    /*
     * prev
     */
    public function prev(Request $request)
    {
        \DB::beginTransaction();
        try {
            // valation取得
            $valiation = $this->valiation->where('invoice_id', $request->id)->first();

            // invoice取得
            $invoice = $this->invoice->find($request->id);
            $prev = Task::prevForPayment($valiation->current_task_id);

            $user = Auth::user();
            $invoice->deal_regist_user_id = $user->id;
            $invoice->status = $prev->status; // 現在のステータスをセット
            if( !empty($request->comment) ){
                $invoice->comment = $request->comment;
            }else if( !empty($request->comment2) ){
                $invoice->comment = $request->comment2;
            }
            $invoice->save();

            // valiationデータ更新
            $valiation->next_task_id = $valiation->current_task_id;
            $prev = Task::prevForPayment($valiation->next_task_id);
            if (is_null($prev)) {
                $valiation->current_task_id = 0;
            } else {
                $valiation->current_task_id = $prev->id;
            }
            $valiation->save();

            \DB::commit();

            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 差し戻し処理
     */
    public function remand(Request $request)
    {

        \DB::beginTransaction();
        try {
            $pa = $this->priceAdoption->find($request->id);
            if (!$this->prev($request)) {
                return false;
            }
            //承認済み
            if( $pa->status == Task::STATUS_UNCONFIRMED ){
                //本部薬品を取得
                $hq_fm = $this->facilityMedicine->getData($pa->facility->parent()->id, $pa->medicine_id);
                $isDelete = true;
                foreach($pa->facility->parent()->children as $child) {
                    //配下の病院薬品があるか確認
                    $own_fm = $this->facilityMedicine->getData($child->id, $pa->medicine_id);
                    if( !empty($own_fm) ){
                        $isDelete = false;
                        break;
                    }
                }
                //本部薬品あり、病院薬品なし
                if( !empty($hq_fm) && $isDelete ){
                    $hq_fp = $this->facilityPrice->getData($hq_fm->id);
                    $hq_fp->delete();
                    $hq_fm->delete();
                }
            //承認待ちかつ新規登録
            }else if( $pa->status == Task::STATUS_APPROVAL_WAITING && !empty($pa->name) ){
                $pa->medicine_id = null;//標準薬品は空にする
                $pa->save();
            }

            \DB::commit();
            $request->session()->flash('message', '更新しました');

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }

        // 差し戻し先にメール
        switch($pa->status)
        {
        case Task::STATUS_APPLYING:
            // 病院にメール
            $mail_users = $pa->facility->users()->isClaimMail()->get();
            break;
        case Task::STATUS_NEGOTIATING:
        case Task::STATUS_UNCONFIRMED:
            // 本部にメール
            $mail_users = $pa->facility->parent()->users()->isClaimMail()->get();
            break;
        case Task::STATUS_APPROVAL_WAITING:
            // 文化連にメール
            $mail_users = $pa->facility->bunkaren()->first()->users()->isClaimMail()->get();
            break;
        }

        foreach($mail_users as $mail_user) {
            $this->sendMail($mail_user->email, $pa, true);
        }
        return true;
    }

    /*
     * 更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $priceAdoption = $this->priceAdoption->find($request->id);
            if (is_null($priceAdoption)) {
                return false;
            }
            if (isBunkaren()) {
                $priceAdoption->purchase_price = $request->purchase_price;
                $priceAdoption->sales_price = $request->sales_price;
                $priceAdoption->trader_id = $request->trader_id;
            }
            $priceAdoption->comment = $request->comment;
            $priceAdoption->save();
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            return true;

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 採用一覧のボタンの情報を返す
     */
    public function button($apply)
    {
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => '',
            'style' => ''
            ];

        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        if (isHospital()) {

            // 申請中は「取り下げ」ボタン
            if ( $apply->status === TASK::STATUS_INVOICING ) {
                $property = [
                    'url' => route('payment.withdraw',['id' => $apply->id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'

                        ];
            }

            // 採用可は「採用」ボタン
            if ( $apply->status === TASK::STATUS_CONFIRM ) {
                $property = [
                    'url' => route('payment.confirm',['id' => $apply->id ]),
                        'label' => '請求確認',
                        'style' => 'btn-danger'
                        ];
            }
        }
        // 本部の場合
        if (isHeadQuqrters()) {
        }

        // 文化連の場合
        if (isBunkaren()) {
            // 交渉中は「価格登録」
            if ( $apply->status === TASK::STATUS_INVOICING ) {
                $property = [
                    'url' => route('payment.import',['id' => $apply->id ]),
                        'label' => '売買登録',
                        'style' => 'btn-danger'
                        ];
            }
            // 承認待は「取り下げ」ボタン
            if ( $apply->status === TASK::STATUS_CONFIRM ) {
                $property = [
                    'url' => route('payment.withdraw',['id' => $apply->id ]),
                        'label' => '取り下げ',
                        'style' => 'btn-secondary'
                        ];
            }
            if ( $apply->status === TASK::STATUS_INVOICED ) {
                $property = [
                    'url' => route('payment.complete',['id' => $apply->invoice_id ]),
                        'label' => '請求確定',
                        'style' => 'btn-danger'
                        ];
            }
        }
        return $property;
    }

    /*
     * 採用申請詳細の差し戻しボタンの情報を返す
     */
    public function buttonRemand($apply)
    {
        $user = Auth::user();
        $property = [
            'url' => null,
            'label' => ''
            ];

        // ステータスとログインしているユーザの施設により処理が変わる
        // 病院の場合
        if (isHospital()) {
            // 承認済は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_UNCONFIRMED && $apply->facility_id == $user->facility->id) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }
        // 本部の場合
        if (isHeadQuqrters()) {
            // 申請中は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_APPLYING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
            // 承認待は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_APPROVAL_WAITING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }

        // 文化連の場合
        if (isBunkaren()) {
            // 交渉中は「差し戻し」ボタン
            if ($apply->status === TASK::STATUS_NEGOTIATING) {
                $property = [
                    'url' => route('apply.remand',['id' => $apply->price_adoption_id ]),
                        'label' => '差し戻し'
                        ];
            }
        }
        return $property;
    }

    /*
     * ステータス一覧
     */
    public function getTasks()
    {
        return Task::STATUS_STR2;
    }

    /*
     * メール送信
     */
    public function sendMail($email, PriceAdoption $pa, $remand = false)
    {
        $data['facility'] = $pa->facility;
        $data['jan_code'] = ($pa->isRegisted()) ? $pa->jan_code : $pa->medicine->packUnit->jan_code;
        $data['name'] = ($pa->isRegisted()) ? $pa->name : $pa->medicine->name;
//        $data['maker_name'] = ($pa->isRegisted()) ? $pa->maker_name : $pa->medicine->maker->name;
        if(empty($pa->maker_name)){
            $data['maker_name'] = $pa->medicine->maker->name;
        }else{
            $data['maker_name'] = $pa->maker_name;
        }

        $data['mail_str'] = self::MAIL_STR[$pa->status];
        $data['subject'] = sprintf("%s%s%sされました(%s)"
            , \Config::get('mail.subject.prefix')
            , self::MAIL_STR[$pa->status]
            , ($remand) ? "が差し戻し" : ""
            , $pa->facility->name);
        $data['url'] = asset(route('apply.detail', ['id' => $pa->id, 'flg' => 1]));

        try {
            Mail::to($email)->send(new AdoptionMail($data));
        } catch (\Exception $e){
            return false;
        }
    }


    /*
     * 明細一覧取得
     */
    public function getInvoiceDetail(Request $request, $user)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;

        $list = $this->invoiceDetail->getListInvoiceDetail($request, $count);

        return $list;
    }
}
