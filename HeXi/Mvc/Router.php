<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:44
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 路由类
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Router {

    /**
     * 路由参数
     * @var array
     */
    private static $param;

    /**
     * 获取某一个路由参数
     * @param bool|int|string $index
     * @return array|string
     */
    public static function param($index = true) {
        if ($index === true) {
            return self::$param;
        }
        if (is_numeric($index)) {
            return self::$param[(int)$index - 1];
        }
        return self::$param[$index];
    }

    /**
     * 解析URL地址
     * @return array
     */
    private static function parseUrl() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $url = urlencode(str_replace('.' . pathinfo($url, PATHINFO_EXTENSION), '', $url));
        $param = explode('%2F', $url);
        return array_values(array_filter($param));
    }

    /**
     * 获取控制器对象
     * @return bool|HeXiActionController|HeXiBaseController
     */
    private static function getController() {
        $controllerDefault = Config::get('app.controller.default');
        $controllerObject = false;
        $param = self::$param;
        if ($param[1]) {
            $cmd = $param[0] . '.' . $param[1] . 'Controller';
            $controllerObject = Controller::create($cmd);
            self::$param['offset'] = 2;
        }
        if (!$controllerObject) {
            $cmd = $param[0] . '.' . $controllerDefault . 'Controller';
            $controllerObject = Controller::create($cmd);
            self::$param['offset'] = 1;
        }
        if (!$controllerObject) {
            $cmd = $param[0] . 'Controller';
            self::$param['offset'] = 1;
            if (!$param[0]) {
                $cmd = $controllerDefault . 'Controller';
                self::$param['offset'] = 0;
            }
            $controllerObject = Controller::create($cmd);
        }
        if (!$controllerObject) {
            $cmd = $controllerDefault . 'Controller';
            $controllerObject = Controller::create($cmd);
            self::$param['offset'] = 0;
        }
        return $controllerObject;
    }

    /**
     * 调用控制器对象
     * @param string|HeXiActionController|HeXiBaseController $controllerObject
     * @param string $method
     * @return mixed
     */
    private static function invokeController($controllerObject, $method) {
        if (!is_callable(array( $controllerObject, $method ))) {
            if (!Config::get('app.controller.capture')) {
                Error::stop('无法调用控制器 "' . get_class($controllerObject) . '" 中的方法 "' . $method . '"');
            }
            $method = Config::get('app.controller.method') . 'Action';
        }
        self::$param['method'] = $method;
        Event::trigger('appRouter');
        return Controller::invoke($controllerObject, $method);
    }

    /**
     * 运行路由
     * @return mixed
     */
    public static function run() {
        self::$param = self::parseUrl();
        $controller = self::getController();
        if ($controller === false) {
            Error::stop('无法找到可以执行的控制器');
        }
        self::$param['controller'] = get_class($controller);
        $methodName = self::$param[self::$param['offset']] . 'Action';
        unset(self::$param['offset']);
        return self::invokeController($controller, $methodName);
    }
}
