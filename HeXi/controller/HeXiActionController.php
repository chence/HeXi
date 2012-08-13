<?php

/**
 * Action控制器类
 */
class HeXiActionController extends HeXiController {

    /**
     * 初始化
     * 定义Action状态
     */
    public function __construct() {
        parent::__construct();
        $GLOBALS['action']['status'] = true;
        $GLOBALS['action']['remove'] = array();
    }

    /**
     * 添加Action
     * @param string $name 称呼
     * @param string $method 方法
     * @param array $args 参数
     * @return HeXiActionController
     */
    protected function _call($name, $method, $args = array()) {
        $GLOBALS['action']['list'][$name]['method'] = $method;
        $GLOBALS['action']['list'][$name]['args'] = $args;
        return $this;
    }

    /**
     * 注入Action
     * @param string $name
     * @param string $method
     * @param array $args
     * @return HeXiActionController
     */
    protected function _inject($name, $method, $args = array()) {
        $GLOBALS['action']['inject'][$name]['method'] = $method;
        $GLOBALS['action']['inject'][$name]['args'] = $args;
        return $this;
    }

    /**
     * 执行添加的Action
     * @return bool
     */
    protected function _invoke() {
        foreach ($GLOBALS['action']['list'] as $name=> $act) {
            #如果标记为end，执行最终操作final，并返回false
            if (!$GLOBALS['action']['status']) {
                $this->_direct('final', $GLOBALS['action']['final']['method'], $GLOBALS['action']['final']['args']);
                return false;
            }
            #如果是移除操作，清理数组内容，并重新下一轮循环
            if (in_array($name, $GLOBALS['action']['remove'])) {
                unset($GLOBALS['action']['list'][$name]);
                unset($GLOBALS['action']['inject'][$name]);
                continue;
            }
            #如果是注入操作，以注入的为优先
            if (isset($GLOBALS['action']['inject'][$name])) {
                $act = $GLOBALS['action']['inject'][$name];
            }
            #执行操作
            $this->_direct($name, $act['method'], $act['args']);
        }
        #返回所有数据
        return $GLOBALS['action']['data'];
    }

    /**
     * 获取所有Action数据
     * @param string $type
     * @return mixed
     */
    protected function _list($type = 'all') {
        if ($type == 'list') {
            return $GLOBALS['action']['list'];
        }
        if ($type == 'inject') {
            return $GLOBALS['action']['inject'];
        }
        return $GLOBALS['action'];
    }

    /**
     * 移除Action操作
     * @param string $name
     * @return HeXiActionController
     */
    protected function _remove($name) {
        $GLOBALS['action']['remove'][] = $name;
        return $this;
    }

    /**
     * 执行Action
     * @param string $name
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws HeXiControllerException
     */
    protected function _direct($name, $method, $args = array()) {
        #分析和获取调用的对象
        $methods = explode('->', $method);
        if ($methods[0] == 'View') {
            #获取视图
            $object = HeXiView::instance();
        }
        elseif (strstr($methods[0], 'Model')) {
            #获取模型
            $object = HeXiModelFactory::factory($methods[0]);
        } else {
            throw new HeXiControllerException('Invalid action method call "' . $method . '" !');
        }
        #判断调用是否有效
        if (!is_callable(array($object, $methods[1]))) {
            throw new HeXiControllerException('Invalid method "' . $methods[1] . '" in object "' . $methods[0] . '" !');
        }
        #调用操作
        $GLOBALS['action']['data'][$name] = call_user_func_array(array($object, $methods[1]), $args);
        #从队列清除操作，防止重复执行
        unset($GLOBALS['action']['list'][$name]);
        unset($GLOBALS['action']['inject'][$name]);
        #日志记录
        if (HEXI_DEBUG) {
            HeXiLogger::write('Invoke action "' . $method . '"' . ' as "' . $name . '"', $method, __FILE__, __LINE__);
        }
        #返回数据
        return $GLOBALS['action']['data'][$name];
    }

    /**
     * 标记Action为停止，开始使用final操作
     * @return HeXiActionController
     */
    protected function _end() {
        $GLOBALS['action']['status'] = false;
        return $this;
    }

    /**
     * 最终操作，当Action标记为停止后处理
     * @param string $method
     * @param array $args
     * @return HeXiActionController
     */
    protected function _final($method, $args = array()) {
        $GLOBALS['action']['final']['method'] = $method;
        $GLOBALS['action']['final']['args'] = $args;
        return $this;
    }

}

