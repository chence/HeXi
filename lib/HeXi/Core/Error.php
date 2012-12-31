<?php


/**
 *  错误类
 *
 *  处理错误，处理调试跟踪
 */
class Error {

    /**
     * 停止App
     * @param string $message
     * @param int $status
     */
    public static function stop($message, $status) {
        #清理掉之前的输出
        ob_end_clean();
        $response         = Response::instance();
        $response->status = $status;
        $response->send();
        $display = HeXi::$config['app']['error'];
        #根据配置显示信息
        if ($display === 2) {
            $status = self::status();
            extract($status);
            include __DIR__ . '/../error.php';
            exit;
        }
        if($display === 1){
            echo $message;
            exit;
        }
        if($display === 0){
            exit;
        }
    }

    /**
     * 获取记录
     * @return array
     */
    public static function status() {
        $time   = round((microtime(true) - $GLOBALS['start']['time']) * 1000, 2);
        $memory = round((memory_get_usage() - $GLOBALS['start']['memory']) / 1024, 2);
        $sql    = Db_SQL::all();
        $trace  = self::trace();
        return compact('time', 'memory', 'sql', 'trace');
    }

    /**
     * 获取跟踪
     * @return array
     */
    private static function trace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $trace = array_slice($trace, 2);
        return array_reverse($trace);
    }
}