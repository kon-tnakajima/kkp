<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MailQue extends Model
{
    protected $guraded = ['id'];
    protected $fillable = [
    		'send_date',
    		'send_que_id',
    		'mail_to',
    		'subject',
    		'facility',
    		'jan_code',
    		'name',
    		'maker_name',
    		'mail_url',
    		'mail_str',
    		'result_code',
    		'result_message',
    		'deleter',
    		'creater',
			'updater',
			'is_send',
			'is_claim',
			'is_target',
			'data_type_name'
    ];
	//メールキューID(操作者が送信するメール群ID)を取得
	public function getMailQueID() {
		$result = \DB::select("select nextval('send_que_id_seq') as nextid");

		return $result[0]->nextid;
	}

}
