<?php
declare(strict_types=1);
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Model\FileStorage;
use App\Model\UploadTransactDetail;
use App\Model\MailQue;
use App\Model\Task;
use App\Model\TransactFileStorage;
use App\Services\MailQueService;
use App\Model\UserGroup;


/**
 * 取引明細作成コマンド
 *
 * cronに下記を設定する
 * *\/2 * * * * cd /var/www/html/bunkaren/ && php artisan transact_details:create >> /dev/null 2>&1
 */
class CreateTransactDetails extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transact_details:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'transact_details create';

	// 一時格納ファイル名
	private const UPLOAD_TRANSACT_DETAIL_FILENAME = 'temp_upload_transact_detail.tsv';
	// 一時テーブル名
	private $tempTableName = 'upload_transact_detail_temporaries';
	// 実テーブル名
	private $tableName = 'upload_transact_details';
	// 主キーカラム名
	private const PRIMARY_KEY_NAME = 'id';

	private $targetColumns = [
		'bunkaren_trading_history_code' => 'COALESCE(bunkaren_trading_history_code, \'\')',
		'bunkaren_billing_code' => 'COALESCE(bunkaren_billing_code, \'\')',
		'bunkaren_payment_code' => 'COALESCE(bunkaren_payment_code, \'\')',
		'claim_invoice_id' => 'COALESCE(claim_invoice_id, 0)',
		'claim_payment_id' => 'COALESCE(claim_payment_id, 0)',
		'bunkaren_item_code' => 'COALESCE(bunkaren_item_code, \'\')',
		'facility_item_code' => 'COALESCE(facility_item_code, \'\')',
		'trader_item_code' => 'COALESCE(trader_item_code, \'\')',
		'gs1_sscc' => 'COALESCE(gs1_sscc, \'\')',
		'maker_name' => 'COALESCE(maker_name, \'\')',
		'item_name' => 'COALESCE(item_name, \'\')',
		'standard' => 'COALESCE(standard, \'\')',
		'item_code' => 'COALESCE(item_code, \'\')',
		'jan_code' => 'COALESCE(jan_code, \'\')',
		'gtin_code' => 'COALESCE(gtin_code, \'\')',
		'quantity' => 'COALESCE(quantity, 0)',
		'unit_name' => 'COALESCE(unit_name, \'\')',
		'is_stock_or_sale' => 'COALESCE(is_stock_or_sale, 0)',
		'tax_division' => 'COALESCE(tax_division, 0)',
		'sales_slip_number' => 'COALESCE(sales_slip_number, \'\')',
		'buy_slip_number' => 'COALESCE(buy_slip_number, \'\')',
	];

	private const TRANSACT_DETAILS_CHANNEL_NAME = 'バッチ処理';
	private const SLACK_USER_NAME = 'transact_details create';

	/**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
		parent::__construct();
        $this->fileStorage = new FileStorage();
        $this->uploadTransactDetail = new UploadTransactDetail();
        $this->mailQue = new MailQue();
        $this->transactFileStorage = new TransactFileStorage();
        $this->userGroup = new UserGroup();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		echo "取引明細作成コマンド開始";
		\Log::debug("*** 取引明細作成コマンド開始 ***");

    	\DB::beginTransaction();
    	try {
			//1日以上前のfile_storagesを削除
			$target = date('Y-m-d H:i:s', strtotime('-5 day', time()));
    		\DB::table('file_storages')->where('created_at', '<', $target)->delete();
    	} catch (\PDOException $e){
    		\DB::rollBack();
			sendChatMessage(
				self::TRANSACT_DETAILS_CHANNEL_NAME,
				self::SLACK_USER_NAME,
				'【transact_details create】['.$e->getMessage() . ']'
			);
    		return false;
    	}

		//file_storagesを取得する
    	echo "file_storages取得";
		\Log::debug("file_storages取得");
    	$fileStorages = $this->fileStorage->get();
		$uploadIds= array();

		// テンポラリテーブルへ格納処理
		$basePath = base_path() . '/storage/app/'. self::UPLOAD_TRANSACT_DETAIL_FILENAME;
		$db_host = \Config::get('database.connections.pgsql.host');
		$db_name = \Config::get('database.connections.pgsql.database');
		$db_user = \Config::get('database.connections.pgsql.username');

		//CSVファイルから取引明細を作成する
		try {
			foreach($fileStorages as $storage) {
				// 一時ファイルが存在する場合は削除
				if (true === \Storage::disk('local')->exists(self::UPLOAD_TRANSACT_DETAIL_FILENAME)) {
					\Storage::disk('local')->delete(self::UPLOAD_TRANSACT_DETAIL_FILENAME);
				}
				// TSVファイルローカルへ格納
				\Storage::disk('local')->put(self::UPLOAD_TRANSACT_DETAIL_FILENAME, decodeByteaData($storage->attachment));
				// 一時テーブルの全レコード削除
				//$cmd = "echo \"TRUNCATE TABLE {$this->tempTableName}\" | /usr/bin/psql -U {$db_user} -h {$db_host} {$db_name}";
				//\Log::debug("command[{$cmd}]");
				// $last_line = shell_exec($cmd);
				//$last_line = exec($cmd);
				//\Log::debug("result[{$last_line}]");
				//if (empty($last_line)) {
				//	throw new Exception('TRUNCATE TABLE実行失敗しました。');
				//}
				//if (strpos($last_line, 'TRUNCATE TABLE') === false) {
				//	throw new Exception('一時テーブルの削除できませんでした。');
				//}
				\DB::table('upload_transact_detail_temporaries')->delete();

	            // COPY処理
    	        $cmd = "echo \"\\COPY {$this->tempTableName} FROM '{$basePath}' CSV DELIMITER E'\\t' HEADER;\" | /usr/bin/psql -U {$db_user} -h {$db_host} {$db_name}";
	            \Log::debug("command[{$cmd}]");
    	        // $last_line = shell_exec($cmd);
				$last_line = exec($cmd);
        	    \Log::debug("result[{$last_line}]");
				if (empty($last_line)) {
					throw new Exception('COPY実行失敗しました。');
				}
	            if (strpos($last_line,'COPY') === false) {
					throw new Exception('一時テーブルにCOPY実行できませんでした。');
        	    }
				// カラム生成
				$columns = $this->getColumns($this->tempTableName, $this->tableName, $storage);
				// 一時テーブルより実テーブルへ移行
				\DB::insert("INSERT INTO {$this->tableName} ({$columns[0]}) SELECT {$columns[1]} FROM {$this->tempTableName}");

				$all_rec = \DB::select("select to_char(claim_date,'YYYY/MM') as claim_month,sales_user_group_id,purchase_user_group_id from upload_transact_details where upload_id={$storage->upload_id}");

				// ストアドプロシージャ実行
			 	\DB::select("select f_deployment_transact_detail({$storage->upload_id})");
				// ローカルファイル削除
				\Storage::disk('local')->delete(self::UPLOAD_TRANSACT_DETAIL_FILENAME);


				//メール送信準備
				if (!empty($all_rec)){
					$salesUserGroup = $this->userGroup->getUserGroup($all_rec[0]->sales_user_group_id);
					$traderUserGroup = $this->userGroup->getUserGroup($all_rec[0]->purchase_user_group_id);

					$mail_users_facility = $this->transactFileStorage->getEmailClaimDetail(
						$all_rec[0]->sales_user_group_id,
						$all_rec[0]->purchase_user_group_id,
						10, //初期ステータス：施設金額未確認 
						10, //初期ステータス：業者金額未確認 
						1
					);
					$mail_users_trader = $this->transactFileStorage->getEmailClaimDetail(
						$all_rec[0]->sales_user_group_id,
						$all_rec[0]->purchase_user_group_id,
						10, //初期ステータス：施設金額未確認
						10, //初期ステータス：業者金額未確認
						2
					);

					$mq_facility = new MailQueService($this->mailQue->getMailQueID());
					$mq_trader = new MailQueService($this->mailQue->getMailQueID());

					$mq_facility->send_mail_add_claim_detail(
						$mail_users_facility,
						$salesUserGroup[0]->name,
						10, //初期ステータス：施設金額未確認
						$all_rec[0]->claim_month,
						1,
						$traderUserGroup[0]->name,
						0
					);

					$mq_trader->send_mail_add_claim_detail(
						$mail_users_trader,
						$salesUserGroup[0]->name,
						10,  //初期ステータス：業者金額未確認 
						$all_rec[0]->claim_month,
						2,
						$traderUserGroup[0]->name,
						0
					);



				}

				 // ファイルストレージの削除
				$storage->delete();
			}
			\DB::commit();
		} catch (\Exception $e){
			// ファイル関連のエラー処理
			echo $e->getMessage();
			\Log::debug($e->getMessage());
			\DB::rollBack();
			sendChatMessage(
				self::TRANSACT_DETAILS_CHANNEL_NAME,
				self::SLACK_USER_NAME,
				'【transact_details create】['.$e->getMessage() . ']'
			);
		} catch (\PDOException $e){
			echo $e->getMessage();
			\Log::debug($e->getMessage());
			\DB::rollBack();
			sendChatMessage(
				self::TRANSACT_DETAILS_CHANNEL_NAME,
				self::SLACK_USER_NAME,
				'【transact_details create】['.$e->getMessage() . ']'
			);
		}
		echo "*** 取引明細作成コマンド終了 ***";
		\Log::debug("*** 取引明細作成コマンド終了 ***");
	}

    /**
     * 移行元から移行先のカラム情報取得
	 *
     * @param string $tempTableName 一時テーブル名
	 * @param string $destTableName 移行先テーブル名
	 * @param FileStorage $storage  ファイル格納テーブル
     * @return array [0]=インサートカラム名(カンマ区切り文字列), [1]=カラム名+実データ
     */
	private function getColumns(string $tempTableName, string $destTableName, FileStorage $storage): array
	{
		// 移行元テーブルよりカラム取得
		$tempColumns = Schema::getColumnListing($tempTableName);
		// 移行先テーブルよりカラム取得
		$destColumns = Schema::getColumnListing($destTableName);
		// 基本は、移行先を軸にカラムを揃える
		$destColumn = $dataColumn = '';
		foreach ($destColumns as $value) {
			// 存在する？
			if (in_array($value, $tempColumns)) {
				// 主キーカラム名
				if ($value === self::PRIMARY_KEY_NAME) {
					continue;
				}
				if ($destColumn) {
					$destColumn .= ',';
					$dataColumn .= ',';
				}
				$destColumn .= $value;
				if (array_key_exists($value, $this->targetColumns)) {
					$dataColumn .= $this->targetColumns[$value];
				} else {
					$dataColumn .= $value;
				}
			}
		}
		// INSERT INTO tablename {$destColumn} select {$dataColumn} from temp_table_name;
		$destColumn .= ',upload_id,creater,updater,created_at,updated_at';
		$dataColumn .= ",{$storage->upload_id} as upload_id,{$storage->creater} as creater,{$storage->updater} as updater,now() as created_at,now() as updated_at";
		$result = [$destColumn, $dataColumn];
		return $result;
	}
}
