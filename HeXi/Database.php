<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午10:36
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 连接到数据库
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Database {

    /**
     * 连接到某个数据库
     * @param string $name 连接名称
     * @return bool|Database_Base
     */
    public static function connect($name = 'default') {
        $driverName = Config::get('database.' . $name . '.driver');
        if (!$driverName) {
            Error::stop('无法获取数据库连接 "' . $name . '" 的驱动类型');
        }
        $driverName = strtoupper($driverName);
        if ($driverName == 'PDO') {
            return Register::create('Database_PDO', ':' . $name);
        }
        Error::stop('无法连接到数据库 "' . $name . '"');
        return false;
    }
}
