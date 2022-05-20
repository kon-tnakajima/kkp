<?php
declare(strict_types=1);
namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Model\OtherStorage;

/**
 * 取引明細作成コマンド
 *
 * cronに下記を設定する
 * *\/2 * * * * cd /var/www/html/bunkaren/ && php artisan other_stores:create >> /dev/null 2>&1
 */
class OtherStoreStorages extends Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'other_stores:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'other stores create';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->otherStorage = new OtherStorage();
	    parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		\Log::debug("*** マスタ関連ファイル作成コマンド開始 ***");
    	\DB::beginTransaction();
    	try {
			//1日以上前のother_storagesを削除
			$target = date('Y-m-d H:i:s', strtotime('-1 day', time()));
    		\DB::table('other_storages')->where('created_at', '<', $target)->delete();
    	} catch (\PDOException $exception){
    		session()->flash('errorMessage', '処理に失敗しました' . $exception->getMessage());
			\Log::debug($exception->getMessage());
    		\DB::rollBack();
    		return false;
    	}
		// other_storagesを取得する
		\Log::debug("other_storages取得");
    	$otherStorages = $this->otherStorage->first();
		try {
			if (isset($otherStorages)) {
				$tsvBytea = decodeByteaData($otherStorages->attachment);
				\Storage::disk('local')->put($otherStorages->file_name, $tsvBytea);
				$otherStorages->delete();
			}
			\DB::commit();
		} catch (\PDOException $exception){
			echo $exception->getMessage();
			\Log::debug($exception->getMessage());
			\DB::rollBack();
		}
		\Log::debug("*** マスタ関連ファイル作成コマンド終了 ***");
	}
}
