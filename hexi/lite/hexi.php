<?php
/**
 *
 * 基础类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
abstract class hexi {

    /**
     * 获取url参数
     * @param int $index
     * @return mixed
     */
    protected function _url_param($index) {
        if ($index === true) {
            return $GLOBALS['url']['param'];
        }
        return $GLOBALS['url']['param'][$index];
    }

    /**
     * 获取url后缀名
     * @return mixed
     */
    protected function _url_ext() {
        return $GLOBALS['url']['ext'];
    }

    /**
     * 获取配置
     * @param string $type
     * @param string $key
     * @return mixed
     */
    protected function _config($type, $key) {
        if($key === true){
            return $GLOBALS['config'][$type];
        }
        return $GLOBALS['config'][$type][$key];
    }

    /**
     * 引入文件
     * @param string $name
     */
    protected function _import($name) {
        if (strstr($name, 'app.')) {
            $name2 = str_replace('.', '/', ltrim($name, 'app.'));
            $file = App_Path . $name2 . '.php';
        } else {
            $name2 = str_replace('.', '/', ltrim($name, 'hexi.'));
            $file = HeXi_Path . $name2 . '.php';
        }
        if (is_file($file)) {
            require_once $file;
            return;
            //throw new Exception('无法import "' . $name . '"');
        }
        $this->_exception('无法import "' . $name . '"');
    }

    /**
     * 写出错误
     * @param string $message
     * @throws Exception
     */
    public function _exception($message) {
        throw new Exception($message);
    }

    /**
     * 获取调试信息
     * @param bool $only
     * @return float|object|string
     */
    protected function _debug($only = false) {
        if ($only == 'time') {
            return round((microtime(true) - $GLOBALS['debug']['time_begin']) * 1000, 1);
        }
        if ($only == 'memory') {
            $size = memory_get_usage() - $GLOBALS['debug']['mem_begin'];
            $units = explode(' ', 'B KB MB GB TB PB');
            for ($i = 0; $size > 1024; $i++) {
                $size /= 1024;
            }
            return round($size, 2) . ' ' . $units[$i];
        }
        $time = round((microtime(true) - $GLOBALS['debug']['time_begin']) * 1000, 1);
        $size = memory_get_usage() - $GLOBALS['debug']['mem_begin'];
        $units = explode(' ', 'B KB MB GB TB PB');
        for ($i = 0; $size > 1024; $i++) {
            $size /= 1024;
        }
        $size = round($size, 2) . ' ' . $units[$i];
        return (object)array(
            'time'  => $time,
            'memory'=> $size
        );
    }

    /**
     * 获取action数据
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function _action($name,$value=null){
        if($value === null){
            if($name === true){
                return $GLOBALS['action']['data'];
            }
            return $GLOBALS['action']['data'][$name];
        }
        $GLOBALS['action']['data'][$name] = $value;
        return $value;
    }

}
