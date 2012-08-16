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
        if (!is_dir(App_Path)) {
            self::init();
            exit;
        }
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
        $string = file_get_contents(HeXi_Path . 'www/error.html');
        echo view::render_string($string, array(
            'message'=> $exc->getMessage()));
        //echo self::error_string($exc);
        exit;
    }

    /**
     * 输出异常为HTML
     * @static
     * @param Exception $exc
     * @return string
     */
    private static function error_string(Exception $exc) {
        $string = '<meta charset="utf-8"/>' . PHP_EOL;
        $string .= '<title>系统出现错误</title>' . PHP_EOL;
        $string .= '<style type="text/css">*{margin:0;padding:0;font-size: 13px;line-height: 28px;font-family:"Consolas","Microsoft Yahei";font-weight:normal}</style>' . PHP_EOL;
        $string .= '<div id="error" style="padding:.5em 1.5em 1.5em 1.5em">' . PHP_EOL;
        $string .= '<h1 style="font-size: 28px;line-height: 2.5em;color: #D42529">系统出现错误</h1>' . PHP_EOL;
        $string .= '<hr style="border:none;border-bottom:1px solid #BBB"/>' . PHP_EOL;
        $string .= '<p style="font-size:14px;line-height:2.5em;margin-top:1.5em">[错误位置] ' . $exc->getFile() . ' <strong style="color:#D42529;font-weight:800">(LINE:' . $exc->getLine() . ')</strong></p>';
        $string .= '<p style="font-size:14px;line-height:2.5em;margin-bottom:1.5em">[错误信息] ' . $exc->getMessage() . ' <strong style="color:#D42529;font-weight:800">(' . get_class($exc) . ')</strong></p>';
        $string .= '<hr style="border:none;border-bottom:1px solid #BBB"/>' . PHP_EOL;
        $string .= '<h4 style="line-height: 2.5em;color: #1A72B6;margin-top: 1.5em">[错误追踪]</h4>' . PHP_EOL;
        #输出跟踪信息
        $trace = array_reverse($exc->getTrace());
        foreach ($trace as $index=> $error) {
            $string .= '<p>#' . ($index + 1) . ' ' . $error['file'] . ' <strong style="font-weight:800">' . $error['line'] . '</strong> ' . $error['class'] . $error['type'] . $error['function'] . '()</p>' . PHP_EOL;
        }
        $string .= '<hr style="border:none;border-bottom:1px solid #BBB;margin-top:1.5em"/>' . PHP_EOL;
        #输出运行信息
        $string .= '<p style="font-size:12px;line-height:2.5em;margin-top:1.5em">[执行时间] ' . round((microtime(true) - $GLOBALS['debug']['time_begin']) * 1000, 1) . ' ms</p>';
        $size = memory_get_usage() - $GLOBALS['debug']['mem_begin'];
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > 1024; $i++) {
            $size /= 1024;
        }
        $string .= '<p style="font-size:12px;line-height:2.5em;margin-bottom:1.5em">[占用内存] ' . round($size, 2) . ' ' . $units[$i] . '</p>';
        $string .= '</div>';
        return $string;
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


    /**
     * 初始化系统
     * @static
     *
     */
    public static function init() {
        #创建页面的内容
        if ($_GET['app'] == 'init') {
            mkdir(App_Path);
            mkdir(App_Path . 'controller/');
            mkdir(App_Path . 'model/');
            mkdir(App_Path . 'compile/');
            mkdir(App_Path . 'view/');
            copy(HeXi_Path . 'config.ini', App_Path . 'config.ini');
            $controller = "<?php " . PHP_EOL;
            $controller .= 'class index_controller extends controller {' . PHP_EOL;
            $controller .= '}' . PHP_EOL;
            file_put_contents(App_Path . 'controller/index_controller.php', $controller);
            header('Location:/');
            exit;
        }
        #判断要不要创建应用
        $string = file_get_contents(HeXi_Path . 'www/error.html');
        echo view::render_string($string, array(
            'message'=> '您的应用是空的<br/>需要创建一个默认应用吗？',
            'ok'     => true,
            'ok_to'  => '/?app=init'));
    }

}
