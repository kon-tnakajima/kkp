<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\User;
use Exception;
use DateTime;
use App\Services\BaseService;

class TableService extends BaseService
{
    // usersテーブルのこのアカウントを除外
    private const FILE_STORAGER_DIRECTORY = 'filedownload';
    private const LIMIT_FILE_SIZE_MB = 512;
    private const MEMORY_LIMIT_SIZE_MB = 128;
    private const TRANSACT_FILE_STORAGES_TABLE_NAME = 'transact_file_storages';
    private const ATTACHMENT_COLUMN_NAME = 'attachment';
    private const ATTACHMENT_COLUMN_NAME2 = 'attachment_send';
    private const ATTACHMENT_COLUMN_NAME3 = 'attachment_receive';
    private const PDF_EXTENSION = '.pdf';
    // JSON出力APIでの使用メモリ容量制限
    private const TABLE_LIMIT_MEMORY_MB = 100;
    // JSON出力APIでの最大レコード取得数
    private const LIMIT_COUNT_NUMBER = 10000;
    // 対象テーブル(29テーブル)
    private $arrTableNames = [
        'claim_history_comments',
        //'claim_invoices',
        //'claim_payments',
        'facility_medicines',
        'facility_prices',
        //'facility_groups',
        //'facility_relations',
        //'facilities',
        'group_relations',
    	'group_role_relations',
        'makers',
    	'medicine_effects',
    	'medicine_prices',
    	'medicines',
    	'other_registration_errors',
        'pack_units',
    	'price_adoptions',
    	'privileges',
    	'role_privilege_relations',
    	'roles',
        'transact_confirmations',
    	'transact_details',
    	'transact_file_storages',
    	'transact_headers',
    	'user_group_data_types',
        'user_group_relations',
    	'user_group_supply_divisions',
    	'user_groups',
    	'users',
    	'file_storages',
    	'tasks',
    	'group_trader_relations'
    ];

    // ファイル格納対象テーブル
    private $arrFileStorageTableNames = [
        //'claim_invoices', 'claim_payments', 'transact_file_storages', 'file_storages'
        'transact_file_storages', 'file_storages'
    ];

    /*
     * コンストラクタ
     */
    public function __construct()
    {
    }

    /*
     * 指定テーブルの条件よりレコード取得
     * @param Request $request リクエスト情報
     * @return Array 一覧
     */
    public function getList(Request $request)
    {
        try {
            // テーブル名が指定なし
            if (!strlen($request->table_name ?? '')) {
                return $this->resultDate($request, 0, [], 'テーブル名を指定してください');
            }
            // 対象のテーブルが存在するか確認
            if (!in_array($request->table_name, $this->arrTableNames)) {
                return $this->resultDate($request, 0, [], '指定したテーブルは存在しません');
            }
            // テーブル名指定
            $cond = \DB::table($request->table_name);
            if ($this->validateDate($request->last_date ?? 'null')) {
                $cond->where('updated_at', '>', $request->last_date);
            }
            // attachmentカラムを除外
            $result = array_diff(Schema::getColumnListing($request->table_name), [self::ATTACHMENT_COLUMN_NAME],[self::ATTACHMENT_COLUMN_NAME2],[self::ATTACHMENT_COLUMN_NAME3]);
            // バイナリカラム除外
            $cond->select($result);
            if ($this->getScopeId($request)) {
                $cond->whereBetween('id', [$request->idf, $request->idt]);
            }
            // APIトークンのみ利用するユーザは除外する。
            if ($request->table_name === 'users') {
                $cond->where('api_token', '');
            }
            /*
            $count = $cond->count();
            // 1万件超えたら終了
            if (self::LIMIT_COUNT_NUMBER < $count) {
                return $this->resultDate($request, 0, [], '取得レコード数が最大値を超えました');
            }
            */

            $list = $cond->orderBy('id', 'asc')->get();
            if (!count($list)) {
                return $this->resultDate($request, 0, [], '指定されたテーブルには情報がありませんでした');
            }
            /*
            $useMemory = (int)(memory_get_usage() / (1024 * 1024));
            // 使用メモリ100MB超えたら終了(通常は128MB想定)
            if (self::TABLE_LIMIT_MEMORY_MB < $useMemory) {
                return $this->resultDate($request, 0, [], '使用メモリを超えてしまいましたので作業を終了しました');
            }
            */
            return $this->resultDate($request, count($list), $list);
        } catch (\PDOException $e){
			\Log::debug($e->getMessage());
            return $this->resultDate($request, 0, [], '指定したテーブルの情報を取得できませんでした');
        }
    }

    /*
     * ID範囲確認
     * @param Request $request リクエスト情報
     * @return boolean true=利用可 false=NG
     */
    private function getScopeId(Request $request): bool
    {
        // 開始ID確認
        if ($request->idf === null ||
            $request->idf === 'null') {
            return false;
        }
        // 終了ID確認
        if ($request->idt === null ||
            $request->idt === 'null') {
            return false;
        }
        // 数値確認
        if (is_numeric($request->idf) && is_numeric($request->idt)) {
            return true;
        }
        return false;
    }

