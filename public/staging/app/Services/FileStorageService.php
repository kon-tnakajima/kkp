<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\User;
use App\Model\Task;
use App\Model\Valiation;
use App\Model\FileStorage;
use Exception;
use DateTime;
use App\Services\BaseService;

class FileStorageService extends BaseService
{
    private $fileStorage;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->fileStorage = new FileStorage();
    }

    /*
     * JSONファイルを指定したテーブルにレコード登録する
     * @param Request $request リクエスト情報
     * @return Array 結果
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();
        try {
            if (!method_exists ( $request->file('file') , 'getClientOriginalName')) {
                \DB::rollBack();
                return [
                    'status'        => 'failed',
                    'error_message' => 'ファイル名を取得できませんでした'
                ];
            }
            $file_name = $request->file('file')->getClientOriginalName();
            $this->fileStorage->upload_id = $this->fileStorage->getUploadSequence();
            $this->fileStorage->attachment = encodeByteaData($request->file('file')); //添付ファイル
            $this->fileStorage->file_name = $file_name; //ファイル名
            $this->fileStorage->save();
            \DB::commit();
            return [
                'status'        => 'successful',
                'error_message' => ''
            ];
        } catch (\PDOException $e){
            \DB::rollBack();
			\Log::debug($e->getMessage());
            return [
                'status'        => 'failed',
                'error_message' => '指定テーブルにレコード登録できませんでした'
            ];
        }
    }
}
