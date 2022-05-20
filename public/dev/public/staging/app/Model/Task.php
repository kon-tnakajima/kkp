<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

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


    const STATUS_STR = [
        self::STATUS_UNAPPLIED => '未採用',
        self::STATUS_APPLYING => '申請中',
        self::STATUS_NEGOTIATING => '交渉中',
        self::STATUS_ESTIMATING => '見積中',
        self::STATUS_ESTIMATED => '見積済み',
        self::STATUS_APPROVAL_WAITING => '承認待ち',
        self::STATUS_UNCONFIRMED => '承認済み',
        self::STATUS_ADOPTABLE => '採用可',
        self::STATUS_FORBIDDEN => '採用禁止',
        self::STATUS_DONE => '採用済み',
    ];

    protected $guraded = ['id'];

    /*
     * 現在のタスクIDからタスクを取得する
     */
    public static function current($id)
    {
        return self::where('id', '=', $id)->orderBy('id')->first();
    }

    /*
     * 現タスクIDから次タスクを取得する
     */
    public static function next($id)
    {
        // TODO 検索条件見直し
        return self::where('id', '>', $id)->orderBy('id')->first();
    }

    /*
     * 現タスクIDから前タスクを取得する
     */
    public static function prev($id)
    {
        // TODO 検索条件見直し
        return self::where('id', '<', $id)->orderBy('id', 'desc')->first();
    }

    /*
     * ステータスからタスクを取得
     */
    public static function getByStatus($status)
    {
        // TODO 検索条件見直し
        return self::where('status', $status)->orderBy('id')->first();
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
}
