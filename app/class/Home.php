<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午9:36
 * To change this template use File | Settings | File Templates.
 */ 
class Home extends Base_Controller{

    public function index(){
        //session_start();
        //$this->input->cookie('test',time(),3600);
        //var_dump($this->input->cookie());
        //$this->input->session('user.name','傅小黑');
        //$this->input->sessionDelete('name');
        $this->input->session('user',Input::SESSION_DELETE);
        var_dump($this->input->session());
    }
}
