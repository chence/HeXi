<?php
/**
 * 路由类
 */
class HeXiRouter {


    /**
     * 方法名称在URL参数中的索引值
     * @var int
     */
    private static $methodOffset = 0;

    /**
     * 分发路由
     * @static
     * @param string $url
     * @return mixed
     */
    public static function dispatch($url) {
        #解析URL参数
        self::parseUrl($url, true);
        #获取控制器
        $controller = self::getController();
        #获取方法名称
        $methodName = $GLOBALS['url']['param'][self::$methodOffset];
        if (!$methodName) {
            $methodName = HeXiConfig::get('controller', 'method');
        }
        #添加调试记录
        if (HEXI_DEBUG) {
            HeXiLogger::write('Dispatch to ' . get_class($controller) . '->' . $methodName . '()', __METHOD__, __FILE__, __LINE__);
        }
        #执行控制器和方法
        return self::executeMethod($controller, $methodName);
    }

    /**
     * 调用控制器方法
     * @static
     * @param string $controllerName 控制器名称
     * @param string $method 控制器方法
     * @param string $dir 控制器的子文件夹名称
     * @return bool|mixed
     */
    public static function call($controllerName, $method, $dir = '') {
        if (!self::isControllerExist($controllerName, $dir)) {
            return false;
        }
        $controller = self::createController($controllerName . 'Controller');
        if (!is_callable(array($controller, $method))) {
            return false;
        }
        #此时的调用不会触发前置和后置的操作
        return call_user_func(array($controller, $method));
    }

    /**
     * 解析URL
     * @static
     * @param string $url
     * @param bool $toGlobal
     * @return array
     * @throws HeXiRouterException
     */
    private static function parseUrl($url, $toGlobal = false) {
        #获取URL的PATH部分
        $url = parse_url($url, PHP_URL_PATH);
        #分割URL找出后缀名
        $urlArray = explode('.', $url);
        #很多.的请求不予支持
        if (count($urlArray) > 2) {
            throw new HeXiRouterException('url parses unsuccessfully ' . $url);
        }
        $ext = $urlArray[1];
        #分割无后缀名的部分
        $urlArray = explode('/', $urlArray[0]);
        #过滤空值
        $urlArrayValue = array_filter($urlArray);
        #如果过滤后空值项目大于2，说明有错误，不支持解析
        #所谓大于2，如/index/,分割后是[],index,[]空值两个
        #错误的请求如/index//，分割后是[],index,[],[]，空值三个，人为错误
        if (count($urlArray) - count($urlArrayValue) > 2) {
            throw new HeXiRouterException('url parses unsuccessfully  ' . $url);
        }
        #重新编码数字索引，保证是0开头
        $urlArrayValue = array_values($urlArrayValue);
        if ($toGlobal) {
            #保存后缀名
            $GLOBALS['url']['ext'] = !$ext ? '' : $ext;
            $GLOBALS['url']['param'] = $urlArrayValue;
        }
        return $urlArrayValue;
    }

    /**
     * 判断控制器是否存在
     * @static
     * @param string $controllerName
     * @param string $dir
     * @return bool|mixed
     */
    private static function isControllerExist($controllerName, $dir = '') {
        $dir = $dir != '' ? $dir . '/' : $dir;
        $controllerFile = HeXiWebApp::getPath() . HeXiConfig::get('controller', 'path') . $dir . $controllerName . 'Controller.php';
        #存在就引入控制器文件
        return is_file($controllerFile) ? require_once $controllerFile : false;
    }

    /**
     * 获取控制器实例
     * @static
     * @return HeXiController
     * @throws HeXiRouterException
     */
    private static function getController() {
        #获取顶级控制器
        $controllerName = $GLOBALS['url']['param'][0];
        self::$methodOffset = 1;
        if (!$controllerName) {
            $controllerName = HeXiConfig::get('controller', 'default');
            self::$methodOffset = 0;
        }
        if (self::isControllerExist($controllerName)) {
            return self::createController($controllerName . 'Controller');
        }
        #获取多级控制器
        $controllerName = $GLOBALS['url']['param'][0] . ucwords($GLOBALS['url']['param'][1]);
        self::$methodOffset = 2;
        if (self::isControllerExist($controllerName, $GLOBALS['url']['param'][0])) {
            return self::createController($controllerName . 'Controller');
        }
        #获取多级控制器的默认控制器
        $controllerName = $GLOBALS['url']['param'][0] . ucwords(HeXiConfig::get('controller', 'default'));
        self::$methodOffset = 1;
        if (self::isControllerExist($controllerName, $GLOBALS['url']['param'][0])) {
            return self::createController($controllerName . 'Controller');
        }
        #获取默认控制器
        $controllerName = HeXiConfig::get('controller', 'default');
        self::$methodOffset = 0;
        if (!self::isControllerExist($controllerName)) {
            throw new HeXiRouterException('Fail to dispatch url');
        }
        return self::createController($controllerName . 'Controller');
    }

    /**
     * 生成控制器实例
     * @static
     * @param string $controllerName
     * @return HeXiController
     * @throws HeXiControllerException
     */
    private static function createController($controllerName) {
        $controller = new $controllerName();
        #判断是否基于基类
        if (!$controller instanceof HeXiController) {
            throw new HeXiControllerException($controllerName . ' is invalid Controller');
        }
        $GLOBALS['url']['controller'] = $controllerName;
        return $controller;
    }

    /**
     * 执行控制器方法
     * @static
     * @param HeXiController $controller
     * @param string $methodName
     * @return mixed
     */
    private static function executeMethod($controller, $methodName) {
        #判断是否是可调用的方法
        if (!is_callable(array($controller, $methodName))) {
            $methodName = HeXiConfig::get('controller', 'method');
        }
        $GLOBALS['url']['method'] = $methodName;
        return $controller->run($methodName);
    }
}

/**
 * 控制器异常类
 */
class HeXiRouterException extends HeXiException {

}
