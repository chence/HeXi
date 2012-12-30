<?php
/**
 * 控制器基类
 * @property Input    input
 * @property Request  request
 * @property Response response
 * @property View     view
 */
class Base_Controller extends Base_Class {

    public function __get($key) {
        switch ($key) {
            case 'input':
                $this->input = Input::instance();
                return $this->input;
            case 'request':
                $this->request = Request::instance();
                return $this->request;
            case 'response':
                $this->response = Response::instance();
                return $this->response;
            case 'view':
                $this->view = $this->instance('View', 'global');
                return $this->view;
        }
        return null;
    }

}

