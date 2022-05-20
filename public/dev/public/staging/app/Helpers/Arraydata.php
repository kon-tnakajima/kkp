<?php

namespace App\Helpers;

/**
 * 配列系クラスの規定のメソッドを用意します。
 */
class Arraydata
{
    /**
     * 値の連想配列
     *
     * @var array
     */
    protected $data = [];
    /**
     * 初期化処理
     *
     * @param array|object $valueList 初期データ
     */
    public function __construct($valueList = [])
    {
        $this->data = $valueList;
    }
    /**
     * 値を設定する。
     *
     * @param string $key キーが入ります。
     * @param mixed $value 値が入ります。
     * @return void
     */
    public function set($key, $value)
    {
        $this->data["$key"] = $value;
        return;
    }
    /**
     * 値を取得する。
     *
     * @param string $key キーが入ります。
     * @param mixed $value 値が入ります。
     * @return void
     */
    public function get($key, $value = '')
    {
        if (isset($this->data["$key"])) {
            $value = $this->data["$key"];
        }
        return $value;
    }
    /**
     * 設定されている値を消します。
     *
     * @param string $key キーが入ります。
     * @return void
     */
    public function unset($key)
    {
        unset($this->data["$key"]);
        return;
    }
    /**
     * 値が存在するかチェックをします。
     *
     * @param string $key キーが入ります。
     * @return boolean
     *  true 存在する。
     *  false 存在しない。
     */
    public function has($key)
    {
        if (isset($this->data["$key"])) {
            return !empty($this->data["$key"]);
        }
        return false;
    }
    /**
     * 設定されている値を返します。
     *
     * @return string 結果を返します。
     */
    public function __toString()
    {
        return print_r($this->data, true);
    }
}
