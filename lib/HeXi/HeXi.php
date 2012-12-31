<?php

require_once 'Base/Class.php';

require_once 'Core/Error.php';

/**
 *
 * 核心类
 *
 */
class HeXi {

    /**
     * 路由类
     * @var Router
     */
    public $router;

    /**
     * 构造方法
     * @param string $dir
     */
    public function __construct($dir) {
        #定义应用地址
        if (!is_dir($dir)) {
            Error::stop("'$dir' is not invalid !", 404);
        }
        define('APP', $dir);
        $appConfigFile = APP . 'config/app.php';
        if (!is_file($appConfigFile)) {
            Error::stop("Application Configuration file is missing !", 500);
        }
        self::$config['app'] = require($appConfigFile);
        #设置自动加载
        spl_autoload_register('HeXi::import');
        #引入路由类
        $this->router = Router::instance();
    }

    /**
     * 运行程序
     */
    public function run() {
        $res = $this->router->dispatch();
        #返回错误就不返回了
        if ($res === false || $res === null) {
            exit;
        }
        #返回true就认为已经设置好了Response类
        if ($res === true) {
            Response::instance()->send();
            exit;
        }
        #可以当作字符串就设置为内容返回
        if (is_string($res) || is_int($res) || is_float($res)) {
            Response::instance()->content($res)->send();
            exit;
        }
        #如果是资源类型，抛出错误，无法处理
        if (is_resource($res)) {
            Error::stop('Unknown Return Data Type to send', 500);
            exit;
        }
        #其他的类型，当作json返回
        $response              = Response::instance();
        $response->contentType = 'application/json';
        $response->content     = json_encode($res);
        $response->send();
    }

    //-----------------------------------

    /**
     * 配置数组
     * @var array
     */
    public static $config = array();

    /**
     * 引入配置文件
     * @param string $filename
     */
    public static function loadConfig($filename) {
        $appConfigFile = APP . 'config/' . $filename . '.php';
        if (!is_file($appConfigFile)) {
            Error::stop("Application {$filename} Configuration file is missing !", 500);
        }
        self::$config[$filename] = require($appConfigFile);
    }

    /**
     * 引入类库
     * @param string $className
     * @param bool   $stop 错误时是否暂停
     * @return bool
     */
    public static function import($className, $stop = true) {
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }
        #设置几个默认加载的文件地址
        $files = array(
            APP . 'class/' . str_replace('_', '/', $className) . '.php',
            __DIR__ . '/Core/' . $className . '.php',
            __DIR__ . '/' . str_replace('_', '/', $className) . '.php'
        );
        foreach ($files as $file) {
            if (is_file($file)) {
                require_once $file;
                return true;
            }
        }
        if ($stop) {
            Error::stop("'{$className}' Class is missing !", 404);
        }
        return false;
    }

    /**
     * 已经实例化的对象
     * @var array
     */
    private static $objects = array();

    /**
     * 实例化一个对象
     * @param string      $className
     * @param null|string $key 对象的标识
     * @return object
     */
    public static function instance($className, $key = null) {
        $hash = $className . ':' . $key;
        if (!self::$objects[$hash] instanceof $className) {
            self::$objects[$hash] = !$key ? new $className() : new $className($key);
        }
        return self::$objects[$hash];
    }

    /**
     * 销毁一个对象
     * @param string      $className
     * @param null|string $key
     */
    public static function destroy($className, $key = null) {
        $hash = $className . ':' . $key;
        unset(self::$objects[$hash]);
    }

}

