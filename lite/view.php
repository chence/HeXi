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

    private static $view;

    /**
     * @static
     * @return view
     */
    public static function init() {
        if (!self::$view instanceof view) {
            self::$view = new view();
        }
        return self::$view;
    }

    private function __construct() {

    }

    public function _clone() {
        throw new Exception('view不能克隆');
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
