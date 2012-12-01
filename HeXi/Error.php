<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:32
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 错误处理类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Error {

    /**
     * 错误常量
     */
    const ERROR = 'ERROR';

    /**
     * 提示常量
     */
    const NOTICE = 'NOTICE';

    /**
     * 停止运行
     * @param string $message
     */
    public static function stop($message) {
        //----------------------------------
        self::log($message, Error::ERROR);
        //----------------------------------------------
        $trace = Config::get('app.error.trace');
        if ($trace) {
            $traceData = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $traceData = array_reverse($traceData);
            $trace = array();
            foreach ($traceData as $value) {
                if ($value['class']) {
                    $trace[] = $value['class'] . $value['type'] . $value['function'] . '() @ ' . $value['file'] . ':' . $value['line'];
                } else {
                    $trace[] = $value['function'] . '() @ ' . $value['file'] . ':' . $value['line'];
                }
            }
        } else {
            $trace = array();
        }
        //--------------
        $template = Config::get('app.error.template');
        $view = false;
        if ($template) {
            $view = View::create();
        }
        //-------------------
        $profile = Config::get('app.error.profile');
        $time = null;
        $memory = null;
        if ($profile) {
            $time = round((microtime(true) - $GLOBALS['profile']['time']) * 1000, 2);
            $memory = round((memory_get_usage() - $GLOBALS['profile']['memory']) / 1024, 2);
        }
        if ($view) {
            echo $view->data(array(
                'message' => $message,
                'trace'   => $trace,
                'time'    => $time,
                'memory'  => $memory,
                'sql'     => Database_Sql::all()
            ))->render($template);
        } else {
            echo $message;
        }
        exit;
    }

    /**
     * 提示性错误
     * @param string $message
     */
    public static function notice($message) {
        self::log($message);
    }

    /**
     * 记录错误信息
     * @param string $message
     * @param string $level
     */
    public static function log($message, $level = Error::NOTICE) {
        $dir = Config::get('app.error.log');
        if (!$dir) {
            return;
        }
        $file = Register::command($dir . '.' . date('Y-m-d')) . '.log';
        $string = '[' . $level . '] ' . $message . ' @ ' . Request::url() . PHP_EOL;
        file_put_contents($file, $string, FILE_APPEND);
    }


}
