<?php
declare(strict_types=1);
namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Model\TransactFileStorage;
use App\Model\TransactDetail;
use App\Observers\AuthorObserver;

class FileStorage extends Model
{
    //
    use SoftDeletes;
    protected $guarded = ['id'];

    /**
     * 初期起動メソッド
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /**
     * 添付ファイル名でファイルストレージ情報取得
     * @param string $file_name ファイル名
     * @return FileStorage
     */
    public function getData(string $file_name)
    {
        return $this->where('file_name', $file_name)->first();
    }

    public function getUploadSequence()
    {
    	return \DB::select("select nextval('upload_id_seq') as upload_id")[0]->upload_id;
    }
}
