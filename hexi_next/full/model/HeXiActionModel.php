<?php

/**
 * 操作模型类
 */
class HeXiActionModel extends HeXiModel {


    /**
     * 注入Action
     * @param string $name
     * @param string $method
     * @return HeXiActionModel
     */
    protected function _inject($name, $method) {
        $GLOBALS['action']['inject'][$name]['method'] = $method;
        $args = func_get_args();
        $args = array_slice($args, 2);
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
     * 最终操作，当Action标记为停止后处理
     * @param string $method
     * @return HeXiActionModel
     */
    protected function _final($method) {
        $GLOBALS['action']['final']['method'] = $method;
        $args = func_get_args();
        $args = array_slice($args, 2);
        $GLOBALS['action']['final']['args'] = $args;
        return $this;
    }

}
