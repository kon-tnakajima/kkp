<?php
declare(strict_types=1);
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Model\OtherStorage;

/**
 * 取引明細作成コマンド
 *
 * cronに下記を設定する
 * *\/2 * * * * cd /var/www/html/bunkaren/ && php artisan other_registrations >> /dev/null 2>&1
 */
class OtherRegistrations extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'other_registrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'other registrations create update';
    // 主キーカラム名
    private const PRIMARY_KEY_NAME = 'id';
    // バイナリファイルカラム除外の為
    private const ATTACHMENT_KEY_NAME = 'attachment';
    private const ATTACHMENT_KEY_NAME2 = 'attachment_send';
    private const ATTACHMENT_KEY_NAME3 = 'attachment_receive';
    // usersテーブルのapi_tokenカラム除外の為
    private const API_TOKEN_KEY_NAME = 'api_token';
    private const CREATER_KEY_NAME = 'creater';
    private const UPDATER_KEY_NAME = 'updater';
    private const CREATED_AT_KEY_NAME = 'created_at';
    private const UPDATED_AT_KEY_NAME = 'updated_at';

    // インサートは除外の為
    private const ARRAY_REJECTION_TABLE = ['transact_file_storage_temporaries'];
    // 対象テーブル（移行元⇒移行先）
    private $arrTableNames = [
        'claim_history_comment_temporaries'      => 'claim_history_comments',
        //'claim_invoice_temporaries'              => 'claim_invoices',
        //'claim_payment_temporaries'              => 'claim_payments',
        'facility_medicine_temporaries'          => 'facility_medicines',
        'facility_price_temporaries'             => 'facility_prices',
        'group_relation_temporaries'             => 'group_relations',
        'group_role_relation_temporaries'        => 'group_role_relations',
        'group_trader_relation_temporaries'      => 'group_trader_relations',
        'maker_temporaries'                      => 'makers',
        'medicine_effect_temporaries'            => 'medicine_effects',
        'medicine_price_temporaries'             => 'medicine_prices',
        'medicine_temporaries'                   => 'medicines',
        'pack_unit_temporaries'                  => 'pack_units',
        'price_adoption_temporaries'             => 'price_adoptions',
        'privilege_temporaries'                  => 'privileges',
        'role_privilege_relation_temporaries'    => 'role_privilege_relations',
        'role_temporaries'                       => 'roles',
        'transact_confirmation_temporaries'      => 'transact_confirmations',
        'transact_detail_temporaries'            => 'transact_details',
        //'transact_file_storage_temporaries'      => 'transact_file_storages',
        'transact_header_temporaries'            => 'transact_headers',
        'user_group_data_type_temporaries'       => 'user_group_data_types',
        'user_group_relation_temporaries'        => 'user_group_relations',
        'user_group_supply_division_temporaries' => 'user_group_supply_divisions',
        'user_group_temporaries'                 => 'user_groups',
        'user_temporaries'                       => 'users',
//テーブル追加による対応
    	'action_name_status_forward_temporaries' => 'action_name_status_forwards',
    	'button_control_temporaries'             => 'button_controls',
    	'is_ok_temporaries'                      => 'is_ok',
    	'price_adoption_log_temporaries'         => 'price_adoption_logs',
    	'revert_temporaries'                     => 'reverts',
    ];
	// エラーテーブル登録SQL部分
	private $otherRegErrorSql = 'INSERT INTO other_registration_errors(table_name, id_tablekey, id_send, created_at, updated_at)';

	private $otherStorage;

	// private const OTHER_STORAGE_LOG_CHANNEL_NAME = 'other_storage_log';
	private const OTHER_STORAGE_LOG_CHANNEL_NAME = 'バッチ処理';
	private const SLACK_USER_NAME = 'other storage';

	/**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
	    parent::__construct();
        $this->otherStorage = new OtherStorage();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		\Log::debug("*** 対象一時テーブルをマスタテーブルへマージ開始 ***");
		try {
			// その他ファイル格納テーブルの5日以上のレコードは削除
			\DB::beginTransaction();
			try {
				//5日以上前のother_storagesを削除
				$target = date('Y-m-d H:i:s', strtotime('-5 day', time()));
				\DB::table('other_storages')->where('created_at', '<', $target)->delete();
			} catch (\PDOException $exception){
				\Log::debug($exception->getMessage());
				\DB::rollBack();
				return false;
			}
			\DB::commit();
			$now = date('Y/m/d H:i:s');
ini_set('memory_limit', '2G');
			// 一時テーブルを軸にループ
			foreach($this->arrTableNames as $key => $value) {
				// 一時テーブル情報確認
				$counts = \DB::table($key)->count();
				\Log::debug("{$key}[".$counts."]");
				if (!$counts) {
					continue;
				}
				\Log::debug('一時テーブル名['.$key.']');
				\Log::debug('移行先テーブル名['.$value.']');
				\DB::beginTransaction();
				// 対象テーブル項目名取得
				$columns = $this->getColumns($key, $value);
				// updated_atが不一致のレコードをエラーログテーブルに格納
				\DB::insert("{$this->otherRegErrorSql} SELECT '{$value}' AS table_name, dist.id, dist.id_send, now(), now() FROM {$key} AS source INNER JOIN {$value} as dist ON dist.id = source.id AND dist.updated_at != source.updated_at AND source.id IS NOT NULL");
				// IDが存在するレコードをUPDATE処理
				\DB::update("UPDATE {$value} SET {$columns} FROM {$key} AS source WHERE source.id = {$value}.id AND source.updated_at = {$value}.updated_at AND source.id IS NOT NULL");
				// attachmentカラムが存在するものは除外
				if (array_key_exists($key, self::ARRAY_REJECTION_TABLE) === false) {
					// テンポラリよりid IS NULLを取得
					$results = \DB::select("SELECT * FROM {$key} WHERE id IS NULL");
					foreach ($results as $col) {
						// インサート時には条件付与
						$condisions = $this->getConditions($value, $col);
						if ($condisions) {
							$rows = \DB::select("SELECT * FROM {$value} WHERE {$condisions} limit 1");
							if (!count($rows)) {
								// 存在しない、実テーブルに登録
								unset($col->id);
								if (property_exists($col, self::API_TOKEN_KEY_NAME) === true) {
									unset($col->api_token);
								}

								if (property_exists($col, self::CREATER_KEY_NAME) === true) {
									$col->creater = 206;
								}
								if (property_exists($col, self::UPDATER_KEY_NAME) === true) {
									$col->updater = 206;
								}
								if (property_exists($col, self::CREATED_AT_KEY_NAME) === true) {
									$col->created_at = "'".$now."'";
								}
								if (property_exists($col, self::UPDATED_AT_KEY_NAME) === true) {
									$col->updated_at = "'".$now."'";
								}

								\DB::table($value)->insert([(array)$col]);
							} else {
								// 存在する、エラーログテーブルへ登録（件数が単数であればOK、複数は、1件目を登録
								\DB::insert("{$this->otherRegErrorSql} values ('{$value}', ?, ?, now(), now())",[$rows[0]->id, $rows[0]->id_send]);
							}
						} else {
							// エラーログテーブルにidがNULLで複合キー一致している情報を登録
							\DB::insert("{$this->otherRegErrorSql} value ('{$value}', NULL, NULL, now(), now())");
						}
					}
				}
				// 削除処理
				\DB::table($key)->delete();
				\DB::commit();
			}
		} catch (\PDOException $e){
			\Log::debug($e->getMessage());
			\DB::rollBack();
		}
		\Log::debug("*** 対象一時テーブルをマスタテーブルへマージ終了 ***");
	}

	/**
	 * 条件を追加(INSERT時の条件のみ対応)
	 * @param string $destTableName 移行先テーブル名
	 * @param object $col テンポラリテーブルのカラム情報
     * @return string 条件文字列
	 */
	private function getConditions(string $destTableName, $col): string
	{
		$tempTableName = null;
		$cond = null;
		switch ($destTableName) {
			case 'claim_history_comments':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.trading_history_id = {$col->trading_history_id}
					AND {$destTableName}.transact_confirmation_id = {$col->transact_confirmation_id}";
				break;
			case 'facility_medicines':
				// 条件(このカラムは一部NOT NULLです。NULLはmedicine_idです)
				$cond = "{$destTableName}.sales_user_group_id = '{$col->sales_user_group_id}' ";
				if (isset($col->medicine_id)) {
					$cond .= "AND COALESCE({$destTableName}.medicine_id, 0) = {$col->medicine_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.medicine_id, 0) = 0 ";
				}
				//$cond .= "AND {$destTableName}.adoption_date = '{$col->adoption_date}' ";
				//$cond .= "AND {$destTableName}.deleted_at is null ";
				break;
			case 'facility_prices':
				// 条件(このカラムは全てNULLありです。)
				/*
				if (isset($col->facility_medicine_id)) {
					$cond = "COALESCE({$destTableName}.facility_medicine_id, 0) = {$col->facility_medicine_id} ";
				} else {
					$cond = "COALESCE({$destTableName}.facility_medicine_id, 0) = 0 ";
				}
				*/

				$cond = "1=1";
				if (isset($col->medicine_id)) {
					$cond .= " AND COALESCE({$destTableName}.medicine_id, 0) = {$col->medicine_id} ";
				} else {
					$cond .= " AND COALESCE({$destTableName}.medicine_id, 0) = 0 ";
				}
				if (isset($col->sales_user_group_id)) {
					$cond .= " AND COALESCE({$destTableName}.sales_user_group_id, 0) = {$col->sales_user_group_id} ";
				} else {
					$cond .= " AND COALESCE({$destTableName}.sales_user_group_id, 0) = 0 ";
				}
				if (isset($col->purchase_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = {$col->purchase_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = 0 ";
				}
				if (isset($col->start_date)) {
					$cond .= "AND COALESCE({$destTableName}.start_date, '1970/01/01') = '{$col->start_date}' ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.start_date, '1970/01/01') = '1970/01/01' ";
				}
				break;
			case 'group_relations':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
						AND {$destTableName}.partner_user_group_id = {$col->partner_user_group_id}";
				break;
			case 'group_role_relations':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
						AND {$destTableName}.role_key_code = '{$col->role_key_code}'";
				break;
			case 'group_trader_relations':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
				AND {$destTableName}.trader_user_group_id = '{$col->trader_user_group_id}'";
				break;

			case 'makers':
				// 条件(このカラムは全てNULLありです。)
				if (isset($col->maker_code)) {
					$cond = "COALESCE({$destTableName}.maker_code, 'UNKNOWN') = '{$col->maker_code}' ";
				} else {
					$cond = "COALESCE({$destTableName}.maker_code, 'UNKNOWN') = 'UNKNOWN' ";
				}
				if (isset($col->start_date)) {
					$cond .= "AND COALESCE({$destTableName}.start_date, '1970/01/01') = '{$col->start_date}' ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.start_date, '1970/01/01') = '1970/01/01' ";
				}
				break;
			case 'medicine_effects':
				// 条件(このカラムはNULLありです。)
				if (isset($col->code)) {
					$cond = "COALESCE({$destTableName}.code, 'UNKNOWN') = '{$col->code}' ";
				} else {
					$cond = "COALESCE({$destTableName}.code, 'UNKNOWN') = 'UNKNOWN' ";
				}
				break;
			case 'medicine_prices':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.medicine_id = {$col->medicine_id}
						AND {$destTableName}.start_date = '{$col->start_date}'";
				break;
			case 'medicines':
				// 条件(このカラムはNULLありです。)
				/*
				if (isset($col->sales_packaging_code)) {
					// NULL以外
					$cond = "COALESCE({$destTableName}.sales_packaging_code, 'UNKNOWN') = '{$col->sales_packaging_code}'";
				} else {
					// NULLの場合
					$cond = "COALESCE({$destTableName}.sales_packaging_code, 'UNKNOWN') = 'UNKNOWN'";
				}
				*/
				$cond = " 1 = 2";
				break;
			case 'pack_units':
				// 条件(このカラムはNULLありです。)
				/*
				if (isset($col->jan_code)) {
					// NULL以外
					$cond = "COALESCE({$destTableName}.jan_code, 'UNKNOWN') = '{$col->jan_code}'";
				} else {
					// NULLの場合
					$cond = "COALESCE({$destTableName}.jan_code, 'UNKNOWN') = 'UNKNOWN'";
				}
				$cond = "COALESCE({$destTableName}.jan_code, 'UNKNOWN') = 'UNKNOWN'";
				*/
				$cond = " 1 = 2";
				break;
			case 'price_adoptions':
				// price_adoptionsは再見積があるのでキー重複レコードのinsertも許容する
				// insertした場合、トリガー：f_trg_price_adoption_migration でキー重複レコードをPA_logsテーブルにinsertし、PAテーブルからはdeleteする。
				// 条件(このカラムはNULLありです。)
				/*
				if (isset($col->medicine_id)) {
					$cond = "COALESCE({$destTableName}.medicine_id, 0) = {$col->medicine_id} ";
				} else {
					$cond = "COALESCE({$destTableName}.medicine_id, 0) = 0 ";
				}
				if (isset($col->sales_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = {$col->sales_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = 0 ";
				}
				if (isset($col->purchase_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = {$col->purchase_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = 0 ";
				}
				$cond .= "AND {$destTableName}.deleted_at is null ";
				*/
				$cond = " 1 = 2";
				break;
			case 'privileges':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.key_code = '{$col->key_code}'
						AND {$destTableName}.privilege_type = {$col->privilege_type}";
				break;
			case 'role_privilege_relations':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.role_key_code = '{$col->role_key_code}'
						AND {$destTableName}.privilege_key_code = '{$col->privilege_key_code}'";
				break;
			case 'roles':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.key_code = '{$col->key_code}'";
				$cond .= "AND {$destTableName}.deleted_at is null ";
				break;
			case 'transact_confirmations':
				// 条件(このカラムはNULLありです)
				if (isset($col->claim_month)) {
					$cond = "COALESCE({$destTableName}.claim_month, '1970/01/01') = '{$col->claim_month}' ";
				} else {
					$cond = "COALESCE({$destTableName}.claim_month, '1970/01/01') = '1970/01/01' ";
				}
				if (isset($col->sales_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = {$col->sales_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = 0 ";
				}
				if (isset($col->purchase_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = {$col->purchase_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = 0 ";
				}
				if (isset($col->supply_division)) {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = {$col->supply_division} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = 0 ";
				}
				break;
			case 'transact_details':
				/*
				// 条件(このカラムはNULLありです)
				if (isset($col->claim_date)) {
					$cond = "COALESCE({$destTableName}.claim_date, '1970/01/01') = '{$col->claim_date}' ";
				} else {
					$cond = "COALESCE({$destTableName}.claim_date, '1970/01/01') = '1970/01/01' ";
				}
				if (isset($col->sales_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = {$col->sales_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = 0 ";
				}
				if (isset($col->purchase_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = {$col->purchase_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.purchase_user_group_id, 0) = 0 ";
				}
				if (isset($col->supply_division)) {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = {$col->supply_division} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = 0 ";
				}
				*/
				$cond=" 1=1 ";
				break;
			case 'transact_headers':
				// 条件(このカラムはNULLありです)
				if (isset($col->claim_month)) {
					$cond = "COALESCE({$destTableName}.claim_month, '1970/01/01') = '{$col->claim_month}' ";
				} else {
					$cond = "COALESCE({$destTableName}.claim_month, '1970/01/01') = '1970/01/01' ";
				}
				if (isset($col->supply_division)) {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = {$col->supply_division} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.supply_division, 0) = 0 ";
				}
				if (isset($col->sales_user_group_id)) {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = {$col->sales_user_group_id} ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.sales_user_group_id, 0) = 0 ";
				}
				break;
			case 'user_group_data_types':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
						AND {$destTableName}.data_type_name = '{$col->data_type_name}'
						AND {$destTableName}.deleted_at is null ";
				break;
			case 'user_group_relations':
				// 条件(このカラムは一部NULLでそれ以外はNOT NULLです)
				$cond = "{$destTableName}.user_id = {$col->user_id}
						AND {$destTableName}.user_group_id = {$col->user_group_id} ";
				if (isset($col->role_key_code)) {
					$cond .= "AND COALESCE({$destTableName}.role_key_code, 'UNKNOWN') = '{$col->role_key_code}' ";
				} else {
					$cond .= "AND COALESCE({$destTableName}.role_key_code, 'UNKNOWN') = 'UNKNOWN' ";
				}
				$cond .= "AND {$destTableName}.deleted_at is null ";
				break;
			case 'user_group_supply_divisions':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
						AND {$destTableName}.supply_division_name = '{$col->supply_division_name}'
						AND {$destTableName}.deleted_at is null ";
				break;
			case 'user_groups':
				// 条件条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.name = '{$col->name}'";
				$cond .= "AND {$destTableName}.deleted_at is null ";
				break;
			case 'users':
				// 条件
				$cond = "{$destTableName}.email = '{$col->email}' ";
				if (isset($col->sub_id)) {
					$cond .= "AND COALESCE({$destTableName}.sub_id, 'UNKNOWN') = '{$col->sub_id}'";
				} else {
					$cond .= "AND COALESCE({$destTableName}.sub_id, 'UNKNOWN') = 'UNKNOWN'";
				}
				break;
			case 'is_ok':
				// 条件(このカラムは全てNOT NULLです)
				$cond = "{$destTableName}.user_group_id = {$col->user_group_id}
				AND {$destTableName}.data_type_name = '{$col->data_type_name}'";
				break;

		}
		return $cond;
	}

    /**
     * 移行元から移行先のカラム情報取得
	 * ※条件として移行先・元にカラムが存在しない場合は移行対象外
	 *
     * @param string $tempTableName 一時テーブル名
	 * @param string $destTableName 移行先テーブル名
     * @return string カラム一覧(カンマ区切り文字列)
     */
	private function getColumns(string $tempTableName, string $destTableName): string
	{
		$now = date('Y/m/d H:i:s');
		// 移行元テーブルよりカラム取得
		$tempColumns = Schema::getColumnListing($tempTableName);
		// 移行先テーブルよりカラム取得
		$destColumns = Schema::getColumnListing($destTableName);
		// 基本は、移行先を軸にカラムを揃える
		$column = '';
		foreach ($destColumns as $value) {
			// 存在する？
			if (in_array($value, $tempColumns)) {
				// 主キー,バイナリデータ(不要ですがお守り),api_tokenは除外
				if ($value === self::PRIMARY_KEY_NAME || $value === self::ATTACHMENT_KEY_NAME || $value === self::ATTACHMENT_KEY_NAME2 || $value === self::ATTACHMENT_KEY_NAME3 || $value === self::API_TOKEN_KEY_NAME || $value === self::CREATER_KEY_NAME || $value === self::CREATED_AT_KEY_NAME) {
					continue;
				}

				if ($column) {
					$column .= ',';
				}

				if ($value === self::UPDATER_KEY_NAME) {
					$column .= $value . '= 206 ';

				} else if ($value === self::UPDATED_AT_KEY_NAME) {
					$column .= $value . "='".$now."'";

				} else {
					$column .= $value . '=' . 'source.'. $value;
				}
			}
		}
		return $column;
	}
}

