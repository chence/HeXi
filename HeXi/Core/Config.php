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
/**
 * 配置类
 * 操作配置信息
 * @package Core
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Config {

    /**
     * 配置数据
     * @var array
     */
    private static $configData = array();

    /**
     * 载入配置文件
     * 载入配置数据
     * @param string|array $name
     * @return bool
     */
    public static function import($name) {
        #如果是配置数据，直接合并
        if (is_array($name)) {
            self::$configData = array_merge(self::$configData, $name);
            return true;
        }
        #如果是配置文件，加载文件并合并数据
        $configFile = constant(AppName . 'Dir') . $name . '.php';
        if (!is_file($configFile)) {
            Error::stop('无法加载配置文件 "' . $configFile . '"');
        }
        $data = require($configFile);
        self::$configData = array_merge(self::$configData, $data);
        return true;
    }

    /**
     * 获取配置数据
     * @param string $cmd
     * @return mixed
     */
    public static function get($cmd) {
        $string = 'return self::$configData["' . str_replace('.', '"]["', $cmd) . '"];';
        return eval($string);
    }

    /**
     * 添加或修改配置数据
     * @param string $cmd
     * @param mixed $value
     */
    public static function set($cmd, $value) {
        $string = 'self::$configData["' . str_replace('.', '"]["', $cmd) . '"] = $value;';
        eval($string);
    }

    /**
     * 返回所有配置信息
     * @return array
     */
    public static function all() {
        return self::$configData;
    }
}
