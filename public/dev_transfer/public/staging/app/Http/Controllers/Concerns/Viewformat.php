<?php

namespace App\Http\Controllers\Concerns;

use App\Helpers\Arraydata;

trait Viewformat
{
    /**
     * Formにわたすための配列
     *
     * @var array
     */
    private $formValue = [
        'indata' => [],
        'viewdata' => [],
        'searchdata' => [],
        'master' => [],
        'notice' => [],
        'message' => [],
        'url' => [],
        'debug' => [],
        'slider' => [],
        'login' => [],
    ];
    /**
     * 入力データを取得します。
     *
     * @return void
     */
    public function getViewArray()
    {
        //$this->setViewLogin($this->authInfo());
        return $this->formValue;
    }
    /**
     * Inputデータを設定します。
     *
     * @param array|object $data 入力データを設定します。
     * @return void
     */
    public function setViewInput($data = [])
    {
        $arrayData = new Arraydata($data);
        $this->formValue['indata'] = $arrayData;
        return;
    }
    /**
     * 表示用データを設定します。
     *
     * @param array $data 表示用データを設定します。
     * @return void
     */
    public function setViewData($data = [])
    {
        $arrayData = new Arraydata($data);
        $this->formValue['viewdata'] = $arrayData;
        return;
    }
    /**
     * 検索用データを設定します。
     *
     * @param array $data 検索用データを設定します。
     * @return void
     */
    public function setViewSearch($data)
    {
        $this->formValue['searchdata'] = $data;
        return;
    }
    /**
     * マスターデータを設定します。
     *
     * @param array $data マスター用データを設定します。
     * @return void
     */
    public function setViewMaster($data = [])
    {
        $arrayData = new Arraydata($data);
        $this->formValue['master'] = $arrayData;
        return;
    }
    /**
     * お知らせデータを設定します。
     *
     * @param string $data お知らせ用データを設定します。
     * @return void
     */
    public function setViewNotice($data = null)
    {
        $noticeList = $this->formValue['notice'];
        if ($data != null) {
            if (is_array($data)) {
                $noticeList = array_merge($noticeList, $data);
            } else {
                $noticeList[] = $data;
            }
        }
        $this->formValue['notice'] = $noticeList;
        return;
    }
    /**
     * メッセージ用データを設定します。
     *
     * @param array $data メッセージ用データを設定します。
     * @return void
     */
    public function setViewMessage($data)
    {
        $arrayData = new Arraydata($data);
        $this->formValue['message'] = $arrayData;
        return;
    }
    /**
     * URL用データを設定します。
     *
     * @param array $data URL用データを設定します。
     * @return void
     */
    public function setViewUrl($data)
    {
        $arrayData = new Arraydata($data);
        $this->formValue['url'] = $arrayData;
        return;
    }
    /**
     * デバッグ用データを設定します。
     *
     * @param array $data デバッグ用データを設定します。
     * @return void
     */
    public function setViewDebug($data)
    {
        $arrayData = new Arraydata($data);
        $this->formValue['debug'] = $arrayData;
        return;
    }
    /**
     * スライダー用データを設定します。
     *
     * @param array $data スライダー用データを設定します。
     * @return void
     */
    public function setViewSlider($data)
    {
        $arrayData = new Arraydata($data);
        $this->formValue['slider'] = $arrayData;
        return;
    }
    /**
     * ログイン用データを設定します。
     *
     * @param array $data ログイン用データを設定します。
     * @return void
     */
    public function setViewLogin($data = [])
    {
        $this->formValue['login'] = $data;
        return;
    }
}
