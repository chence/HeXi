<?php

/**
 * 连接类
 */
class HeXiConnection {


    /**
     * 数据库连接对象
     * @var array
     */
    protected static $instance = array();

    /**
     * 连接到数据库
     * @static
     * @param string $name 连接名称
     * @return HeXiDriver|HeXiPdoDriver
     */
    public static function connect($name = 'default') {
        if (!self::$instance[$name] instanceof HeXiDriver) {
            HeXiConfig::import('driver');
            $driver = $GLOBALS['config']['database_' . $name]['driver'];
            self::$instance[$name] = self::getDriver($driver, $name);
            $GLOBALS['driver'][] = $name;
            if (HEXI_DEBUG) {
                HeXiLogger::write('Connect to Database "' . $name . '" - ' . $driver, __METHOD__, __FILE__, __LINE__);
            }
        }
        return self::$instance[$name];
    }

    /**
     * 获取数据库连接对象
     * @static
     * @param string $driver 驱动名称
     * @param string $name 连接名称
     * @return HeXiPdoDriver
     */
    protected static function getDriver($driver, $name) {
        switch ($driver) {
            case 'pdo':
            default:
                require_once 'driver/HeXiPdoDriver.php';
                return new HeXiPdoDriver($name);
            /**
             * @todo 完善其他驱动类型
             */
        }
    }
}
