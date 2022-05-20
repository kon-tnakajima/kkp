<?php

namespace App\Services;

use App\Model\MailQue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Apply;
use App\Mail\AdoptionMail;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Mail\SampleNotification;
use Illuminate\Support\Facades\Mail;

class MailQueService
{

	private $send_que_id;
	const MAIL_STR = [
			Task::STATUS_APPLYING => '申請',
			Task::STATUS_NEGOTIATING => '交渉許可',
			Task::STATUS_APPROVAL_WAITING => '価格登録',
			Task::STATUS_UNCONFIRMED => '採用承認',
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

    /*
     * 登録
     */
    public function send_mail_add($email, PriceAdoption $pa, $is_target = 1, $remand = false)
    {
        info('send_mail_add開始['.$this->send_que_id."]");
        $data = array();
        $data['facility'] = $pa->facility->name;
        $data['jan_code'] = ($pa->isRegisted()) ? $pa->jan_code : $pa->medicine->packUnit->jan_code;
        $data['name'] = ($pa->isRegisted()) ? $pa->name : $pa->medicine->name;
//        $data['maker_name'] = ($pa->isRegisted()) ? $pa->maker_name : $pa->medicine->maker->name;
        $data['maker_name'] = (empty($pa->medicine->maker)) ? $pa->maker_name : $pa->medicine->maker->name;
//        $data['mail_str'] = self::MAIL_STR[$pa->status] ;
        $data['mail_str'] = ($remand) ? self::MAIL_STR[$pa->status]."が差し戻し" : self::MAIL_STR[$pa->status];
        $data['subject'] = sprintf("%s%s%sされました(%s)"
        		, \Config::get('mail.subject.prefix')
        		, self::MAIL_STR[$pa->status]
        		, ($remand) ? "が差し戻し" : ""
                , $pa->facility->name);

        //担当者かどうか
        $data['is_target'] = $is_target;

        if($pa->status === Task::STATUS_UNCONFIRMED){
            //is_search=1&search=49XXXXX&discontinuation_date=1
            $data['mail_url'] = asset(route('apply.index', ['is_search' => 1, 'search' => $data['jan_code'], 'discontinuation_date' => 1]));
        }else{
            $data['mail_url'] = asset(route('apply.detail', ['id' => $pa->id, 'flg' => 1]));
        }

        $data['send_date'] = date('Y-m-d');
        $data['send_que_id'] = $this->send_que_id;
        $data['mail_to'] = $email;
        $data['is_send'] = 1;

        \DB::beginTransaction();
        try {
        	$this->mailque->create($data);
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

}
