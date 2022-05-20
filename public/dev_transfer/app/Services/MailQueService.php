<?php

namespace App\Services;

use App\Model\MailQue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Apply;
use App\Mail\AdoptionMail;
use App\Model\PriceAdoption;
use App\Model\TransactFileStorage;
use App\Model\Task;
use App\Mail\SampleNotification;
use Illuminate\Support\Facades\Mail;
use App\Model\Maker;
use App\Model\UserGroup;
use App\Model\UserGroupDataType;
use App\Services\BaseService;

class MailQueService extends BaseService
{
	private $send_que_id;
	/*const MAIL_STR = [
			Task::STATUS_APPLYING => '申請',
			Task::STATUS_NEGOTIATING => '交渉許可',
			Task::STATUS_ESTIMATING => '見積中',
			Task::STATUS_APPROVAL_WAITING => '価格登録',
			Task::STATUS_UNCONFIRMED => '採用承認',
	];*/
	// 最新のステータスに差し替え
	const MAIL_STR = [
			Task::STATUS_UNAPPLIED => '未採用',//1
    		Task::STATUS_WAIT_REAPPLY => '再申請待ち',//10
    		Task::STATUS_PASS_REAPPLY => '再申請見送り',//13
    		Task::STATUS_APPLYING => '交渉依頼待ち',//20
    		Task::STATUS_PASS_APPLYING => '交渉依頼見送り',//23
    		Task::STATUS_NEGOTIATING => '見積依頼待ち',//30
    		Task::STATUS_PASS_NEGOTIATING => '見積依頼見送り',//33
    		Task::STATUS_ESTIMATING => '回答待ち',//40
    		Task::STATUS_PASS_ESTIMATING => '回答見送り',//43
    		Task::STATUS_REQUEST => '受諾依頼待ち',//50
    		Task::STATUS_ENTRUSTMENT => '受諾待ち',//60
    		Task::STATUS_PASS_ENTRUSTMENT => '受諾見送り',//63
    		Task::STATUS_ESTIMATED => '見積提出待ち',//70
    		Task::STATUS_APPROVAL_WAITING => '承認待ち',//80
    		Task::STATUS_PASS_APPROVAL_WAITING => '承認見送り',//83
    		Task::STATUS_UNCONFIRMED => '採用待ち',//90
    		Task::STATUS_PASS_UNCONFIRMED => '採用見送り',//93
    		Task::STATUS_ADOPTABLE => '採用可',//91
    		Task::STATUS_DONE => '採用済',//100
    		Task::STATUS_EXPIRE => '期限切れ',//110
    		Task::STATUS_EXPIRE_HONBU => '申請期限切れ'//120
	];

	// TODO ここがおかしいけど
    const MAIL_STR2 = [
        Task::STATUS_UNCLAIMED => '請求ファイル登録',
        Task::STATUS_INVOICING => '請求ファイル登録',
        Task::STATUS_TRADER_CONFIRM => 'YYY',
        Task::STATUS_TRADER_CLAIMED => 'XXX',
        Task::STATUS_CONFIRM => '売買登録',
        Task::STATUS_CLAIMED => '請求確認',
        Task::STATUS_COMPLETE => '請求確定',
    ];

    /*
     * コンストラクタ
     */
    public function __construct($send_que)
    {
        $this->mailque = new MailQue();
        info('send_que['.$send_que.']');
        $this->send_que_id=$send_que;
    }

