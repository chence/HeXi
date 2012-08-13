<?php

/**
 * 操作模型类
 */
class HeXiActionModel extends HeXiModel {


    /**
     * 注入Action
     * @param string $name
     * @param string $method
     * @param array $args
     * @return HeXiActionModel
     */
    protected function _inject($name, $method, $args = array()) {
        $GLOBALS['action']['inject'][$name]['method'] = $method;
        $GLOBALS['action']['inject'][$name]['args'] = $args;
        return $this;
    }

    /**
     * 移除Action
     * @param string $name
     * @return HeXiActionModel
     */
    protected function _remove($name) {
        $GLOBALS['action']['remove'][] = $name;
        return $this;
    }

    /**
     * 结束Action
     * @return HeXiActionModel
     */
    protected function _end() {
        $GLOBALS['action']['status'] = false;
        return $this;
    }

    /**
     * 最终调用Action
     * @param string $method
     * @param array $args
     * @return HeXiActionModel
     */
    protected function _final($method, $args = array()) {
        $GLOBALS['action']['final']['method'] = $method;
        $GLOBALS['action']['final']['args'] = $args;
        return $this;
    }

}
