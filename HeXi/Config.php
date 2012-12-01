<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:01
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 配置操作类
 * 1.引入配置信息
 * 2.查询和设置配置信息
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 * @version 0.5
 *
 */

class Config {

    /**
     * 配置数据
     * @var array
     */
    private static $data = array();

    /**
     * 加载配置信息
     * 会覆盖原有的数据
     * @param array $data
     */
    public static function import(array $data) {
        self::$data = array_merge(self::$data, $data);
    }

    /**
     * 获取所有配置数据
     * @return array
     */
    public static function all() {
        return self::$data;
    }

    /**
     * 获取某一条配置数据
     * @param bool|string $command 数据索引如app.name；True时获取所有数据
     * @return array|mixed
     */
    public static function get($command = true) {
        if ($command === true) {
            return self::all();
        }
        return eval('return self::$data["' . str_replace('.', '"]["', $command) . '"];');
    }

    /**
     * 设置配置数据
     * 会覆盖原有的
     * @param string $command
     * @param mixed $value
     */
    public static function set($command, $value) {
        eval('self::$data["' . str_replace('.', '"]["', $command) . '"] = $value;');
    }
}

//-----------------------alias-------------------

/**
 * 简化方法
 * 获取或设置配置
 * @param string $command
 * @param null|mixed $value
 * @return array|mixed|null
 */
function config($command,$value=null){
    if($value === null){
        return Config::get($command);
    }
    Config::set($command,$value);
    return $value;
}
