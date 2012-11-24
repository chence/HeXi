<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:45
 * @link      : http://hexiaz.com
 *
 *
 */

/**
 * 控制器调用类
 * 负责控制器的生成和调用
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Controller {

    /**
     * 生成控制器对象
     * @param string $cmd
     * @return bool|HeXiBaseController|HeXiActionController
     */
    public static function create($cmd) {
        $controllerName = $cmd;
        if (strstr($cmd, '.')) {
            $controllerName = explode('.', $cmd);
            $controllerName = $controllerName[0] . ucwords($controllerName[1]);
        }
        $cmd = Config::get('app.controller.cmd') . '.' . $cmd;
        $controller = Register::create($controllerName, $cmd, false);
        if (!$controller) {
            return false;
        }
        if ((!$controller instanceof HeXiBaseController) && (!$controller instanceof HeXiActionController)) {
            Error::stop('无法执行无效的控制器 "' . get_class($controller) . '"');
        }
        return $controller;
    }

    /**
     * 执行控制器
     * @param string|HeXiActionController|HeXiBaseController $controllerObject 控制器对象或引入命令
     * @param string                                         $method
     * @return mixed
     */
    public static function invoke($controllerObject, $method) {
        if (is_string($controllerObject)) {
            $controllerObject = self::create($controllerObject);
        }
        if (!is_callable(array( $controllerObject, $method ))) {
            Error::stop('无法调用控制器 "' . get_class($controllerObject) . '" 中的方法 "' . $method . '"');
        }
        Event::trigger('appControllerInvoke:'.get_class($controllerObject).'->'.$method);
        return call_user_func(array( $controllerObject, $method ));
    }
}
