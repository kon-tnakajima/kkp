<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    // TODO
    /* 旧
    const STATUS_UNAPPLIED = 1;
    const STATUS_APPLYING = 2;
    const STATUS_NEGOTIATING = 3;
    const STATUS_ESTIMATING = 4;
    const STATUS_ESTIMATED = 5;
    const STATUS_APPROVAL_WAITING = 6;
    const STATUS_UNCONFIRMED = 7;
    const STATUS_ADOPTABLE = 8;
    const STATUS_FORBIDDEN = 9;
    const STATUS_DONE = 10;
    const STATUS_EXPIRE = 11;
    const STATUS_STOP = 99;

    const STATUS_STR = [
        self::STATUS_UNAPPLIED => '未採用',
        self::STATUS_APPLYING => '申請中',
        self::STATUS_NEGOTIATING => '交渉中',
        self::STATUS_ESTIMATING => '見積中',
        self::STATUS_ESTIMATED => '見積済み',
        self::STATUS_APPROVAL_WAITING => '承認待ち',
        self::STATUS_UNCONFIRMED => '承認済み',
        self::STATUS_ADOPTABLE => '採用可',
        //self::STATUS_FORBIDDEN => '採用禁止',
        self::STATUS_DONE => '採用済み',
        //self::STATUS_STOP => '採用中止',
        self::STATUS_EXPIRE => '期限切れ',
        //TODO self::STATUS_EXPIRE2 => '本部期限切れ'
    ];*/

    //新
    const STATUS_UNAPPLIED = 1; //未採用
    const STATUS_WAIT_REAPPLY = 10; //再申請待ち
    const STATUS_PASS_REAPPLY = 13; //再申請見送り
    const STATUS_APPLYING = 20; //交渉依頼待ち
    const STATUS_PASS_APPLYING = 23; //交渉依頼見送り
    const STATUS_NEGOTIATING = 30; //見積依頼待ち
    const STATUS_PASS_NEGOTIATING = 33; //見積依頼見送り
    const STATUS_ESTIMATING = 40; //回答待ち
    const STATUS_PASS_ESTIMATING = 43; //回答見送り
    const STATUS_REQUEST= 50; //受諾依頼待ち
    const STATUS_PASS_REQUEST= 53; //受諾依頼見送り
    const STATUS_ENTRUSTMENT =60; //受諾待ち
    const STATUS_PASS_ENTRUSTMENT =63; //受諾見送り
    const STATUS_ESTIMATED = 70; //見積提出待ち
    const STATUS_PASS_ESTIMATED = 73; //見積提出見送り
    const STATUS_APPROVAL_WAITING = 80; //承認待ち
    const STATUS_PASS_APPROVAL_WAITING = 83; //承認見送り
    const STATUS_UNCONFIRMED = 90; //採用待ち
    const STATUS_ADOPTABLE = 91; //採用可
    const STATUS_PASS_UNCONFIRMED = 93; //採用見送り
    const STATUS_DONE = 100; //採用済
    const STATUS_EXPIRE = 110; //期限切れ
    const STATUS_EXPIRE_HONBU = 120; //申請期限切れ


    const STATUS_STR = [
    		self::STATUS_UNAPPLIED => '未採用',
    		self::STATUS_WAIT_REAPPLY => '再申請待ち',
    		self::STATUS_APPLYING => '交渉依頼待ち',
    		self::STATUS_NEGOTIATING => '見積依頼待ち',
    		self::STATUS_ESTIMATING => '見積回答待ち',
    		self::STATUS_REQUEST => '受諾依頼待ち',
    		self::STATUS_ENTRUSTMENT => '受諾待ち',
    		self::STATUS_ESTIMATED => '見積提出待ち',
    		self::STATUS_APPROVAL_WAITING => '承認待ち',
            self::STATUS_UNCONFIRMED => '採用待ち',
            self::STATUS_DONE => '採用済',
    		self::STATUS_ADOPTABLE => '採用可',
            
            self::STATUS_PASS_REAPPLY => '再申請見送り',
            self::STATUS_PASS_APPLYING => '交渉依頼見送り',
            self::STATUS_PASS_NEGOTIATING => '見積依頼見送り',
            self::STATUS_PASS_ESTIMATING => '見積回答見送り',//'受諾依頼見送り',
            self::STATUS_PASS_REQUEST => '受諾依頼見送り',//'見積回答見送り',
            self::STATUS_PASS_ENTRUSTMENT => '受諾見送り',
            self::STATUS_PASS_ESTIMATED => '見積提出見送り',
            self::STATUS_PASS_APPROVAL_WAITING => '承認見送り',
            self::STATUS_PASS_UNCONFIRMED => '採用見送り',
            self::STATUS_EXPIRE => '期限切れ'
    ];

    const STATUS_STR_MIN = [
        self::STATUS_DONE => '採用済',
        self::STATUS_ADOPTABLE => '採用可' 
];

