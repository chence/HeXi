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
 * 控制器类
 * 控制器的创建和调用
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Controller {

    /**
     * 创建控制器对象
     * @param string $controllerName 控制器名称
     * @return bool|Controller_Base
     */
    public static function create($controllerName) {
        $command = Config::get('app.controller.command') . '.' . str_replace('_', '.', $controllerName);
        return Register::create($controllerName, $command);
    }

    /**
     * 调用控制器方法
     * @param string $controllerName
     * @param string $methodName
     * @return mixed
     */
    public static function invoke($controllerName, $methodName) {
        $controller = self::create($controllerName);
        if (!$controller) {
            Error::stop('无法调用控制器 "' . $controllerName . '"');
        }
        if (!is_callable(array( $controller, $methodName ))) {
            Error::stop('无法调用控制器 "' . $controllerName . '" 的方法 "' . $methodName . '"');
        }
        return call_user_func(array( $controller, $methodName ));
    }

}


/**
 * 控制器基类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */
abstract class Controller_Base {

    /**
     * 初始化方法
     */
    public function __construct(){
        $this->template = View::create();
        $this->response = Response::create();
    }

    /**
     * 控制器默认的初始化方法
     */
    abstract public function init();

    /**
     * 视图对象
     * @var View
     */
    protected $template;

    /**
     * 返回对象
     * @var Response
     */
    protected $response;

    /**
     * 空方法，默认提示错误信息，需要覆写
     */
    public function index() {
        Error::stop('调用控制器的默认方法，需要重写');
    }

}


