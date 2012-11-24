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
 * 模型类的调用
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Model {

    /**
     * 创建模型类对象
     * @param string $cmd 模型类名称
     * @return bool|HeXi
     */
    public static function create($cmd) {
        $modelName = $cmd;
        $cmd = Config::get('app.model.cmd') . '.' . $cmd;
        return Register::create($modelName, $cmd, false);
    }

    /**
     * 调用模型的执行方法
     * @param string|object $modelCmd 可以命令，也可以直接是对象
     * @param string        $methodName
     * @param array         $args
     * @return mixed
     */
    public static function invoke($modelCmd, $methodName, $args = array()) {
        if (is_string($modelCmd)) {
            $modelObject = self::create($modelCmd);
        } else {
            $modelObject = $modelCmd;
        }
        #验证类型是否正确
        if (!$modelObject instanceof HeXiBaseModel) {
            Error::stop('无法调用模型对象 "' . $modelCmd . '"');
        }
        #验证是否可以调用
        if (!is_callable(array( $modelObject, $methodName ))) {
            Error::stop('无法执行模型 "' . get_class($modelObject) . '" 的方法 "' . $methodName . '"');
        }
        Event::trigger('appModelInvoke:' . get_class($modelObject) . '->' . $methodName);
        #执行模型类调用
        return call_user_func_array(array( $modelObject, $methodName ), $args);
    }
}
