<?php

namespace App\Services\Auth;

use Illuminate\Http\Request;
use App\Model\PasswordReset;

class ResetPasswordService
{

    /*
     * コンストラクタ
     */
    public function __construct()
    {
    	$this->password_reset = new PasswordReset();
    }

	public function getPasswordResetInfo($token){
		return $this->password_reset->getData($token);
	}


}
