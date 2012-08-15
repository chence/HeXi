<?php

/**
 * 基础类
 */
abstract class HeXiBase {

    /**
     * 获取URL参数
     * @param bool|int $index
     * @return mixed
     */
    protected function _param($index = true) {
        return $index === true ? $GLOBALS['url']['param'] : $GLOBALS['url']['param'][$index];
    }

    /**
     * 获取后缀名
     * @return mixed
     */
    protected function _ext() {
        return $GLOBALS['url']['ext'];
    }

    /**
     * 获取控制器名称
     * @return mixed
     */
    protected function _controller() {
        return $GLOBALS['url']['controller'];
    }

    /**
     * 获取方法名称
     * @return mixed
     */
    protected function _method() {
        return $GLOBALS['url']['method'];
    }

    protected function _data($name, $value = null) {
        if ($value === null) {
            if($name === true){
                return $GLOBALS['action']['data'];
            }
            return $GLOBALS['action']['data'][$name];
        }
        $GLOBALS['action']['data'][$name] = $value;
        return $this;
    }
}
