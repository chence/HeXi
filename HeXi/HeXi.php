<?php

/**
 * 引入常用函数类，定义一些常用函数到全局
 */
require_once 'common.php';

/**
 * 核心类
 * @author FuXiaoHei
 */
class HeXi {

    /**
     * 配置信息常量
     * @var array
     */
    public static $config;

    /**
     * 运行应用程序
     * @param array $config
     */
    public static function run($config) {
        #检测应用文件夹是否有效
        if (!is_dir(APP_PATH)) {
            exit('Your web application directory is invalid !');
        }
        #加载配置信息
        self::$config = $config;
        self::$classes = config('class');
        #初始化程序，如自动加载等
        self::init();
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        #运行路由类，并开启过滤
        Router::init()->run($url, 'all');
    }

    /**
     * 初始化
     */
    private static function init() {
        #设置时区，错误提示和自动加载
        date_default_timezone_set(TIMEZONE);
        error_reporting(E_ERROR | E_PARSE);
        $GLOBALS['DEBUG']['TIME'] = microtime(true);
        $GLOBALS['DEBUG']['MEM'] = memory_get_usage();
        spl_autoload_register('HeXi::autoLoader');
    }

    /**
     * 自动加载函数
     * @param string $className
     */
    public static function autoLoader($className) {
        if (!isset(self::$classes[$className])) {
            HeXi::error('无法自动加载库类 "' . $className . '"');
        }
        self::import('HeXi.' . self::$classes[$className]);
    }

    /**
     * 预设的自动加载的类库
     * @var array
     */
    private static $classes = array();

    /**
     * 引入文件
     * @param string $classStr
     */
    public static function import($classStr) {
        $command = explode('.', $classStr);
        $first = array_shift($command);
        $file = '';
        if ($first == 'HeXi') {
            $file = HEXI_PATH . join(DS, $command) . '.php';
        }
        if ($first == 'App') {
            $file = APP_PATH . join(DS, $command) . '.php';
        }
        if (!is_file($file)) {
            HeXi::error('无法引入文件 "' . $file . '"');
        }
        require_once $file;
    }

    /**
     * 提交错误
     * @param string $message
     */
    public static function error($message) {
        $exc = new HeXiException($message);
        $exc->output();
    }
}

/**
 * 异常类
 */
class HeXiException extends Exception {

    /**
     * 初始化
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message);
        #暂停返回类的输出，忽略所有发送请求的函数
        Response::init()->pause = true;
    }

    /**
     * 输出错误
     */
    public function output() {
        $response = Response::init();
        #取消返回类的暂停，允许发送返回请求
        $response->pause = false;
        #如果错误安静模式，就只提示错误了
        if (config('error.silent')) {
            $response->status = 500;
            $response->body = '系统出现错误';
            $response->end();
            exit;
        }
        #简单输出错误信息
        $trace = $this->getTrace();
        $class = $trace[1]['class'];
        if ($class) {
            $string = '<strong>[' . $class . ' Exception]</strong> ' . $this->getMessage();
        } else {
            $string = '<strong>['.$trace[2]['class'].' Exception]</strong> ' . $this->getMessage();
        }
        $string = '<span style="font-family:\'Consolas\';color:#E32529;line-height:2em;font-size:14px">' . $string . '</span>';
        $response->status = 500;
        $response->body = $string;
        $response->end();
        exit;
    }
}
