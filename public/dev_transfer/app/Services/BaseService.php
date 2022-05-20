<?php
declare(strict_types=1);
namespace App\Services;

class BaseService {
    private function get_caller_str($add_level = 0) {
        $dbg = debug_backtrace();
        return sprintf('%s#%s', get_class($this), $dbg[2 + $add_level]['function']);
    }

    protected function start_log($add_level = 0) {
        logger(sprintf('#    【START】 [%s]', $this->get_caller_str($add_level)));
    }

    protected function end_log($add_level = 0) {
        logger(sprintf('#    【END  】 [%s]', $this->get_caller_str($add_level)));
    }
}
