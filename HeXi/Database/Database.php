<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:47
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 数据库调用类
 * @package Database
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Database {

    /**
     * 保存数据库驱动类的名称和引入命名
     * @var array
     */
    private static $driverCmd = array(
        'pdo' => array( 'name' => 'PDODriver', 'cmd' => 'HeXi.Database.Driver.PDODriver' )
    );

    /**
     * 获取数据库驱动的对象
     * @param string $name
     * @param string $driver
     * @return HeXiDriver
     */
    private static function getDriver($name, $driver) {
        return Register::create(self::$driverCmd[$driver]['name'], self::$driverCmd[$driver]['cmd'] . ':' . $name);
    }

    /**
     * 获取一个有标识的数据库驱动类
     * @param string $name
     * @return HeXiDriver
     */
    public static function connect($name = 'default'){
        #获取数据库连接类型
        $driver = Config::get('database.' . $name . '.driver');
        if (!$driver) {
            Error::stop('数据库配置"database.' . $name . '"无效');
        }
        return self::getDriver($name, $driver);
    }
}
