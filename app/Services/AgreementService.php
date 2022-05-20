<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Model\Agreement;
use App\Http\Controllers\Concerns\Pager;
use App\User;
use Carbon\Carbon;
use App\Services\BaseService;

class AgreementService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 20;
    const AGREEMENT_FILE_NAME = '規約書_%d.pdf';
    const GUEST_AGREEMENT_FILE_NAME = '規約書.pdf';
    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->agreement = new Agreement();
    }

    /**
     * 規約一覧
     *
     * @param Request $request リクエスト情報
     * @return 権限一覧
     */
    public function getAgreements(Request $request)
    {
        $body  = empty($request->keyword) ? null : '%' . $request->keyword . '%';
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        return $this->agreement
                    ->select('agreements.id',\DB::raw('SUBSTRING(agreements.body,0,20) as body'),'agreements.from_date','agreements.to_date', 'agreements.attachment','users.name','agreements.updated_at')
                    ->leftJoin('users', 'users.id','agreements.updater')
                    ->where(function ($query) use ($body) {
                        if (!empty($body)) {
                            $query->where('body', 'LIKE', $body);
                        }
                    })
                    ->orderBy('id', 'desc')
                    ->paginate($count);
    }

    /*
     * 規約登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $fromDate = new Carbon($request->from_date);
            // 前の終了日が未来期限日（2038-01-01）と一致する日のレコード取得
            $before = $this->agreement->where('to_date', \Config::get('const.agreement_to_date'))->first();
            if (!empty($before)) {
                // 存在するので1日前の日付をセットする。
                $before->to_date = $fromDate->subDay();
                // 存在するので1日前より未来の場合は2日前に修正
                if ($before->from_date > $before->to_date) {
                    $fromDate = new Carbon($request->from_date);
                    $before->from_date = $fromDate->subDay(2);
                }
                $before->save();
            }
            $agreement = new Agreement();
            $agreement->id         = $agreement->getAgreementId();
            $agreement->body       = $request->body;
            $agreement->from_date  = $request->from_date;
            $agreement->to_date    = \Config::get('const.agreement_to_date');  // 2038年1月1日を終了日とする
            if ($request->hasFile('file')) {
                $agreement->file_name = $request->file('file')->getClientOriginalName();
                $agreement->attachment = encodeByteaData($request->file('file'));
            }
            $agreement->save();
            \DB::commit();
            $request->session()->flash('message', '登録しました');
            return true;
        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            return false;
        }
    }

    /**
     * 規約更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $agreement = $this->agreement->find($request->id);
            // 規約情報更新
            if ($request->hasFile('file')) {
                $agreement->file_name  = $request->file('file')->getClientOriginalName();
                $agreement->attachment = encodeByteaData($request->file('file'));
            }
            $agreement->body      = $request->body;
            if (isBunkaren()) {
                $agreement->from_date = $request->from_date;
                $agreement->to_date   = $request->to_date;
            }
            $agreement->save();
            \DB::commit();
            $request->session()->flash('message', '更新しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 削除処理
     */
    public function delete(Request $request)
    {
        \DB::beginTransaction();
        try {
            $agreement = $this->agreement->find($request->id);
            $agreement->delete();
            \DB::commit();
            $request->session()->flash('message', '削除しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /**
     * 一覧の検索条件
     */
    public function getConditions(Request $request)
    {
        $conditions = [];
        $conditions['keyword'] = '';
        if ($request->keyword) {
            $conditions['keyword'] = $request->keyword;
        }
        return $conditions;
    }

    /**
     * ダウンロード
     */
    public function download(Request $request, bool $isGuest = false)
    {
        $now = Carbon::now();
        $agreement = null;
        $fileName = '';
        if ($isGuest) {
            $agreement = $this->agreement->where('from_date', '<=', $now)->where('to_date', '>', $now)->first();
            $fileName = $agreement->file_name; // self::GUEST_AGREEMENT_FILE_NAME;
        } else {
            $agreement = $this->agreement->find($request->id);
            $fileName = $agreement->file_name; // sprintf(self::AGREEMENT_FILE_NAME, $request->id);
        }
        //ダウンロード
        $type = getMimeType(pathinfo($fileName, PATHINFO_EXTENSION) );
        $stream = decodeByteaData($agreement->attachment);//16進デコード
        $headers = array(
            'Content-Type' => $type,
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode($fileName),
        );
        return Response::make($stream, 200, $headers);
    }
}
