<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:43
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 错误操作类
 * 调试操作类
 * 日志记录类
 * @package Core
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Error {

    public static function stop($message) {
        echo '<h2 style="color:red">'.$message.'</h2>';
        echo '<hr/>';
        $profile = Error::profile('app:stop');
        echo '<p>time: '.$profile['time'].' ms , memory: '.$profile['memory'].' KB</p>';
        exit;
    }

    public static function notice() {

    }

    public static function log() {

    }

    /**
     * 调试用记录数据
     * @var array
     */
    private static $profileData = array();

    /**
     * 开始记录调试数据
     * @param string $name
     */
    private static function startProfile($name) {
        $time = microtime(true);
        $memory = memory_get_usage();
        self::$profileData[$name] = array(
            'time'   => $time,
            'memory' => $memory
        );
    }

    /**
     * 结束调试执行
     * @param string $name
     * @return array
     */
    private static function stopProfile($name) {
        $execute = microtime(true) - self::$profileData[$name]['time'];
        $usage = memory_get_usage() - self::$profileData[$name]['memory'];
        return array(
            'time'  => number_format($execute * 1000, 2),
            'memory' => number_format($usage / 1024, 2)
        );
    }

    /**
     * 调用调试操作
     * @param string $cmd 带有start和stop作为开关
     * @return array|bool
     */
    public static function profile($cmd) {
        $cmd = explode(':', $cmd);
        if ($cmd[1] == 'start') {
            self::startProfile($cmd[0]);
        } elseif ($cmd[1] == 'stop') {
            return self::stopProfile($cmd[0]);
        }
        return true;
    }
}
