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
        #设置自动加载
        spl_autoload_register('HeXi::import');
        #引入路由类
        $this->router = Router::instance();
    }

    /**
     * 运行程序
     */
    public function run() {
        $this->router->dispatch();
    }

    //-----------------------------------

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

}

