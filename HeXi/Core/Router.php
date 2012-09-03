<?php

/**
 * 路由处理类
 * @author FuXiaoHei
 */
class Router {

    /**
     * 路由对象
     * @var Router
     */
    private static $obj;

    /**
     * 返回单例对象
     * @return Router
     */
    public static function init() {
        return !self::$obj ? self::$obj = new Router() : self::$obj;
    }

    /**
     * 私有初始化方法
     */
    private function __construct() {
        $this->param = array();
    }

    /**
     * 解析后的路由参数
     * @var array
     */
    public $param;

    /**
     * 运行路由，分析url
     * @param string $url
     * @param string|null $withFilter 是否开启过滤，支持before，after和all
     */
    public function run($url, $withFilter = null) {
        #按"."分割，获取后缀名
        $arr = explode('.', $url);
        if (count($arr) > 2) {
            HeXi::error('无法解析很多"."的URL');
        }
        $param['ext'] = isset($arr[1]) ? $arr[1] : '';
        $arr = explode('/', $arr[0]);
        $arr2 = array_filter($arr);
        if (count($arr) - count($arr2) > 2) {
            HeXi::error('无法解析很多"\"的URL');
        }
        $param += array_values($arr2);
        $this->param = $param;
        //---------------------------
        #如果有多级的url解析param，按照多级优先寻找控制器
        if ($param[1]) {
            $controllerName = $param[0] . ucwords($param[1]) . 'Controller';
            $controllerFile = CONTROLLER_PATH . $controllerName . '.php';
            #首先寻找和判断文件
            if (is_file($controllerFile)) {
                require_once $controllerFile;
                #然后是寻找控制器类
                if (!class_exists($controllerName, false)) {
                    HeXi::error('控制器文件中没有定义控制器类 "' . $controllerName . '"');
                }
                #再后是生成控制器实例
                $controller = new $controllerName();
                #最后是执行控制器
                $this->executeController($controller, $param[2], $withFilter);
                return;
            }
        }
        //----------------------------
        #寻找单级控制器
        $controllerName = $param[0] . 'Controller';
        $controllerFile = CONTROLLER_PATH . $controllerName . '.php';
        if (is_file($controllerFile)) {
            require_once $controllerFile;
            if (!class_exists($controllerName, false)) {
                HeXi::error('控制器文件中没有定义控制器类 "' . $controllerName . '"');
            }
            $controller = new $controllerName();
            $this->executeController($controller, $param[1], $withFilter);
            return;
        }
        //-----------------------------
        #寻找默认控制器
        $controllerName = CONTROLLER_DEFAULT . 'Controller';
        $controllerFile = CONTROLLER_PATH . $controllerName . '.php';
        if (is_file($controllerFile)) {
            require_once $controllerFile;
            if (!class_exists($controllerName, false)) {
                HeXi::error('控制器文件中没有定义控制器类 "' . $controllerName . '"');
            }
            $controller = new $controllerName();
            $this->executeController($controller, $param[0], $withFilter);
            return;
        }
        //------------------------------
        HeXi::error('无法找到需要的控制器类');
    }

    /**
     * 执行控制器
     * @param Controller $controller
     * @param string $method
     * @param string $filter 过滤开启
     */
    private function executeController(Controller $controller, $method, $filter) {
        #确定执行方法，空方法就使用默认方法
        $method = !$method ? CONTROLLER_METHOD_DEFAULT : $method;
        if (!is_callable(array($controller, $method))) {
            if (CONTROLLER_ROUTE_DIRECT) {
                HeXi::error('无法在控制器 "' . get_class($controller) . '" 中调用方法 "' . $method . '"');
            } else {
                $method = CONTROLLER_METHOD_DEFAULT;
            }
        }
        #先执行before的方法
        if ($filter == 'before' || $filter == 'all') {
            $controller->doFilter('before_' . $method);
        }
        #再执行制定的方法
        $controller->{$method}();
        #最后是after方法
        if ($filter == 'after' || $filter == 'all') {
            $controller->doFilter('after_' . $method);
        }
    }

    /**
     * 静态方法，任意分析和执行url
     * @static
     * @param string $url
     * @param null|string $filter
     * @return Router
     */
    public static function execute($url, $filter = null) {
        $router = new Router();
        $router->run($url, $filter);
        return $router;
    }
}


