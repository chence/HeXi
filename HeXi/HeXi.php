<?php

/**
 * HeXi核心库
 * @author FuXiaoHei
 */
class HeXi {

    /**
     * 初始化
     */
    private static function init() {
        #设置时区，错误提示和自动加载
        date_default_timezone_set(TIMEZONE);
        error_reporting(E_ERROR | E_PARSE);
        $GLOBALS['DEBUG']['TIME'] = microtime(true);
        $GLOBALS['DEBUG']['MEM'] = memory_get_usage();
        spl_autoload_register('HeXi::load');
    }

    /**
     * 自动加载的类别
     * @var array
     */
    protected static $classes = array(
        'Hook' => 'Core/Hook',
        'Router' => 'Core/Router',
        'Db' => 'Db/Db',
        'Controller' => 'Mvc/Controller',
        'Model' => 'Mvc/Model',
        'View' => 'Mvc/View',
        'Request' => 'Web/Request',
        'Response' => 'Web/Response',
        'Upload'=>'Web/Upload'
    );

    /**
     * 自动加载方法
     * @param string $className
     */
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

    /**
     * 运行应用
     */
    public static function run() {
        #判断应用文件夹
        if (!is_dir(APP_PATH)) {
            exit('无效的应用文件夹：' . APP_PATH);
        }
        self::init();
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        #运行路由类，并开启过滤
        Router::init()->run($url, 'all');
    }

    /**
     * 触发错误
     * @param string $message
     */
    public static function error($message) {
        $exc = new HeXiException($message);
        HeXiException::handler($exc);
    }

    /**
     * 自动加载
     * @param string $command
     */
    public static function import($command){
        $command = explode('.',$command);
        $first = array_shift($command);
        $file = '';
        if($first == 'HeXi'){
            $file = HEXI_PATH.join(DS,$command).'.php';
        }
        if($first == 'App'){
            $file = APP_PATH.join(DS,$command).'.php';
        }
        if(!is_file($file)){
            HeXi::error('无法引入文件 "'.$file.'"');
        }
        require_once $file;
    }
}


/**
 * 异常处理类
 */
class HeXiException extends Exception {

    /**
     * 异常类的触发类或函数
     * @var string
     */
    public $type;

    /**
     * 异常类
     * @param string $message
     */
    public function __construct($message) {
        parent::__construct($message);
        $trace = $this->getTrace();
        $this->type = $trace[1]['class'];
        #将请求设置为暂停，不响应调用发送方法
        Response::init()->pause = true;
        //Response::$breakSend = true;
    }

    /**
     * 处理异常
     * @param Exception $exc
     */
    public static function handler(Exception $exc) {
        $response = Response::init();
        $response->pause = false;
        if (EXCEPTION_SILENT) {
            $response->status = 500;
            $string = '系统出现错误';
        } else {
            if ($exc instanceof HeXiException) {
                $string = '<strong>[' . $exc->type . ' Exception]</strong> ' . $exc->getMessage();
            } else {
                $string = '<strong>[' . get_class($exc) . ']</strong> ' . $exc->getMessage();
            }
            $string = '<span style="font-family:\'Consolas\';color:#E32529;line-height:2em;font-size:14px">' . $string . '</span>';
        }
        $response->body = $string;
        $response->end();
        exit;
    }
}


/**
 * 公共函数，调用类
 * @param string $command
 */
function import($command){
    HeXi::import($command);
}