    /**
     * 複数ファイルをローカルフォルダに格納しレスポンス情報を返す
     *
     * @param Request $request リクエスト情報
     * @return レスポンス情報
     */
    public function putFiles(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            \Log::debug('開始');
            // ディレクトリ削除
            \Storage::disk('local')->deleteDirectory(self::FILE_STORAGER_DIRECTORY);
            if (!strlen($request->table_name ?? '')) {
                throw new Exception('テーブル名を指定してください');
            }
            // 対象のテーブルが存在するか確認
            if (!in_array($request->table_name, $this->arrFileStorageTableNames)) {
                throw new Exception('指定したテーブルは存在しません');
            }
            // テーブル名指定
            $cond = \DB::table($request->table_name);
            if ($this->validateDate($request->last_date ?? 'null')) {
                $cond->where('updated_at', '>', $request->last_date);
            }
            if ($this->getScopeId($request)) {
                $cond->whereBetween('id', [$request->idf, $request->idt]);
            }
            // $storages = $cond->orderBy('id', 'asc')->get();
            $cond->orderBy('id', 'asc');

            $arrFileNames = [];
            foreach ($cond->cursor() as $row) {

            	$tmpAry = explode('.', $row->file_name);
            	$max_cnt = count($tmpAry);
            	if ($max_cnt > 1) {
            		$extension = $tmpAry[$max_cnt - 1];
            		$fileName = sprintf("%s_%d.%s", $request->table_name, $row->id, $extension);
            	} else {
            		$fileName = sprintf("%s_%d", $request->table_name, $row->id);
            	}

            	$arrFileNames[] = $fileName;
            	\Log::debug('ローカルにファイル格納する前');
                // ローカルにファイル格納する
                \Storage::disk('local')->put(self::FILE_STORAGER_DIRECTORY.DIRECTORY_SEPARATOR.$fileName, decodeByteaData($row->attachment));
            }
            if (!count($arrFileNames)) {
                throw new Exception('指定されたテーブルには情報がありませんでした');
            }
          	\Log::debug('ZipArchive前');
            $za = new \ZipArchive();
            $basePath = base_path() . DIRECTORY_SEPARATOR. 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR. self::FILE_STORAGER_DIRECTORY . DIRECTORY_SEPARATOR;
            $zipFilePath = $basePath . $request->table_name . '.zip';
            if ($za->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === false) {
                throw new Exception('圧縮ファイルを新規作成できませんでした');
            }
            $totalSize = 0;
            foreach ($arrFileNames as $fileName) {
                // ローカルファイル内の実ファイル容量が全て64Mを超える場合は、ダウンロードできないエラーを出力。
                $totalSize += \File::size($basePath . $fileName);
                // \Log::debug(sprintf("現在のファイル容量[%d]MB", $totalSize/(1024*1024)));
                if (self::LIMIT_FILE_SIZE_MB < (int)($totalSize/(1024*1024))) {
                    throw new Exception('全ファイル容量が64MBを超えましたのでダウンロードできません');
                }
                if ($za->addFile($basePath . $fileName, $fileName) === false) {
                    throw new Exception('圧縮ファイルに指定ファイルをエントリ出来ませんでした');
                }
            }
            // 容量を超えたようなのでエラー
            // \Log::debug('作業容量1['.(memory_get_usage() / (1024 * 1024)).']');
            if (self::MEMORY_LIMIT_SIZE_MB < (memory_get_usage() / (1024 * 1024))) {
                throw new Exception('メモリ容量を超えたので処理ができません');
            }
            if ($za->close() === false) {
                throw new Exception('圧縮ファイルを作成できませんでした');
            }
            // \Log::debug('作業容量2['.(memory_get_usage() / (1024 * 1024)).']');
            // 容量確認
            //********************************************************************
            // $zip = new \ZipArchive;
            // if ($zip->open($zipFilePath) === true) {
            //     $loop = 0;
            //     while (1) {
            //         $res = $zip->statIndex($loop);
            //         if ($res === false) {
            //             break;
            //         }
            //         \Log::debug(print_r($res, true));
            //         $loop++;
            //     }
            //     $zip->close();
            // }
            // \Log::debug('作業容量3['.(memory_get_usage() / (1024 * 1024)).']');
            //********************************************************************
            $filename = $request->table_name . '.zip';
            $headers = [
                'Content-Type' => 'application/zip',
                'Content-disposition' => "attachment; filename={$filename}",
                'Content-Length'  => filesize($zipFilePath)
            ];
            return response()->download($zipFilePath, $filename, $headers);
        } catch (\Exception $e){
            \Log::debug($e->getMessage());
            return $this->resultDate($request, 0, [], $e->getMessage());
        }
    }

    /*
     * 結果配列を取得
     * @param Request $request リクエスト情報
     * @param string $format 日時フォーマット
     * @return array 結果配列
     */
    private function resultDate(Request $request, int $count, $details, string $error = '')
    {
        return [
            'table_name'    => $request->table_name,
            'last_date'     => $request->last_date,
            'idf'           => $request->idf ?? 0,
            'idt'           => $request->idt ?? 0,
            'count'         => $count,
            'error_message' => $error,
            'details'       => $details
        ];
    }

    /*
     * 日時を確認する
     * @param string $date 日時
     * @param string $format 日時フォーマット
     * @return boolean true=OK false=NG
     */
    private function validateDate(string $date, string $format = 'Y-m-d H:i:s'): bool
    {
        if ($date === null) return false;
        if ($date === 'null') return false;
        \Log::debug($date);
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}
