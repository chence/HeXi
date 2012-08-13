<?php


/**
 * Web应用类
 */
class HeXiWebApp {

    /**
     * 获取路径
     * @static
     * @return string
     */
    public static function getPath() {
        return ROOT_PATH . '/' . $GLOBALS['app']['path'];
    }

    /**
     * 获取名称
     * @static
     * @return mixed
     */
    public static function getName() {
        return $GLOBALS['app']['name'];
    }

    //---------------

    /**
     * 运行应用
     */
    public function run() {
        #载入默认配置
        HeXiConfig::import('hexi');
        #启动调试
        if (HEXI_DEBUG) {
            HeXiLogger::init();
        }
        #分发路由
        HeXiRouter::dispatch($_SERVER['REQUEST_URI'], true);
        #输出调试
        if (HEXI_DEBUG) {
            HeXiLogger::flush();
        }
    }


}

/**
 * Web应用异常类
 */
class HeXiWebAppException extends HeXiException {

}
