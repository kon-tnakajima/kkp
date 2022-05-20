<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{

	public function getData($token)
	{
		$sql  = "";
		$sql .= " select * from password_resets where token='".$token."'";

		$all_rec = \DB::select($sql);

		if (empty($all_rec)){
			$result=null;
		} else {
			$result = PasswordReset::query()->hydrate($all_rec);
		}

		return $result;
	}
}
