<?php

/**
 * 视图模型类
 *
 * 就是带了一个View类
 */
abstract class Base_ViewModel extends Base_Model {

    /**
     * View类
     * @var View
     */
    protected $view;

    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct();
        $this->view = $this->instance('View', get_class($this));
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

}
