<?php

namespace App\Services;

use Illuminate\Http\Request;

class ApplyConditionSetting {
    public $name;
    private $label;
    private $default_value;
    private $exist_value;

    /**
     * コンストラクタ
     */
    public function __construct($name, $label, $default_value, $exist_value = null) {
        $this->name = $name;
        $this->label = $label;
        $this->default_value = $default_value;
        $this->exist_value = $exist_value;
    }

    public function setConditions(&$conditions, $request) {
        $prop_name = $this->name;
        if (!empty($request->$prop_name)) {
            $conditions[$prop_name] = isset($this->exist_value) ? $this->exist_value : $request->$prop_name;
        } else {
            $conditions[$prop_name] = $this->default_value;
        }
    }

    public function setConditionsDefault(&$conditions) {
        $prop_name = $this->name;
        $conditions[$prop_name] = $this->default_value;
    }
}