    /*
     * 登録
     * 
     *@param  int $statusUpdated  タスク実行後のステータス
     */
    public function send_mail_add($mail_users, PriceAdoption $pa, $statusUpdated, $is_target = 1, $remand = false)
    {
        info('send_mail_add開始['.$this->send_que_id."]");
        if (empty($mail_users)) {
            logger('メール宛先がないので終了');
        	return true;
        }

        $mdUserGroup = new UserGroup();

        $data = array();
        $salesUserGgroupName=$mdUserGroup->find($pa->sales_user_group_id)->name;
        $purchaseUserGgroupName=$mdUserGroup->find($pa->purchase_user_group_id)->name;
        $statusFromGyosya = [43,50,63,70]; //業者由来で生成されるステータス
        if(!empty($purchaseUserGgroupName) && in_array($statusUpdated, $statusFromGyosya)){
            $facilityName = $salesUserGgroupName .'-' . $purchaseUserGgroupName;
        }else{
            $facilityName = $salesUserGgroupName;
        }

        $data['facility'] = $facilityName;
        $data['jan_code'] = $pa->medicine->packUnit->jan_code;
        $data['name'] = $pa->medicine->name;

        $medicine=$pa->medicine;
        $mdMaker = new Maker();
        $tasks = new Task();
        $maker = $mdMaker->find($medicine->selling_agency_code);
        $data['maker_name'] = (empty($maker->name)) ? $pa->maker_name : $maker->name;
        $data['mail_str'] = ($remand) ? "差し戻しで".$tasks->getStatusName($pa->status, 0) : $tasks->getStatusName($pa->status, 0);
        $data['subject'] = sprintf("%s申請(%s)が%sになりました:%s"
                , \Config::get('mail.subject.prefix')
                , $facilityName
        		, $data['mail_str']
                , mb_substr($data['name'],0,64));

        //担当者かどうか
        $data['is_target'] = $is_target;
        $data['mail_url'] = asset(route('apply.index', ['simple_search' => 'my_charge', 'is_search' => 1]));
        
        // is_search=1&my_charge=1&mode_radios=1
        $data['send_date'] = date('Y-m-d');
        $data['send_que_id'] = $this->send_que_id;
        // $data['mail_to'] = $email;
        $data['is_send'] = 1;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $datum = array();
        for ($i = 0; $i < count($mail_users); $i++) {
            $data['mail_to'] = $mail_users[$i]->email;
            $datum[] = $data;
        }

        \DB::beginTransaction();
        try {
            MailQue::insert($datum);
        	// $this->mailque->create($data);
            \DB::commit();

            //$cmd = "php artisan email:send ".$this->send_que_id." > /dev/null 2>&1 &";
            //$cmd = "php ".$_SERVER["DOCUMENT_ROOT"]."/../"."artisan email:send ".$this->send_que_id." > /dev/null 2>&1 &";
            //exec($cmd);

            return true;

        } catch (\PDOException $e){
        	info('エラー['.$e->getMessage()."]");
            session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * メール送信
     */
    public function sendMail($send_que_id)
    {
    	//５日以上前のmail_queを削除
    	$target = date('Y-m-d H:i:s', strtotime('-5 day', time()));
    	\DB::beginTransaction();
    	try {
    		\DB::table('mail_ques')->where('created_at', '<', $target)->delete();
    		\DB::commit();

    	} catch (\PDOException $e){
    		session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
    		\DB::rollBack();
    		return false;
    	}

    	$query = $this->mailque->query();
    	$query->where('send_que_id',$send_que_id);
    	$result = $query->get();
    	foreach($result as $mail_data) {
    		$data['facility'] = $mail_data->facility;
    		$data['jan_code'] = $mail_data->jan_code;
    		$data['name'] = $mail_data->name;
    		$data['maker_name'] = $mail_data->maker_name;
    		$data['mail_str'] = $mail_data->mail_str;
    		$data['subject'] = $mail_data->subject;
    		$data['url'] = $mail_data->mail_url;

    		Mail::to($mail_data->mail_to)->send(new AdoptionMail($data));

    	}

    }

    /**
     * send_mail_add_claim
     *
     * @param  string $email            メールアドレス
     * @param  int    $user_group_id    ユーザグループID
     * @param  string $user_group_name  ユーザグループ名
     * @param  int    $status           ステータス
     * @param  string $claim_month      年月
     * @param  int    $is_target        対象
     * @param  bool   $remand           差戻条件
     * @return bool true=成功, false=失敗
     */
    public function send_mail_add_claim($mail_users, UserGroup $userGroup, UserGroupDataType $userGroupDataType, TransactFileStorage $transactFileStorage, int $is_target = 1, $is_reject = false)
    {
        info('send_mail_add_claim開始['.$this->send_que_id."]");
        logger($mail_users);
        if (empty($mail_users)) {
            logger('メール宛先がないので終了');
        	return true;
        }

        $data = array();
        $data['facility'] = $userGroup->name;
        $tasks = new Task();
        $mail_str = $tasks->getStatusName($transactFileStorage->claim_task_status, 3); //請求登録のapply_payment_flgは3を設定
        $data['mail_str'] = $mail_str;

        $data['subject'] = sprintf("%s請求ファイル(%s:%s)が%sになりました"
        		, \Config::get('mail.subject.prefix')
                , $userGroup->name
                , mb_substr($transactFileStorage->claim_month,0,7)
                , $data['mail_str']);

        $data['is_target'] = $is_target;
        $data['mail_url'] = ($is_reject) ? asset(route('claim.regist_list', ['status' => 'status'])) : asset(route('claim.regist_list', ['reginitial' => 1])); //却下済みを含むのトップページを返してあげる
        $data['send_date'] = date('Y-m-d');
        $data['send_que_id'] = $this->send_que_id;
        // $data['mail_to'] = $email;
        $data['is_send'] = 1;
        $data['is_claim'] = 1;
        $data['data_type_name'] = $userGroupDataType->data_type_name;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $datum = array();
        for ($i = 0; $i < count($mail_users); $i++) {
            $data['mail_to'] = $mail_users[$i]->email;
            $datum[] = $data;
        }

        \DB::beginTransaction();
        try {
            // $this->mailque->create($data);
            MailQue::insert($datum);
            \DB::commit();

            return true;

        } catch (\PDOException $e){
        	info('エラー['.$e->getMessage()."]");
            session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            throw $e;
            return false;
        }
    }

    /**
     * 請求明細用
     *
     * @param string $email メールアドレス
     * @param string $user_group_name ユーザグループ名
     * @param int $status ステータス
     * @param string $claim_month 年月
     * @param int $claim_control 1:施設 2:業者
     * @param int $is_target 対象
     * @param bool $remand 差戻条件
     * @return bool true=成功,false=失敗
     */
    public function send_mail_add_claim_detail($mail_users, $user_group_name, $status, $claim_month, $claim_control, $trader_user_group_name, $transact_header_id = 0, $is_target = 1, $remand = false)
    {
        info('send_mail_add_claim_detail開始['.$this->send_que_id."]");
        if (empty($mail_users)) {
            logger('メール宛先がないので終了');
        	return true;
        }

    	$data = array();
        $data['facility'] = $user_group_name;
    	$tasks = new Task();
    	$mail_str = "";
    	if($claim_control == '1') {
    		$mail_str = $tasks->getStatusName($status, 5);
    	} else if ($claim_control == '2') {
    		$mail_str = $tasks->getStatusName($status, 4);
    	}
        $data['mail_str'] = $mail_str;
        $data['mail_str'] = ($remand) ? $mail_str."差し戻しで" : $mail_str;


    	$data['subject'] = sprintf("%s請求内容(%s:%s:%s)が%s%sになりました"
    			, \Config::get('mail.subject.prefix')
                , $user_group_name
                , $trader_user_group_name
                , mb_substr($claim_month,0,7)
                , ($remand) ? "差し戻しで" : ""
                , $mail_str);

    	//担当者かどうか
    	$data['is_target'] = $is_target;
        $data['mail_url'] = ($transact_header_id == 0) ? asset(route('claim.index', ['initial' => 1])) : asset(route('claim.trader', ['id' => $transact_header_id]));
    	$data['send_date'] = date('Y-m-d');
    	$data['send_que_id'] = $this->send_que_id;
    	// $data['mail_to'] = $email;
    	$data['is_send'] = 1;
        $data['is_claim'] = 1;
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $datum = array();
        for ($i = 0; $i < count($mail_users); $i++) {
            $data['mail_to'] = $mail_users[$i]->email;
            $datum[] = $data;
        }

    	\DB::beginTransaction();
    	try {
            // $this->mailque->create($datum);MailQue
            MailQue::insert($datum);
    		\DB::commit();

    		return true;

    	} catch (\PDOException $e){
    		info('エラー['.$e->getMessage()."]");
    		session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
    		\DB::rollBack();
    		throw $e;
    		return false;
    	}
    }
}
