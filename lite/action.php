<?php
/**
 * action操作类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
abstract class action extends hexi {

    /**
     * 初始化
     */
    public function __construct() {
        $GLOBALS['action']['status'] = true;
        $GLOBALS['action']['remove'] = array();
        $GLOBALS['action']['list'] = array();
    }

    /**
     * 添加action
     * @param string $name
     * @param string $action
     * @return action
     */
    protected function _call($name, $action) {
        $GLOBALS['action']['list'][$name]['action'] = $action;
        $GLOBALS['action']['list'][$name]['args'] = array();
        $args = func_get_args();
        if (count($args > 2)) {
            $args = array_slice($args, 2);
            $GLOBALS['action']['list'][$name]['args'] = $args;
        }
        return $this;
    }

    /**
     * 执行action
     * @return mixed
     */
    protected function _invoke() {
        #循环action
        foreach ($GLOBALS['action']['list'] as $name=> $action) {
            #如果是end了，执行最后的操作
            if (!$GLOBALS['action']['status']) {
                $this->_do('final', $GLOBALS['action']['final']['action'], $GLOBALS['action']['final']['args']);
                unset($GLOBALS['action']['inject']);
                $GLOBALS['action']['list'] = array();
                $GLOBALS['action']['remove'] = array();
                return $GLOBALS['action']['data'];
            }
            #如果remove了，清除action
            if (in_array($name, $GLOBALS['action']['remove'])) {
                unset($GLOBALS['action']['list'][$name]);
                unset($GLOBALS['action']['inject'][$name]);
                continue;
            }
            #如果inject了，替换方法和参数
            if (isset($GLOBALS['action']['inject'][$name])) {
                $action['action'] = $GLOBALS['action']['inject'][$name]['action'];
                $action['args'] = $GLOBALS['action']['inject'][$name]['args'];
            }
            #执行操作
            $this->_do($name, $action['action'], $action['args']);
            #如果check，回调验证函数
            if (isset($GLOBALS['action']['check'][$name])) {
                #验证函数现在控制器里
                $res = call_user_func(array($this, $GLOBALS['action']['check']['callback']));
                #验证失败，执行替换函数
                if ($res === false) {
                    #有替换就执行
                    if ($GLOBALS['action']['check']['new'] != false) {
                        $this->_do($name, $GLOBALS['action']['check']['new'], $GLOBALS['action']['check']['args']);
                    }
                }
            }
            #执行完了，移除对应内容
            unset($GLOBALS['action']['list'][$name]);
            unset($GLOBALS['action']['inject'][$name]);
        }
        return $GLOBALS['action']['data'];
    }

    /**
     * 直接执行action
     * @param string $name
     * @param string $action
     * @param array $args
     * @return mixed
     * @throws Exception
     */
    protected function _do($name, $action, $args = array()) {
        $actions = explode('->', $action);
        $obj = $this->_model($actions[0]);
        if (!is_callable(array($obj, $actions[1]))) {
            throw new Exception('无法call "' . $action . '"');
        }
        $GLOBALS['action']['data'][$name] = call_user_func_array(array($obj, $actions[1]), $args);
        return $GLOBALS['action']['data'][$name];
    }

    /**
     * 结束action
     * @return action
     */
    protected function _end() {
        $GLOBALS['action']['status'] = false;
        return $this;
    }

    /**
     * 最终action
     * @param string $action
     * @return action
     */
    protected function _final($action) {
        $GLOBALS['action']['final']['action'] = $action;
        $GLOBALS['action']['final']['args'] = array();
        $args = func_get_args();
        if (count($args > 1)) {
            $args = array_slice($args, 1);
            $GLOBALS['action']['final']['args'] = $args;
        }
        return $this;
    }

    /**
     * 注入action
     * @param string $name
     * @param string $action
     * @return action
     */
    protected function _inject($name, $action) {
        $GLOBALS['action']['inject'][$name]['action'] = $action;
        $GLOBALS['action']['inject'][$name]['args'] = array();
        $args = func_get_args();
        if (count($args > 2)) {
            $args = array_slice($args, 2);
            $GLOBALS['action']['inject'][$name]['args'] = $args;
        }
        return $this;
    }

    /**
     * 移除action
     * @param string $name
     * @return action
     */
    protected function _remove($name) {
        $GLOBALS['action']['remove'][] = $name;
        return $this;
    }

    /**
     * @param $action_name
     * @param $callback
     * @param bool $new_action
     * @return action
     */
    protected function _check($action_name, $callback, $new_action = false) {
        $GLOBALS['action']['check'][$action_name]['callback'] = $callback;
        $GLOBALS['action']['check'][$action_name]['new'] = $new_action;
        $GLOBALS['action']['check'][$action_name]['args'] = array();
        $args = func_get_args();
        if (count($args > 3)) {
            $args = array_slice($args, 3);
            $GLOBALS['action']['check'][$action_name]['args'] = $args;
        }
        return $this;
    }

    /**
     * 获取模块
     * @param string $name
     * @return mixed
     */
    protected function _model($name) {
        if (!$GLOBALS['model'][$name]) {
            $this->_import('app.model.' . $name . '_model');
            $name = $name.'_model';
            $GLOBALS['model'][$name] = new $name();
        }
        return $GLOBALS['model'][$name];
    }

}
