<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:00
 * @link      : http://hexiaz.com
 *
 *
 */
require_once 'Error.php';

require_once 'Register.php';
/**
 * 核心类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class HeXi {

    /**
     * 应用执行的结果
     * @var string|Response
     */
    private static $appData = null;

    /**
     * 执行前的预备信息
     */
    private static function prepare() {
        spl_autoload_register(function ($className) {
            $command = 'HeXi.' . str_replace('_', '.', $className);
            Register::import($command);
        });
        Config::import(require(constant(AppName . 'Dir') . 'config.php'));
        //set_error_handler();
        //set_exception_handler();
        register_shutdown_function(function () {
            $error = error_get_last();
            $invalidCode = array( E_WARNING, E_NOTICE, E_STRICT, E_DEPRECATED );
            if (!in_array($error['type'], $invalidCode)) {
                ob_end_clean();
                Error::stop($error['message']);
            }
        });
    }

    /**
     * 运行应用
     */
    public static function run() {
        self::prepare();
        self::$appData = Router::run();
        self::send();
    }

    /**
     * 发出结果
     */
    private static function send() {
       if(self::$appData instanceof Response){
          self::$appData->send();
           return;
       }
       Response::create(self::$appData)->send();
    }
}
