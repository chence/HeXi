<?php
/**
 * 应用程序类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 */
class app {

    /**
     * 开启运行
     * @static
     *
     */
    public static function run() {
        self::begin_debug();
        spl_autoload_register('app::auto_load');
        set_exception_handler('app::error');
        self::import_config();
        self::router();
    }

    /**
     * 载入配置
     * @static
     * @throws Exception
     */
    private static function import_config() {
        $file = App_Path . 'config.ini';
        if (!is_file($file)) {
            throw new Exception('无法载入配置文件 config.ini');
        }
        $GLOBALS['config'] = parse_ini_file($file, true);
    }

    /**
     * 开启debug
     * @static
     *
     */
    private static function begin_debug() {
        $GLOBALS['debug']['time_begin'] = microtime(true);
        $GLOBALS['debug']['mem_begin'] = memory_get_usage();
    }

    /**
     * 运行路由
     * @static
     * @throws Exception
     */
    private static function router() {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $paths = explode('.', $path);
        if (count($paths) > 2) {
            throw new Exception('太多.的URL');
        }
        $GLOBALS['url']['ext'] = isset($paths[1]) ? $paths[1] : '';
        $param = explode('/', $paths[0]);
        $param_value = array_filter($param);
        if (count($param) - count($param_value) > 2) {
            throw new Exception('太多/的URL');
        }
        $param_value = array_values($param_value);
        $GLOBALS['url']['param'] = $param_value;
        //------------------
        $controller = null;
        $offset = 0;
        if ($param_value[1]) {
            $controller_name = $param_value[0] . '_' . $param_value[1] . '_controller';
            $file = App_Path . 'controller/' . $controller_name . '.php';
            if (is_file($file)) {
                require_once $file;
                $controller = new $controller_name();
                $offset = 2;
            }
        }
        if ($controller == null) {
            $controller_name = $param_value[0] . '_controller';
            if (!$param_value[0]) {
                $controller_name = 'index_controller';
            }
            $file = App_Path . 'controller/' . $controller_name . '.php';
            if (is_file($file)) {
                require_once $file;
                $controller = new $controller_name();
                $offset = 1;
            } else {
                $controller_name = 'index_controller';
                $file = App_Path . 'controller/' . $controller_name . '.php';
                if (is_file($file)) {
                    require_once $file;
                    $controller = new $controller_name();
                    $offset = 0;
                }
                if (!$controller) {
                    throw new Exception('路由迷路了');
                }
            }
        }
        if (!$controller instanceof controller) {
            throw new Exception('路由走错路了');
        }
        $method = $param_value[$offset];
        if (!is_callable(array($controller, $method))) {
            $method = 'index';
            $offset = $offset - 1;
        }
        $args = array_slice($param_value, $offset + 1);
        if (count($args) % 2 == 0) {
            foreach ($args as $key=> $value) {
                $key % 2 == 0 ? $_REQUEST[$value] = $args[$key + 1] : null;
            }
        }
        $controller->$method();
    }

    /**
     * 抛出错误
     * @static
     * @param Exception $exc
     */
    public static function error(Exception $exc) {
        echo $exc->getMessage();
    }

    /**
     * 自动加载
     * @static
     * @param string $class
     * @throws Exception
     */
    public static function auto_load($class) {
        $file = __DIR__ . '/' . $class . '.php';
        if (!is_file($file)) {
            throw new Exception('无法找到类 "' . $class . '"');
        }
        require_once $file;
    }
}
