<?php
declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\User;
use App\Model\Task;
use App\Model\Valiation;
use App\Model\Medicine;
use App\Model\OtherStorage;
use Exception;
use DateTime;
use App\Services\BaseService;

class UploadOtherService extends BaseService
{
    private $datetime;
	// 対象テーブル（移行先⇒移行元）
	private $arrTableNames = [
		'claim_history_comments'      => 'claim_history_comment_temporaries',
		'facility_medicines'          => 'facility_medicine_temporaries',
		'facility_prices'             => 'facility_price_temporaries',
		'facilities'                  => 'facility_temporaries',
		'group_relations'             => 'group_relation_temporaries',
		'group_role_relations'        => 'group_role_relation_temporaries',
		'group_trader_relations'      => 'group_trader_relation_temporaries',
		'makers'                      => 'maker_temporaries',
		'medicine_effects'            => 'medicine_effect_temporaries',
		'medicine_prices'             => 'medicine_price_temporaries',
		'medicines'                   => 'medicine_temporaries',
		'pack_units'                  => 'pack_unit_temporaries',
		'price_adoptions'             => 'price_adoption_temporaries',
		'privileges'                  => 'privilege_temporaries',
		'role_privilege_relations'    => 'role_privilege_relation_temporaries',
		'roles'                       => 'role_temporaries',
		'transact_confirmations'      => 'transact_confirmation_temporaries',
		'transact_details'            => 'transact_detail_temporaries',
		'transact_file_storages'      => 'transact_file_storage_temporaries',
		'transact_headers'            => 'transact_header_temporaries',
		'user_group_data_types'       => 'user_group_data_type_temporaries',
		'user_group_relations'        => 'user_group_relation_temporaries',
		'user_group_supply_divisions' => 'user_group_supply_division_temporaries',
		'user_groups'                 => 'user_group_temporaries',
		'users'                       => 'user_temporaries'
	];

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->datetime = Carbon::now();
        $this->otherStorage =  new OtherStorage();
    }

    /*
     * ファイルを一時対象テーブルに登録する
     * @param Request $request リクエスト情報
     * @return Array 結果
     */
    public function store(Request $request): array
    {
        \DB::beginTransaction();
        try {
            // 対象区分
            $targetType = $request->target_type ?? null;
            if (!isset($targetType)) {
                return [
                    'id'            => 0,
                    'other_storage' => '',
                    'status'        => 'failed',
                    'error_message' => '対象区分を指定してください'
                ];
            }
            // 対象区分より一時ファイル名を取得
            $fileName = $this->getFileName($targetType);
            if (is_null($fileName)) {
                return [
                    'id'            => 0,
                    'other_storage' => '',
                    'status'        => 'failed',
                    'error_message' => '対象区分が不正です'
                ];
            }
            $this->otherStorage->id = $this->otherStorage->getOtherSequence();
            $this->otherStorage->attachment = encodeByteaData($request->file('file')); //添付ファイル
            $this->otherStorage->file_name = $fileName; //ファイル名
            $this->otherStorage->save();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
			\Log::debug($e->getMessage());
            return [
                'id'            => 0,
                'other_storage' => '',
                'status'        => 'failed',
                'error_message' => '指定テーブルにレコード登録できませんでした'
            ];
        } catch (\PDOException $e){
            \DB::rollBack();
			\Log::debug($e->getMessage());
            return [
                'id'            => 0,
                'other_storage' => '',
                'status'        => 'failed',
                'error_message' => '指定テーブルにレコード登録できませんでした'
            ];
        }
        return [
            'id'            => $this->otherStorage->id,
            'other_storage' => $this->otherStorage->file_name,
            'status'        => 'successful',
            'error_message' => ''
        ];
    }

    /*
     * ファイル名を取得
     * @param string $targetType 対象区分テーブル名
     * @return string ファイル名
     */
    private function getFileName(string $targetType): string
    {
        $suffixName = sprintf("---%04d%02d%02d%02d%02d%02d%02d.tsv",
            $this->datetime->year,
            $this->datetime->month,
            $this->datetime->day,
            $this->datetime->hour,
            $this->datetime->minute,
            $this->datetime->second,
            rand(1, 99));
        if (!array_key_exists($targetType, $this->arrTableNames)) {
            return null;
        }
        return $this->arrTableNames[$targetType].$suffixName;
    }
}
