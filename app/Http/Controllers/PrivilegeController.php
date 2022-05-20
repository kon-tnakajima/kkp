<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\PrivilegeService;
use App\Http\Controllers\Concerns\Pager;
use App\Model\Privilege;
use Illuminate\Support\Facades\Response;

class PrivilegeController extends BaseController
{
    const PAGER_DISPLAY_COUNT = 20;
    use Pager;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = new PrivilegeService();
    }

    /**
     * 権限一覧
     * 
     * @param Request $request リクエスト情報
     */
    public function index(Request $request)
    {
        $list = $this->service->getPrivileges($request);
        $page_count = (!empty($request->page_count)) ? $request->page_count : PrivilegeService::DEFAULT_PAGE_COUNT;
        // 一覧取得
        $this->setViewData([
            'list'       => $list,
            'conditions' => $this->service->getConditions($request),
            'pager'      => $this->getPager($list, self::PAGER_DISPLAY_COUNT),
            'page_count' => $page_count,
            'message'    => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 登録画面
     *
     * @param Request $request リクエスト情報
     */
    public function add(Request $request)
    {
        $this->setViewData([
            'page'         => empty($request->page) ? null: $request->page,
            'types'        => \Config::get('const.privilege_type'),
            'errorMessage' => $request->session()->get('errorMessage', ''),
            'message'      => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 権限登録実行
     */
    public function regist(Request $request)
    {
        $request->validate(
            $this->getValidation()
        );
        if (!$this->service->regist($request) ) {
            return redirect()->route('privilege.add')->withInput();
        }
        return redirect()->route('privilege.index');
    }

    /**
     * 権限編集画面
     */
    public function detail(Request $request)
    {
        $privilege = Privilege::find($request->id);
        $types = \Config::get('const.privilege_type');
        $name = '';
        foreach($types as $value) {
            if ($privilege->privilege_type === $value['id']) {
                $name = $value['name'];
            } 
        }
        if(!empty($request->page)){
            $privilege->page = $request->page;
        }
        $this->setViewData([
            'detail'            => $privilege,
            'privilegeTypeName' => $name,
            'errorMessage'      => $request->session()->get('errorMessage', ''),
            'message'           => $request->session()->get('message', ''),
        ]);
        return view(\Route::currentRouteName(),  $this->getViewArray());
    }

    /**
     * 権限編集実行
     */
    public function edit(Request $request)
    {
        if (!$this->service->edit($request)) {
            return redirect()->route('privilege.detail', ['id' => $request->id])->withInput();
        }
        return redirect()->route('privilege.detail', ['id' => $request->id]);
    }

    /**
     * 削除実行
     */
    public function delete(Request $request)
    {
        if (!$this->service->delete($request)) {
            return redirect()->route('privilege.detail', ['id' => $request->id]);
        }
        return redirect()->route('privilege.index');
    }

    /*
     * 採用品一覧CSV
     */
    public function download(Request $request)
    {
        $user = Auth::user();
        $list = $this->service->getPrivilegesCsv($request);
        $stream = fopen('php://temp', 'r+b');
        foreach ($list as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $csv = str_replace(PHP_EOL, "\r\n", stream_get_contents($stream));
        $csv = mb_convert_encoding($csv, 'SJIS-win', 'UTF-8');
        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename*=UTF-8''".urlencode('権限一覧_').date('YmdHis').'.csv',
        );
        return Response::make($csv, 200, $headers);
    }

    /**
     * validation
     */
    public function getValidation()
    {
        return [
            'name'           => 'required',
            'key_code'       => 'required',
            'privilege_type' => 'required',
        ];
    }
}