//     const STATUS_STR = [
//         self::STATUS_UNAPPLIED => '未採用',
//         self::STATUS_WAIT_REAPPLY => '再申請待ち',
//         self::STATUS_PASS_REAPPLY => '再申請見送り',
//         self::STATUS_APPLYING => '交渉依頼待ち',
//         self::STATUS_PASS_APPLYING => '交渉依頼見送り',
//         self::STATUS_NEGOTIATING => '見積依頼待ち',
//         self::STATUS_PASS_NEGOTIATING => '見積依頼見送り',
//         self::STATUS_ESTIMATING => '見積回答待ち',
//         self::STATUS_PASS_ESTIMATING => '見積回答見送り',
//         self::STATUS_REQUEST => '受諾依頼待ち',
//         self::STATUS_ENTRUSTMENT => '受諾待ち',
//         self::STATUS_PASS_ENTRUSTMENT => '受諾見送り',
//         self::STATUS_ESTIMATED => '見積提出待ち',
//         self::STATUS_APPROVAL_WAITING => '承認待ち',
//         self::STATUS_PASS_APPROVAL_WAITING => '承認見送り',
//         self::STATUS_UNCONFIRMED => '採用待ち',
//         self::STATUS_PASS_UNCONFIRMED => '採用見送り',
//         self::STATUS_ADOPTABLE => '採用可',
//         self::STATUS_DONE => '採用済',
//         self::STATUS_EXPIRE => '期限切れ',
//         self::STATUS_EXPIRE_HONBU => '申請期限切れ',
// ];

    //請求
    // 旧
    /*
    const STATUS_UNCLAIMED = 1;
    const STATUS_INVOICING = 2;
    const STATUS_TRADER_CONFIRM = 3;
    const STATUS_TRADER_CLAIMED = 4;
    const STATUS_CONFIRM = 5;
    const STATUS_CLAIMED = 6;
    const STATUS_CLAIMOK = 7;
    const STATUS_COMPLETE = 8;
    const STATUS_INVALID = 9;

    const STATUS_STR2 = [
        self::STATUS_UNCLAIMED => '未登録',
        self::STATUS_INVOICING => '登録中',
        self::STATUS_TRADER_CONFIRM => '受付済',
        self::STATUS_TRADER_CLAIMED => '明細登録済',
        self::STATUS_CONFIRM => '支払確認中',
        self::STATUS_CLAIMED => '施設確認中',
        self::STATUS_CLAIMOK => '施設確認済',
        self::STATUS_COMPLETE => '確定',
        self::STATUS_INVALID => '無効',
    ];*/

    // 新仮
    const STATUS_UNCLAIMED = 10;
    const STATUS_INVOICING = 20;
    const STATUS_TRADER_CONFIRM = 30;
    const STATUS_TRADER_CLAIMED = 4;
    const STATUS_CONFIRM = 5;
    const STATUS_CLAIMED = 6;
    const STATUS_CLAIMOK = 7;
    const STATUS_COMPLETE = 8;
    const STATUS_INVALID = 9;

    const STATUS_STR2 = [
        self::STATUS_UNCLAIMED => '未登録',
        self::STATUS_INVOICING => '登録中',
        self::STATUS_TRADER_CONFIRM => '受付済',
        self::STATUS_TRADER_CLAIMED => '明細登録済',
        self::STATUS_CONFIRM => '支払確認中',
        self::STATUS_CLAIMED => '施設確認中',
        self::STATUS_CLAIMOK => '施設確認済',
        self::STATUS_COMPLETE => '確定',
        self::STATUS_INVALID => '無効',
    ];




    // TODO あとで新に変更 下記はメールで使用している
    //const STATUS_UNCLAIMED = 1; //未登録
    //const STATUS_INVOICING = 2; // '登録中',
    //const STATUS_CONFIRM = 5; //'確認待ち', これないが独自で名称きってる
    //const STATUS_CLAIMED =6; //'確認済',

    // 新 はTasksのDBに取得するように変更
    //tasks apply_payment_flg 3:請求登録
    // 10 照合待ち
    // 13 却下
    // 20 照合中
    // 23 却下
    // 30 明細登録済
    // 33 却下
    //tasks apply_payment_flg 4:請求照会_業者
    // 1 削除
    // 5 相違あり
    // 10 未確認
    // 20 確認済み
    // 30 業者確認待ち
    // 40 確定
    //tasks_apply_payment_flg 5:請求照会_施設
    // 1 削除
    // 5 相違あり
    // 10 未確認
    // 20 確認済み
    // 30 施設確認待ち
    // 40 確定


    //アカウント申請
    const STATUS_NEWACCOUNT = 1;
    const STATUS_READY = 3;


    protected $guraded = ['id'];

    /*
     * 現在のタスクIDからタスクを取得する
     */
    public static function current($id)
    {
        return self::where('id', '=', $id)->where('apply_payment_flg', '=', '0')->orderBy('id')->first();
    }

    /*
     * 現タスクIDから次タスクを取得する
     */
    public static function next($id)
    {
        // TODO 検索条件見直し
        return self::where('id', '>', $id)->where('apply_payment_flg', '=', '0')->orderBy('id')->first();
    }

    /*
     * 現タスクIDから前タスクを取得する
     */
    public static function prev($id)
    {
        // TODO 検索条件見直し
        return self::where('id', '<', $id)->where('apply_payment_flg', '=', '0')->orderBy('id', 'desc')->first();
    }

    /*
     * 請求画面毎のステータスを取得する
     */
    public static function getStatusClaimList($apply_payment_flg)
    {
        $taskList = self::select('status')->where('apply_payment_flg', '=', $apply_payment_flg)->orderBy('status')->get();

        return $taskList;
    }



    /*
     * ステータスからタスクを取得
     */
    public static function getByStatus($status)
    {
        // TODO 検索条件見直し
        return self::where('status', $status)->where('apply_payment_flg', '=', '0')->orderBy('id')->first();
    }

    /*
     * ステータス値から文字列を取得
     */
    public static function getStatus($status)
    {
        if (is_null($status)) {
            $status = self::STATUS_UNAPPLIED;
        }
        return self::STATUS_STR[$status];
    }

    /* --------------------------------------------- 請求
     * ステータスからタスクを取得
     */
    public static function getByStatusForClaim($status, $target_actor_id)
    {
        // TODO 検索条件見直し
        return self::where('status', $status)->where('apply_payment_flg', '1')->orderBy('id')->first();
    }

    /*
     * ステータス値から文字列を取得 TODO ここが影響ある。ステータスから名称を取得するが、キーが必要な気がする
     */
    public static function getStatusForClaim_old($status)
    {
        if (is_null($status)) {
            $status = self::STATUS_UNCLAIMED;
        }
        return self::STATUS_STR2[$status];
    }

     /*
     * ステータス値から文字列を取得
     */
    public static function getStatusName($status, $apply_payment_flg)
    {
    	$targetStatus = self::select('name')->where('status', $status)->where('apply_payment_flg', $apply_payment_flg)->orderBy('id')->first();
        $status_name = "";
        if (!empty($targetStatus)) {
           $status_name = $targetStatus->name;
        }
        return $status_name;
    }

    /*
     * 現在のタスクIDからタスクを取得する
     */
    public static function currentForClaim($id, $target_actor_id)
    {
        return self::where('id', '=', $id)->where('apply_payment_flg', '1')->orderBy('id')->first();
    }

    /*
     * 現タスクIDから次タスクを取得する
     */
    public static function nextForClaim($id, $target_actor_id)
    {
        // TODO 検索条件見直し
        return self::where('id', '>', $id)->where('apply_payment_flg', '1')->where('target_actor_id', $target_actor_id)->orderBy('id')->first();
    }

    /*
     * 現タスクIDから前タスクを取得する
     */
    public static function prevForClaim($id, $target_actor_id)
    {
        // TODO 検索条件見直し
        return self::where('id', '<', $id)->where('apply_payment_flg', '1')->where('target_actor_id', $target_actor_id)->orderBy('id', 'desc')->first();
    }
}
