<?php
declare(strict_types=1);
namespace App\Services;

use App\User;
use App\Model\Information;
use Illuminate\Http\Request;
use App\Services\BaseService;

class InfoService extends BaseService
{
    const DEFAULT_PAGE_COUNT = 50;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->information = new Information();
    }

    /*
     * 一覧画面
     */
    public function getInfoList(Request $request)
    {
        $count = ($request->page_count) ? $request->page_count : self::DEFAULT_PAGE_COUNT;
        $list = $this->information->getInfoList($request, $count);
        return $list;
    }


    /*
     * ユーザ登録実行
     */
    public function regist(Request $request)
    {
        \DB::beginTransaction();
        try {
            $this->information->title = $request->title;
            $this->information->contents = $request->contents;
            $this->information->category = $request->category;
            $this->information->save();

            \DB::commit();
            $request->session()->flash('message', 'お知らせを追加しました');

            return true;

        } catch (\PDOException $e){
            \DB::rollBack();
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            return false;
        }
    }

    /*
     * お知らせ更新
     */
    public function edit(Request $request)
    {
        \DB::beginTransaction();
        try {
            $info = $this->information->find($request->id);
            $info->title = $request->title;
            $info->contents = $request->contents;
            $info->category = $request->category;
            $info->save();

            \DB::commit();
            $request->session()->flash('message', 'お知らせを更新しました');
            return true;

        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }
    }

    /*
     * 一覧の検索条件
     */
    public function getConditions(Request $request)
    {
        $conditions['keyword'] = (!empty($request->keyword)) ? $request->keyword : "";
        $conditions['category'] = (!empty($request->category)) ? $request->category : [];
        $conditions['start_date'] = (!empty($request->start_date)) ? $request->start_date : "";
        $conditions['end_date'] = (!empty($request->end_date)) ? $request->end_date : "";
        return $conditions;
    }

    /*
     * ダッシュボード用
     */
    public function getDashbordList(Request $request, $count)
    {
        return $this->information->getInfoList($request, $count);
    }

    /*
     * 削除処理
     */
    public function delete(Request $request)
    {
        \DB::beginTransaction();
        try {
            $information = $this->information->find($request->id);
            $information->delete();
            \DB::commit();
            $request->session()->flash('message', '削除しました');
            return true;
        } catch (\PDOException $e){
            $request->session()->flash('errorMessage', '処理に失敗しました' . $e->getMessage());
            \DB::rollBack();
            return false;
        }

    }

}
