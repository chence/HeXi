<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午9:36
 * To change this template use File | Settings | File Templates.
 */
class Home extends Base_Controller {

    public function index() {
        $string = $this->view->with('header', 'header.html')
            ->fetch('index.html');
        $this->response->content($string)->send();
    }
}


