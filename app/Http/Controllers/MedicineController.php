<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Services\MedicineService;
use Illuminate\Http\Request;
use App\Http\Controllers\Concerns\Pager;

class MedicineController extends Controller
{
    const PAGER_DISPLAY_COUNT = 20;
    protected $function_id = 1;
    private $medicineService;

    use Pager;

    /*
     * コンストラクタ
     */
    public function __construct()
    {
        $this->service = new MedicineService();
    }
    /*
     * 標準薬品一覧
     */
    public function index(Request $request)
    {
        $conditions = $this->service->getConditions($request);
        $list = $this->service->getMedicineList($request);

        $page_count = MedicineService::DEFAULT_PAGE_COUNT;
        if(!empty($request->page_count)){
            $page_count = $request->page_count;
        }

        // 一覧取得
        $this->setViewData([
            'list' => $list,
            'page_count' => $page_count,
            'conditions' => $conditions,
            'pager' => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'message' => $request->session()->get('message', '')
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }
    
    /*
     * 採用申請詳細
     */
    public function detail(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getMedicineDetail($request),
         'errorMessage' => $request->session()->get('errorMessage', ''),
         'makers' => $this->service->getMakers(),
         'medicine_effects' => $this->service->getMedicineEffects(),
         'message' => $request->session()->get('message', ''),
        ]);
    }

    /*
     * 採用申請修正
     */
    public function edit(Request $request)
    {
        // TODO
        // この情報は誰が修正できるのか確認(施設 or 本部 or 文化連)

        // TODO バリデーション
        $request->validate([
//            'purchase_price' => 'required|numeric',
//            'sales_price' => 'required|numeric',
        ]);

        // 実行
        $this->service->edit($request);
        return redirect()->route('medicine.detail', ['id' => $request->id]);
    }
    
    /*
     * 採用申請詳細
     */
    public function regist(Request $request)
    {
        return view(\Route::currentRouteName(),  
        ['detail' => $this->service->getMedicineDetail($request),
         'errorMessage' => $request->session()->get('errorMessage', ''),
         'makers' => $this->service->getMakers(),
         'medicine_effects' => $this->service->getMedicineEffects(),
         'message' => $request->session()->get('message', ''),
        ]);
    }

    /*
     * 採用申請実行
     */
    public function regexec(Request $request)
    {
        if (!$this->service->regist($request)) {
            // TODOエラーメッセージ
        }
        return redirect()->route('medicine.index');
    }
}
