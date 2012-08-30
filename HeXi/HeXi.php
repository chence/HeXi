<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-8-29
 * Time: 下午7:16
 */
/**
 * @author FuXiaoHei
 */
class HeXi {

    private static function init() {
        date_default_timezone_set(TIMEZONE);
        error_reporting(E_ERROR | E_PARSE | E_STRICT);
        $GLOBALS['DEBUG']['TIME'] = microtime(true);
        $GLOBALS['DEBUG']['MEM'] = memory_get_usage();
        spl_autoload_register('HeXi::load');
    }

    protected static $classes = array(
        'Hook' => 'Core/Hook',
        'Router' => 'Core/Router',
        'Db' => 'Db/Db',
        'Controller' => 'Mvc/Controller',
        'Model' => 'Mvc/Model',
        'View' => 'Mvc/View',
        'Request' => 'Web/Request',
        'Response' => 'Web/Response'
    );

    protected static function load($className) {
        if (!isset(self::$classes[$className])) {
            HeXi::error('无法自动加载库类 "' . $className . '"');
        }
        $file = HEXI_PATH . self::$classes[$className] . '.php';
        if (!is_file($file)) {
            HeXi::error('无法自动加载库类文件 "' . $className . '"');
        }
        require_once $file;
    }

    public static function run() {
        if (!is_dir(APP_PATH)) {
            exit('无效的应用文件夹：' . APP_PATH);
        }
        self::init();
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        Router::init()->run($url, 'all');
    }

    public static function error($message) {
        $exc = new HeXiException($message);
        HeXiException::handler($exc);
    }
}


class HeXiException extends Exception {

    public $type;

    public function __construct($message) {
        parent::__construct($message);
        $trace = $this->getTrace();
        $this->type = $trace[1]['class'];
        #将请求设置为暂停，不响应调用发送方法
        Response::init()->pause = true;
        //Response::$breakSend = true;
    }

    public static function handler(Exception $exc) {
        if ($exc instanceof HeXiException) {
            $string = '[' . $exc->type . ' Exception] ' . $exc->getMessage();
        } else {
            $string = '[' . get_class($exc) . '] ' . $exc->getMessage();
        }
        $response = Response::init();
        $response->pause = false;
        $response->body = $string;
        $response->end();
        exit;
    }
}