<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:40
 * @link      : http://hexiaz.com
 *
 *
 */
require 'Core/Error.php';

Error::profile('app:start');

require 'Core/Config.php';

require 'Core/Register.php';

/**
 *
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class HeXi {

    /**
     * 返回的结果内容
     * @var string|null|Response|View
     */
    private static $appResult = null;

    /**
     * 预备操作
     */
    private static function prepare() {
        Config::import('config');
        spl_autoload_register(function ($className) {
            $cmd = Config::get('app.auto.' . $className);
            if (!$cmd) {
                Error::stop('无法自动加载库类 "' . $className . '"');
            }
            Register::import($cmd);
        });
        Event::trigger('appPrepare');
    }

    /**
     * 运行实体
     */
    public static function run() {
        self::prepare();
        Event::trigger('appRunStart');
        self::$appResult = Router::run();
        Event::trigger('appRunEnd');
        self::result();
    }

    /**
     * 处理结果
     */
    private static function result() {
        Event::trigger('appFinish');
        if (is_string(self::$appResult)) {
            Response::create()->body(self::$appResult)->send();
            return;
        }
        if (self::$appResult instanceof Response) {
            self::$appResult->send();
            return;
        }
        if (self::$appResult instanceof View) {
            Response::create()->body(self::$appResult->fetch())->send();
            return;
        }
        //Response::create()->contentType('application/json')
            //->body(json_encode(self::$appResult))->send();
    }
}
