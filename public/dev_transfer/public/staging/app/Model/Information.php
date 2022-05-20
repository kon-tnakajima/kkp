<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Observers\AuthorObserver;
use Illuminate\Http\Request;

class Information extends Model
{
    use SoftDeletes;

    const CATEGORY_NEWS = 1;
    const CATEGORY_PRICE = 2;
    const CATEGORY_HR = 3;


    const CATEGORY_STR = [
        self::CATEGORY_NEWS => 'ニュース',
        self::CATEGORY_PRICE => '価格',
        self::CATEGORY_HR => '人事',
    ];

    protected $fillable = [
        'title',
        'contents',
        'category',
        'deleter', 
        'creater', 
        'updater'
    ];

    public static function boot()
    {
        parent::boot();
        self::observe(new AuthorObserver());
    }

    /*
     * ステータス値から文字列を取得
     */
    public function getCategory()
    {
        return self::CATEGORY_STR[$this->category];
    }

    /*
     * お知らせ一覧
     */
    public function getInfoList(Request $request, $count)
    {
        $query = $this->select('*');
        if (!empty($request->keyword)) {

            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->keyword . '%')
                ->orWhere('contents', 'like', '%' . $request->keyword . '%');
            });
        }
        if (!empty($request->category)) {
            if (count($request->category) > 0) {
                $query->whereIn('category', $request->category);
            }
        }

        if (!empty($request->start_date)) {
            $query->where('updated_at', '>=', $request->start_date);
        }

        if (!empty($request->end_date)) {
            $query->where('updated_at', '<=', $request->end_date . ' 23:59:59');
        }
        $query->orderBy('updated_at', 'desc');
        return $query->paginate($count);

    }

    /*
     * 作成者の名前を取得
     */
    public function getCreater()
    {
        return $this->belongsTo('App\User', 'creater');
    }
}
