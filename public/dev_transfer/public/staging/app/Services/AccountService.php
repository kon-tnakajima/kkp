<?php

namespace App\Services;

use App\Model\AccountRequest;
use Illuminate\Http\Request;
class AccountService 
{
    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->accountRequest = new AccountRequest();
    }

    
    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $this->accountRequest->email = $request->email;
            $this->accountRequest->organization_id = $request->organization_id;
            $this->accountRequest->name = $request->name;
            $this->accountRequest->save();

            \DB::commit();

            return true;
            
        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            return false;
        }
    }

    
}
