<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Model\MailQue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Apply;
use App\Mail\AdoptionMail;
use App\Model\PriceAdoption;
use App\Model\Task;
use App\Mail\SampleNotification;
use Illuminate\Support\Facades\Mail;


class SendEmails extends Command
{
	const MAIL_STR = [
			Task::STATUS_APPLYING => '申請',
			Task::STATUS_NEGOTIATING => '交渉許可',
			Task::STATUS_APPROVAL_WAITING => '価格登録',
			Task::STATUS_UNCONFIRMED => '採用承認',
	];

	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'email send';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->mailque = new MailQue();
    	parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
    	//
		echo "メール送信コマンド開始";
		\Log::debug("*** メール送信コマンド開始 ***");
    	//$queId = $this->argument('id');
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

    	echo "メール送信取得";
		\Log::debug("メール送信取得");
    	$query = $this->mailque->query();
    	//$query->where('send_que_id',$queId);
    	$query->where('is_send', 1);
    	$que_datas = $query->get();
		$message="";
		
		//send_que_idごとにメール送信する
		$result = [];
    	foreach($que_datas as $que_data) {
			if(!array_key_exists($que_data->send_que_id, $result)){
				$mail_groups = [];
				$mail_groups["id"] = $que_data->id;
	    		$mail_groups['facility'] = $que_data->facility;
	    		$mail_groups['jan_code'] = $que_data->jan_code;
	    		$mail_groups['name'] = $que_data->name;
	    		$mail_groups['maker_name'] = $que_data->maker_name;
	    		$mail_groups['mail_str'] = $que_data->mail_str;
	    		$mail_groups['subject'] = $que_data->subject;
	    		$mail_groups['url'] = $que_data->mail_url;
				$mail_groups["to"] = [];
				$mail_groups["cc"] = [];
				if($que_data->is_target == "1"){
					echo "Toで送信[ email:".$que_data->mail_to."]";
					\Log::debug("Toで送信[ email:".$que_data->mail_to."]");
					array_push($mail_groups["to"], $que_data->mail_to);
				}else{
					echo "CCで送信[ email:".$que_data->mail_to."]";
					\Log::debug("CCで送信[ email:".$que_data->mail_to."]");
					array_push($mail_groups["cc"], $que_data->mail_to);
				}
				$result[$que_data->send_que_id] = $mail_groups;
			}else{
				if($que_data->is_target == "1"){
					echo "Toで送信[ email:".$que_data->mail_to."]";
					\Log::debug("Toで送信[ email:".$que_data->mail_to."]");
					array_push($result[$que_data->send_que_id]["to"], $que_data->mail_to);
				}else{
					echo "CCで送信[ email:".$que_data->mail_to."]";
					\Log::debug("CCで送信[ email:".$que_data->mail_to."]");
					array_push($result[$que_data->send_que_id]["cc"], $que_data->mail_to);
				}
			}
		}

    	//foreach($result as $mail_data) {
		foreach($result as $key => $mail_data) {
				$message="";
			try {
				//echo "メール送信[ que_id:".$mail_data->id."]";
				//\Log::debug("メール送信[ que_id:".$mail_data->id."]");

	    		// $data['facility'] = $mail_data->facility;
	    		// $data['jan_code'] = $mail_data->jan_code;
	    		// $data['name'] = $mail_data->name;
	    		// $data['maker_name'] = $mail_data->maker_name;
	    		// $data['mail_str'] = $mail_data->mail_str;
	    		// $data['subject'] = $mail_data->subject;
	    		// $data['url'] = $mail_data->mail_url;
				echo "メール送信[ que_id:".$mail_data["id"]."]";
				\Log::debug("メール送信[ que_id:".$mail_data["id"]."]");

	    		$data['facility'] = $mail_data['facility'];
	    		$data['jan_code'] = $mail_data['jan_code'];
	    		$data['name'] = $mail_data['name'];
	    		$data['maker_name'] = $mail_data['maker_name'];
	    		$data['mail_str'] = $mail_data['mail_str'];
	    		$data['subject'] = $mail_data['subject'];
	    		$data['url'] = $mail_data['url'];
//				Mail::to($mail_data->mail_to)->send(new AdoptionMail($data));
				Mail::to($mail_data['to'])->cc($mail_data['cc'])->send(new AdoptionMail($data));

				//キューを処理済にする
				\DB::beginTransaction();
				//$mailque = $this->mailque->find($mail_data["id"]);
				//$upd_data = array();
				//$upd_data['is_send'] = 0;
				//$upd_data['send_date'] = date('Y/m/d');
				//$mailque->update($upd_data);
				$update_column = [
						'is_send' => 0,
						'send_date' => date('Y/m/d')
				];
				$this->mailque->where('send_que_id', $key)->update($update_column);

				\DB::commit();

				//echo "送信フラグを更新[ que_id:".$mailque->id."]";
				//\Log::debug("送信フラグを更新[ que_id:".$mailque->id."]");

			} catch (Exception $e){
				echo $e->getMessage();
				\DB::rollBack();
			}

    	}

    	echo "メール結果[".count(Mail::failures())."]";
		\Log::debug("メール結果[".count(Mail::failures())."]");
		\Log::debug("*** メール送信コマンド終了 ***");

    	//エラーメールが存在するか？
    	if( count(Mail::failures()) > 0 ) {

    		echo "There was one or more failures. They were: <br />";
			\Log::debug("There was one or more failures. They were: <br />");

    		foreach(Mail::failures() as $email_address) {
    			echo " - $email_address <br />";
				\Log::debug(" - $email_address <br />");
    		}

    	} else {
    		echo "No errors, all sent successfully!";
			\Log::debug("No errors, all sent successfully!");
    	}

    }
}
