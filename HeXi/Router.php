<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:15
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 路由类的操作
 * 直接是自动路由，目前不支持手动路由
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Router {

    /**
     * 路由解析参数
     * @var array
     */
    private static $args = array();

    /**
     * 获取路由参数
     * @param bool|string|int $index
     * @return array|string
     */
    public static function param($index = true) {
        if ($index === true) {
            return self::$args;
        }
        if (is_numeric($index)) {
            return self::$args[$index - 1];
        }
        return self::$args[$index];
    }

    /**
     * 解析访问地址获得路由参数
     */
    private static function _parse() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = str_replace('.' . pathinfo($url, PATHINFO_EXTENSION), '', $url);
        self::$args = array_values(array_filter(explode('%2F', urlencode($url))));
    }

    /**
     * 获取控制器对象
     * @return bool|Controller_Base
     */
    private static function _controller() {
        $controller = false;
        $controllerName = null;
        if (self::$args[1]) {
            $controllerName = self::$args[0] . '_' . self::$args[1] . 'Controller';
            $controller = Controller::create($controllerName);
            self::$args['offset'] = 2;
        }
        if (!$controller && self::$args[0]) {
            $controllerName = self::$args[0] . '_' . Config::get('app.controller.default') . 'Controller';
            $controller = Controller::create($controllerName);
            self::$args['offset'] = 1;
        }
        if (!$controller) {
            $controllerName = self::$args[0] . 'Controller';
            self::$args['offset'] = 1;
            if (!self::$args[0]) {
                $controllerName = Config::get('app.controller.default') . 'Controller';
                self::$args['offset'] = 0;
            }
            $controller = Controller::create($controllerName);
        }
        if (!$controller) {
            $controllerName = Config::get('app.controller.default') . 'Controller';
            $controller = Controller::create($controllerName);
            self::$args['offset'] = 0;
        }
        if (!$controller) {
            Error::stop('无法找到可以执行的控制器');
        }
        self::$args['controller'] = $controllerName;
        return $controller;
    }

    /**
     * 调用控制器操作
     * @param Controller_Base $controllerObject
     * @return mixed
     */
    private static function _invoke(Controller_Base $controllerObject) {
        $methodName = self::$args[self::$args['offset']];
        unset(self::$args['offset']);
        if (!is_callable(array( $controllerObject, $methodName ))) {
            if (!Config::get('app.controller.map')) {
                Error::stop('无法执行指定控制器 "' . get_class($methodName) . '" 的方法 "' . $methodName . '"');
            }
            $methodName = Config::get('app.controller.action');
            if (!is_callable(array( $controllerObject, $methodName ))) {
                Error::stop('无法执行控制器的默认方法');
            }
        }
        self::$args['action'] = $methodName;
        call_user_func(array($controllerObject,'init'));
        return call_user_func(array( $controllerObject, $methodName ));
    }

    /**
     * 调用路由
     * @return mixed
     */
    public static function run() {
        self::_parse();
        $controllerObject = self::_controller();
        return self::_invoke($controllerObject);
    }

}
