<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-24 - 下午3:23
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 组件类调用类
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Component {

    /**
     * 生成组件类
     * @param string $cmd
     * @return HeXiBaseComponent
     */
    public static function create($cmd) {
        $comName = $cmd;
        $cmd = Config::get('app.component.cmd') . '.' . rtrim($cmd, 'Component') . '.' . $cmd;
        return Register::create($comName, $cmd, false);
    }

    /**
     * 调用组件的操作
     * @param string|HeXiBaseComponent $comCmd
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public static function invoke($comCmd, $methodName, $args = array()) {
        if (is_string($comCmd)) {
            $comObject = self::create($comCmd);
        } else {
            $comObject = $comCmd;
        }
        #验证类型是否正确
        if (!$comObject instanceof HeXiBaseComponent) {
            Error::stop('无法调用组件对象 "' . $comCmd . '"');
        }
        #验证是否可以调用
        if (!is_callable(array( $comObject, $methodName ))) {
            Error::stop('无法执行组件 "' . get_class($comObject) . '" 的方法 "' . $methodName . '"');
        }
        Event::trigger('appComponentInvoke:' . get_class($comObject) . '->' . $methodName);
        #执行模型类调用
        return call_user_func_array(array( $comObject, $methodName ), $args);
    }
}
