<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\ApplicationUseRequest;
use App\Model\ApplicationGroupRequest;
use App\Model\GroupRoleRelation;
use App\Model\UserGroup;
use App\Model\Role;
use App\Model\GroupRelation;
use App\Model\UserGroupRelation;
use App\Model\Agreement;
use App\User;
use App\Model\Concerns\UserGroup as UserGroupTrait;
use App\Services\BaseService;
use Illuminate\Support\Str;

class AccountService extends BaseService
{
	use UserGroupTrait;

    const DEFAULT_PAGE_COUNT = 20;
    const APPLICATION_STATUS = 1; // アカウント申請
    const PERMISSION_STATUS  = 2; // アカウント申請許可
    const START_STATUS       = 3; // アカウント申請開始
    const REJECTION_STATUS   = 4; // アカウント申請却下
    const NEW_CREATE_GROUP_ROLE_KEY_CODE = 'グループ管理者'; // 新規利用申請の許可登録時の役割名固定
    const NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE = '権限なし'; // 新規利用申請の許可登録時の役割名固定
    const NEW_REFERENCE_ROLE_KEY_CODE = '参照';   // 新規利用申請の許可登録時の役割名固定
    const NEW_KOJIN_ROLE_KEY_CODE = '個人利用者'; // 個人の新規利用申請の許可登録時の役割名固定
    // 利用申請登録条件
    const EXISTING_APPLICATION_MODE = 1; // 既存グループ利用申請
    const EXISTING_PERSONAL_MODE    = 2; // 個人利用申請
    const NEW_USE_APPLICATION_MODE  = 9; // 新規利用申請


    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->applicationUseRequest   = new ApplicationUseRequest();
        $this->applicationGroupRequest = new ApplicationGroupRequest();
        $this->userGroup               = new UserGroup();
        $this->user                    = new User();
        $this->userGroupRelation       = new UserGroupRelation();
        $this->groupRoleRelation       = new GroupRoleRelation();
        $this->groupRelation           = new GroupRelation();
        $this->role                    = new Role();
        $this->agreement               = new Agreement();
    }

    /**
     * 規約取得
     */
    public function getAgreement()
    {
    	return $this->agreement->getAgreement() ?? null;
    }

    /**
     * メール送信
     *
     * @param string $viewName ビュー名
     * @param string $email メールアドレス
     * @param string $subject 件名
     * @params array $body 本体情報
     * @return void
     */
    /**
     * メール送信
     *
     * @param string $viewName ビュー名
     * @param string $email メールアドレス
     * @param string $subject 件名
     * @param array $body 本体情報
     * @return bool
     */
    private function sendMail(string $viewName, string $email, string $subject, array $body): bool
    {
        try {
            // メール送信
            Mail::send($viewName, $body,
                function($message) use ($email, $subject) {
                    $message
                        ->from(env('MAIL_ADMIN_ADDRESS'))
                        ->to($email)
                        ->subject($subject);
                });

        } catch (\Swift_TransportException $exception) {
            \Log::info('メール送信失敗しました['. $exception->getMessage(). ']');
            return false;
        }
        return true;
    }

    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $condition = (int)$request->condition;
            // 新規利用申請条件
            if ($condition === self::NEW_USE_APPLICATION_MODE) {
                // グループ名が既にある？
                if ($this->userGroup->where('name', $request->user_group_name)->exists() === true) {
                    throw new \PDOException('グループ名が存在しております。別のグループ名を登録してください。');
                }
            // 個人
            } else if ($condition === self::EXISTING_PERSONAL_MODE){
                // グループ名が既にある？
                if ($this->userGroup->where('name', $request->user_group_name)->exists() === true) {
                    throw new \PDOException('グループ名が存在しております。別のグループ名を登録してください。');
                }

            // 既存・個人利用申請条件
            } else {
                // 既にユーザグループテーブルにはグループキーが存在
                if ($this->userGroup->where('group_key', $request->group_key)->exists() === false) {
                    throw new \PDOException('グループキーが存在しません。グループキーをご確認ください。');
                }
            }

            // 共通でemail+sub_idがあるか確認
            $sub_id = $request->sub_id ?? '';
            /*
            if ($this->user->where('email', $request->email)->where('sub_id', $sub_id)->exists() === true) {
                throw new \PDOException('既に同じサブIDは利用されているので別のサブIDを登録してください。');
            }
            */

            $subject = '既存利用申請登録ありがとうございます。';
            $body = [ 'body' => '申請内容確認次第、開始メール（パスワード登録）をお送りしますので暫くお待ちください。'];
            // 新規
            if ($condition === self::NEW_USE_APPLICATION_MODE) {
                $subject = '新規利用申請登録ありがとうございます。';
                $apply = $this->applicationGroupRequest;
                $apply->group_key = $request->group_key;
                $apply->user_group_name = $request->user_group_name;
            // 個人
            } else if ($condition === self::EXISTING_PERSONAL_MODE){
            	$subject = '個人利用申請登録ありがとうございます。';
                $apply = $this->applicationUseRequest;
                $apply->group_key = \Config::get('const.kojin_group_key');
                $apply->user_group_name = $request->user_group_name;
            // 既存
            } else {
            	$apply = $this->applicationUseRequest;
            	$apply->group_key = $request->group_key;
            	$apply->user_group_name = null;
            }
            $apply->email           = $request->email;
            //$apply->group_key       = ($condition === self::NEW_USE_APPLICATION_MODE) ? null : $request->group_key;
            //$apply->user_group_name = ($condition === self::NEW_USE_APPLICATION_MODE) ? $request->user_group_name : null;
            $apply->name            = $request->name;
            $apply->sub_id          = $sub_id;
            $apply->remarks         = $request->remarks;
            $apply->status          = self::APPLICATION_STATUS;
            $apply->save();
            \DB::commit();
            // 申請完了メール送信
            $view_name = 'mail.application';
            if (!$this->sendMail($view_name, $request->email, $subject, $body)) {
            	throw new \PDOException('メール送信失敗しました。');
            }

            return true;
        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            return false;
        }
    }

    /*
     * 既存グループ利用申請一覧
     */
    public function getAccountUseList(Request $request, int $id)
    {
        $result = $this->userGroup->find($id);
        if (empty($result)) {
            return [];
        }
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $name       = empty($request->name) ? null : '%'.$request->name.'%';
        $email      = empty($request->email) ? null : '%'.$request->email.'%';
        $group_name = empty($request->user_group_name) ? null : '%'.$request->user_group_name.'%';
        $group_key = $result['group_key'];
        if (isBunkaren()) {
        	//$group_key = null;
        	$group_key = \Config::get('const.kojin_group_key'); // メニュー制御の件で文化連ユーザーの場合、個人だけを表示するように変更
        }


        return $this->applicationUseRequest
                    //->where('group_key', $result['group_key'])
                    ->where(function ($query) use ($name, $email, $group_name,$group_key ){
                        if (!empty($name)) {
                            $query->where('name', 'LIKE', $name);
                        }
                        if (!empty($email)) {
                            $query->where('email', 'LIKE', $email);
                        }
                        if (!empty($group_name)) {
                            $query->where('user_group_name', 'LIKE', $group_name);
                        }
                        if (!empty($group_key)) {
                            $query->where('group_key', '=', $group_key);
                        }
                    })
                    ->orderBy('id', 'asc')
                    ->paginate($count);
    }

    /*
     * 新規利用申請一覧画面
     */
    public function getAccountGroupList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $name       = empty($request->name) ? null : '%'.$request->name.'%';
        $email      = empty($request->email) ? null : '%'.$request->email.'%';
        $group_name = empty($request->user_group_name) ? null : '%'.$request->user_group_name.'%';
        return $this->applicationGroupRequest
                    ->where(function ($query) use ($name, $email, $group_name){
                        if (!empty($name)) {
                            $query->where('name', 'LIKE', $name);
                        }
                        if (!empty($email)) {
                            $query->where('email', 'LIKE', $email);
                        }
                        if (!empty($group_name)) {
                            $query->where('user_group_name', 'LIKE', $group_name);
                        }
                    })
                    ->orderBy('id', 'asc')
                    ->paginate($count);
    }

    /**
     * 新規利用申請許可処理
     */
    public function groupPermission(Request $request)
    {
        $applicationGroupRequest = $this->applicationGroupRequest->find($request->id);
        \DB::beginTransaction();
        try {
            // グループキーが既にある場合
            if($this->userGroup->where('group_key', $request->group_key)->exists()) {
                throw new \PDOException('グループキーが既に存在しております。別のグループキーを登録してください。');
            }
            // ユーザグループ作成
            $this->userGroup->id          = $this->userGroup->getUserGroupId();
            //$this->userGroup->facility_id = -1;
            $this->userGroup->name        = $applicationGroupRequest->user_group_name;
            $this->userGroup->formal_name = $applicationGroupRequest->user_group_name;
            $this->userGroup->group_type  = $request->group_type;
            $this->userGroup->group_key   = $request->group_key;
            $this->userGroup->save();

            // ユーザ作成
            if (empty($applicationGroupRequest->sub_id)) {
            	$sub_id='';
            } else {
            	$sub_id=$applicationGroupRequest->sub_id;

            }

            $list = $this->user->where('email', $applicationGroupRequest->email)->where('sub_id', $sub_id)->get();
            if (!count($list)) {
            	$this->user->id                          = $this->user->getUserId();
            	$this->user->name                        = $applicationGroupRequest->name;
            	$this->user->email                       = $applicationGroupRequest->email;
            	$this->user->sub_id                      = $applicationGroupRequest->sub_id ?? '';
            	$this->user->password                    = '';
            	//$this->user->facility_id                 = -1;
            	$this->user->email_verified_at           = date('Y-m-d H:i:s');
            	$this->user->is_adoption_mail            = false;
            	$this->user->is_claim_mail               = false;
            	$this->user->primary_honbu_user_group_id = $this->userGroup->id;
            	$this->user->primary_user_group_id       = $this->userGroup->id;
            	$this->user->save();
            } else {
            	$this->user->id                          = $list[0]->id;
            }

            // ユーザグループリレーション作成
            $this->userGroupRelation->user_id       = $this->user->id;
            $this->userGroupRelation->user_group_id = $this->userGroup->id;
            $this->userGroupRelation->role_key_code = self::NEW_CREATE_GROUP_ROLE_KEY_CODE;
            $this->userGroupRelation->save();

            // groupRelationの初期登録処理
            // 初期登録時に文化連ユーザーと紐づけをデフォルトで作成する。
            // グループリレーション作成
            $this->groupRelation->user_group_id         = $this->userGroup->id;
            $this->groupRelation->partner_user_group_id = $this->userGroup->getBunkarenUserGroupId();
            $this->groupRelation->save();

/* TODO 元からコメントアウトされていたソース
            // グループリレーション作成
            $this->groupRelation->user_group_id         = $this->userGroup->id;

            //本部なら文化連配下に所属
            if ($request->group_type == '本部') {
            	$this->groupRelation->partner_user_group_id = $this->userGroup->getBunkarenUserGroupId();
            } else {
            	$this->groupRelation->partner_user_group_id = $this->userGroup->id;
            }

            $this->groupRelation->save();
*/
            // グループロールリレーション作成
            $groupRoleRelation = new GroupRoleRelation();
            $groupRoleRelation->user_group_id  = $this->userGroup->id;
            $groupRoleRelation->role_key_code  = self::NEW_CREATE_GROUP_ROLE_KEY_CODE;
            $groupRoleRelation->save();
            $groupRoleRelation = new GroupRoleRelation();
            $groupRoleRelation->user_group_id  = $this->userGroup->id;
            $groupRoleRelation->role_key_code  = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
            $groupRoleRelation->save();
            $groupRoleRelation = new GroupRoleRelation();
            $groupRoleRelation->user_group_id  = $this->userGroup->id;
            $groupRoleRelation->role_key_code  = "参照";
            $groupRoleRelation->save();

            // グループ区分とキーコードで一致しない(グループ管理者)
            if(!$this->role->where('group_type', $request->group_type)->where('key_code', self::NEW_CREATE_GROUP_ROLE_KEY_CODE)->exists()) {
                $role = new Role();
                $role->id = $role->getRoleId();
                $role->name = self::NEW_CREATE_GROUP_ROLE_KEY_CODE;
                $role->key_code = self::NEW_CREATE_GROUP_ROLE_KEY_CODE;
                $role->group_type = $request->group_type;
                $role->save();
            }
            // グループ区分とキーコードで一致しない(権限なし)
            if(!$this->role->where('group_type', $request->group_type)->where('key_code', self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE)->exists()) {
                $role = new Role();
                $role->id = $role->getRoleId();
                $role->name = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
                $role->key_code = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
                $role->group_type = $request->group_type;
                $role->save();
            }

            // グループ区分とキーコードで一致しない(参照)
            if(!$this->role->where('group_type', $request->group_type)->where('key_code', self::NEW_REFERENCE_ROLE_KEY_CODE)->exists()) {
            	$role = new Role();
            	$role->id = $role->getRoleId();
            	$role->name = self::NEW_REFERENCE_ROLE_KEY_CODE;
            	$role->key_code = self::NEW_REFERENCE_ROLE_KEY_CODE;
            	$role->group_type = $request->group_type;
            	$role->save();
            }


            $bUserList=$this->user->getMasterUsers();

            foreach($bUserList as $rec) {
            	$ugr = new UserGroupRelation();

            	$ugr->user_id       = $rec->id;
            	$ugr->user_group_id = $this->userGroup->id;
            	$ugr->role_key_code = "文化連管理者";
            	$ugr->save();

            }

            // 利用申請テーブル更新
            $applicationGroupRequest->remarks   = $request->remarks ?? null;
            $applicationGroupRequest->group_key = $request->group_key;
            $applicationGroupRequest->user_id   = $this->user->id;
            $applicationGroupRequest->status    = self::PERMISSION_STATUS;
            $applicationGroupRequest->save();
            \DB::commit();
            $request->session()->flash('message', '許可しました');
            return true;
        } catch (\PDOException $e){
            $errMessage = $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $this->getErrorMessage($errMessage));
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 新規利用申請却下処理
     */
    public function groupRejection(Request $request)
    {
        $applicationGroupRequest = $this->applicationGroupRequest->find($request->id);
        \DB::beginTransaction();
        try {
            $applicationGroupRequest->status = self::REJECTION_STATUS;
            $applicationGroupRequest->remarks = $request->remarks;
            $applicationGroupRequest->save();
            \DB::commit();

            $send_ret=$this->sendMail(
                'mail.rejection',
                $applicationGroupRequest->email,
                '新規利用申請について',
                ['body' => $applicationGroupRequest->remarks]);

            if (!$send_ret) {
            	throw new \PDOException('メール送信失敗しました。');
            }

            $request->session()->flash('message', '却下しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 簡易エラーメッセージ取得
     *
     * @param string $message システムよりのエラーメッセージ
     * @return エラーメッセージ情報
     */
    private function getErrorMessage(string $message)
    {
        $findString = '重複キーが一意性制約"users_unique1"に違反しています';
        $pos = strpos( $message, $findString);
        if ($pos !== false) {
            return '【重複エラー】ユーザテーブルにメールアドレスとサブIDが既に存在しております。';
        }
        return $message;
    }

    /**
     * 新規利用申請開始処理
     */
    public function groupStart(Request $request)
    {
        \DB::beginTransaction();
        try {
            $applicationGroupRequest = $this->applicationGroupRequest->find($request->id);
            $result = $this->user->find($applicationGroupRequest->user_id);
            $result->password = '';
            $result->save();

            // トークン取得
            $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($result);
            // メール送信
            $send_ret=$this->sendMail(
                'mail.passwordreset',
                $result->email,
                '新規利用申請ご登録ありがとうございます。担当者様へパスワード登録をお願いいたします。',
                ['sub_id' => $result->sub_id, 'actionUrl' => $request->root() . '/password/reset/' . $token]);

            if (!$send_ret) {
            	throw new \PDOException('メール送信失敗しました。');
            }


            // 新規利用申請テーブル更新
            $applicationGroupRequest->remarks = $request->remarks ?? null;
            $applicationGroupRequest->status = self::START_STATUS;
            $applicationGroupRequest->save();
            \DB::commit();
            $request->session()->flash('message', '開始しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 既存グループ利用申請開始処理
     */
    public function useStart(Request $request)
    {
        $applicationUseRequest = $this->applicationUseRequest->find($request->id);

        //$group_name = $this->getUserGroupName($applicationUseRequest->group_key);
        \DB::beginTransaction();
        try {
            $user_group_id = -1;
            $user = Auth::user();

            $complete_message = '既存グループ利用申請のご登録ありがとうございます。担当者様へパスワード登録をお願いいたします。';
            // 個人の場合
            if ($applicationUseRequest->group_key === \Config::get('const.kojin_group_key')) {
                $complete_message = '個人利用申請のご登録ありがとうございます。担当者様へパスワード登録をお願いいたします。';


                // ユーザグループ作成
                $this->userGroup->id          = $this->userGroup->getUserGroupId();
                //$this->userGroup->facility_id = -1;
                $this->userGroup->name        = $applicationUseRequest->user_group_name;
                $this->userGroup->formal_name = $applicationUseRequest->user_group_name;
                $this->userGroup->group_type  = \Config::get('const.kojin_name'); // 個人を設定
                $this->userGroup->group_key   = $applicationUseRequest->group_key;
                $this->userGroup->save();

                // ユーザ作成
                if (empty($applicationUseRequest->sub_id)) {
                    $sub_id='';
                } else {
                    $sub_id=$applicationUseRequest->sub_id;
                }

                // ユーザー登録
                $this->user->id                          = $this->user->getUserId();
                $this->user->name                        = $applicationUseRequest->name;
                $this->user->email                       = $applicationUseRequest->email;
                $this->user->sub_id                      = $applicationUseRequest->sub_id ?? '';
                $this->user->password                    = '';
                //$this->user->facility_id                 = -1;
                $this->user->email_verified_at           = date('Y-m-d H:i:s');
                $this->user->is_adoption_mail            = false;
                $this->user->is_claim_mail               = false;
                $this->user->primary_honbu_user_group_id = $this->userGroup->id;
                $this->user->primary_user_group_id       = $this->userGroup->id;
                $this->user->save();

                // ユーザグループリレーション作成
                $this->userGroupRelation->user_id       = $this->user->id;
                $this->userGroupRelation->user_group_id = $this->userGroup->id;
                $this->userGroupRelation->role_key_code = self::NEW_KOJIN_ROLE_KEY_CODE; // 個人特有のロールKeyを設定
                $this->userGroupRelation->save();


                // groupRelationの初期登録処理
                $this->groupRelation->user_group_id         = $this->userGroup->id;
                $this->groupRelation->partner_user_group_id = $this->userGroup->getBunkarenUserGroupId();
                $this->groupRelation->save();


                // グループロールリレーション作成
                $groupRoleRelation = new GroupRoleRelation();
                $groupRoleRelation->user_group_id  = $this->userGroup->id;
                $groupRoleRelation->role_key_code  = self::NEW_KOJIN_ROLE_KEY_CODE; // 個人用のロールkeyを作成する
                $groupRoleRelation->save();
                $groupRoleRelation = new GroupRoleRelation();
                $groupRoleRelation->user_group_id  = $this->userGroup->id;
                $groupRoleRelation->role_key_code  = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
                $groupRoleRelation->save();
                $groupRoleRelation = new GroupRoleRelation();
                $groupRoleRelation->user_group_id  = $this->userGroup->id;
                $groupRoleRelation->role_key_code  = self::NEW_REFERENCE_ROLE_KEY_CODE;;
                $groupRoleRelation->save();

                // グループ区分とキーコードで一致しない(個人利用者)
                if(!$this->role->where('group_type', \Config::get('const.kojin_name'))->where('key_code', self::NEW_KOJIN_ROLE_KEY_CODE)->exists()) {
                    $role = new Role();
                    $role->id = $role->getRoleId();
                    $role->name = self::NEW_KOJIN_ROLE_KEY_CODE;
                    $role->key_code = self::NEW_KOJIN_ROLE_KEY_CODE;
                    $role->group_type = \Config::get('const.kojin_name');
                    $role->save();
                }
                // グループ区分とキーコードで一致しない(権限なし)
                if(!$this->role->where('group_type', \Config::get('const.kojin_name'))->where('key_code', self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE)->exists()) {
                    $role = new Role();
                    $role->id = $role->getRoleId();
                    $role->name = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
                    $role->key_code = self::NEW_CREATE_NOAUTHORITY_ROLE_KEY_CODE;
                    $role->group_type = \Config::get('const.kojin_name');
                    $role->save();
                }
                // グループ区分とキーコードで一致しない(参照)
                if(!$this->role->where('group_type', \Config::get('const.kojin_name'))->where('key_code', self::NEW_REFERENCE_ROLE_KEY_CODE)->exists()) {
                    $role = new Role();
                    $role->id = $role->getRoleId();
                    $role->name = self::NEW_REFERENCE_ROLE_KEY_CODE;
                    $role->key_code = self::NEW_REFERENCE_ROLE_KEY_CODE;
                    $role->group_type = \Config::get('const.kojin_name');
                    $role->save();
                }

                $bUserList=$this->user->getMasterUsers();

                foreach($bUserList as $rec) {
                    $ugr = new UserGroupRelation();

                    $ugr->user_id       = $rec->id;
                    $ugr->user_group_id = $this->userGroup->id;
                    $ugr->role_key_code = "文化連管理者";
                    $ugr->save();

                }

                // 個人利用申請テーブル更新
                $applicationUseRequest->remarks         = $request->remarks ?? null;
                $applicationUseRequest->user_group_name = $applicationUseRequest->user_group_name;
                $applicationUseRequest->user_id         = $this->user->id;
                $applicationUseRequest->status          = self::START_STATUS;
                $applicationUseRequest->save();


            // 既存グループの追加の場合
            } else {
               $group_name = $this->getUserGroupName($applicationUseRequest->group_key);

               // 既にユーザグループテーブルにはグループキーが存在
               if ($this->userGroup->where('group_key', $applicationUseRequest->group_key)->exists() === false) {
                  throw new \PDOException("グループキーがありません。グループキー({$applicationUseRequest->group_key})をご確認ください。");
               }

               // ユーザ作成
               if (empty($applicationUseRequest->sub_id)) {
                  $sub_id='';
               } else {
                  $sub_id=$applicationUseRequest->sub_id;

               }

               $baseUg=$this->userGroup->where('group_key', $applicationUseRequest->group_key)->first();
               $primary =$this->userGroup->getPrimaryHonbuId($baseUg->id);

               $list = $this->user->where('email', $applicationUseRequest->email)->where('sub_id', $sub_id)->get();
               if (!count($list)) {
                  $this->user->id                          = $this->user->getUserId();
                  $this->user->name                        = $applicationUseRequest->name;
                  $this->user->email                       = $applicationUseRequest->email;
                  $this->user->sub_id                      = $applicationUseRequest->sub_id ?? '';
                  $this->user->password                    = '';
                  //$this->user->facility_id                 = -1;
                  $this->user->email_verified_at           = date('Y-m-d H:i:s');
                  $this->user->is_adoption_mail            = false;
                  $this->user->is_claim_mail               = false;

                  if ($primary == null) {
                     $this->user->primary_honbu_user_group_id = $baseUg->id;

                  } else {
                     $this->user->primary_honbu_user_group_id = $primary->primary_honbu_user_group_id;
                  }
                  $this->user->primary_user_group_id       = $baseUg->id;
                  $this->user->save();
               } else {
                  $this->user->id                          = $list[0]->id;
                  $this->user->email                       = $list[0]->email;
               }

               // ユーザグループリレーション作成
               $this->userGroupRelation->user_id       = $this->user->id;
               $this->userGroupRelation->user_group_id = $baseUg->id;
               //$this->userGroupRelation->user_group_id = $user->primary_user_group_id;
               $this->userGroupRelation->role_key_code = $request->role;
               $this->userGroupRelation->save();
               // 既存グループ利用申請テーブル更新
               $applicationUseRequest->remarks         = $request->remarks ?? null;
               $applicationUseRequest->user_group_name = $group_name;
               $applicationUseRequest->user_id         = $this->user->id;
               $applicationUseRequest->status          = self::START_STATUS;
               $applicationUseRequest->save();

               $bUserList=$this->user->getMasterUsers();

               foreach($bUserList as $rec) {
                  $check = $this->userGroupRelation->where('user_id',$rec->id)->where('user_group_id',$baseUg->id)->get();

                  if (count($check)==0) {
                    $ugr = new UserGroupRelation();

                    $ugr->user_id       = $rec->id;
                    $ugr->user_group_id = $baseUg->id;
                    $ugr->role_key_code = "文化連管理者";
                    $ugr->save();

                  }
               }

               $result = $this->userGroup->parent($baseUg->id);
               foreach($result as $key => $value) {
                  if ($value->group_type !== \Config::get('const.bunkaren_name')) {
                    $check = $this->userGroupRelation->where('user_id',$this->user->id)->where('user_group_id',$value->id)->get();

                    if (count($check)==0) {
                        $ugr = new UserGroupRelation();

                        $ugr->user_id       = $this->user->id;
                        $ugr->user_group_id = $value->id;
                        $ugr->role_key_code = "参照";
                        $ugr->save();
                    }
                  }
               }
            }

            // トークン取得
            $token = app(\Illuminate\Auth\Passwords\PasswordBroker::class)->createToken($this->user);
            // メール送信
            $this->sendMail(
                'mail.passwordreset',
                $this->user->email,
                $complete_message,
                ['sub_id' => $this->user->sub_id, 'actionUrl' => $request->root() . '/password/reset/' . $token]);
            \DB::commit();
            $request->session()->flash('message', '開始しました');
            return true;
        } catch (\PDOException $e){
            $errMessage = $e->getMessage();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $this->getErrorMessage($errMessage));
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 既存グループ利用申請却下処理
     */
    public function useRejection(Request $request)
    {
        \DB::beginTransaction();
        try {
            $applicationUseRequest = $this->applicationUseRequest->find($request->id);
            $applicationUseRequest->remarks = $request->remarks;
            $applicationUseRequest->status = self::REJECTION_STATUS;
            $applicationUseRequest->save();
            \DB::commit();
            $this->sendMail(
                'mail.rejection',
                $applicationUseRequest->email,
                '既存グループ・個人利用申請について',
                ['body' => $applicationUseRequest->remarks]);
            $request->session()->flash('message', '却下しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 一覧の検索条件
     *
     * @param Request $request リクエスト情報
     */
    public function getConditions(Request $request)
    {
    	$conditions = [];
    	$conditions['name'] = '';
    	$conditions['email'] = '';
    	$conditions['user_group_name'] = '';

    	if ($request->name) {
    		$conditions['name'] = $request->name;
    	}
    	if ($request->email) {
    		$conditions['email'] = $request->email;
    	}
    	if ($request->user_group_name) {
    		$conditions['user_group_name'] = $request->user_group_name;
    	}
        return $conditions;
    }

    /**
     * グループキーのユーザグループIDのロール情報を取得
     *
     * @param string $group_key グループキー
     * @return ロール一覧情報
     */
    public function getRoleList(string $group_key)
    {
        // グループキーでユーザグループIDを取得
        $id = $this->userGroup->select('id')->where('group_key', $group_key)->first()['id'];
        if (!empty($id)) {
            return $this->groupRoleRelation->select('role_key_code as name')->where('user_group_id', $id)->orderby('id')->get();
        }
        return [];
    }

    /**
     * ユーザグループ名取得
     *
     * @param string $group_key グループキー
     * @return string グループ名
     */
    public function getUserGroupName(string $group_key)
    {
        $result = $this->userGroup->select('name')->where('group_key', $group_key)->first();
        return $result->name;
    }

    /**
     * トークンを再発行する。
     * usersテーブルにはハッシュ化済みトークンを登録し、平文トークンを返却する。
     * この平文トークンはsessionには登録せず、画面に表示してユーザに覚えてもらうためのデータとして扱うこと。
     * ※ 下記参考URLはLaravel5.8の実装例であり、現在のLaravel5.7では実現できない
     * ※ 認証処理としてのハッシュ化対応は、vendorフォルダのTokenGuard.phpのuser関数内で行っている。
     * @see https://readouble.com/laravel/5.8/ja/api-authentication.html
     * @param [type] $userId
     * @return string
     */
    public function updateUserToken($userId)
    {
        $newToken = Str::random(60);
        $this->user->where('id', $userId)->update([
            'api_token' => hash('sha256', $newToken)
        ]);
        return $newToken;
    }

    /**
     * primary_user_group_idを更新する
     *
     * @param [type] $primaryUserGroupId
     */
    public function updatePrimaryUserGroupId($primaryUserGroupId)
    {
        $this->user->updatePrimaryUserGroupId($primaryUserGroupId);
    }
}
