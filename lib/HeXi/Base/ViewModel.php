<?php

/**
 * 视图模型类
 *
 * 就是带了一个View类
 *
 * @property View view
 */
abstract class Base_ViewModel extends Base_Model {

    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * 设置视图文件夹
     * @param string $dir
     * @return Base_ViewModel
     */
    protected function viewDir($dir) {
        if (!is_dir($dir)) {
            Error::stop('ViewModel can not use an invalid template directory', 500);
        }
        $this->view->dir = $dir;
        return $this;
    }

    /**
     * 渲染视图
     * @param string $tpl
     * @param array $data
     * @return mixed
     */
    protected function fetch($tpl, $data = array()) {
        foreach ($data as $k => $v) {
            $this->view->set($k, $v);
        }
        return $this->fetch($tpl);
    }

    /**
     * 魔术方法获取View类
     * @param string $key
     * @return null|View
     */
    public function __get($key){
        if($key === 'view'){
            $this->view = $this->instance('View',get_class($this));
            return $this->view;
        }
        return null;
    }

}
