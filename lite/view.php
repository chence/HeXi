<?php
/**
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
class view {

    protected $path;

    protected $is_compile;

    protected $compile_path;

    public static function init() {

    }

    public function render($file) {

    }

    public function display($file) {
        echo $this->render($file);
    }

    public static function compile_string($string) {

    }

    public static function compile_layout($file) {

    }
}
