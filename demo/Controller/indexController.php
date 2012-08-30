<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-8-29
 * Time: 下午7:43
 */
/**
 * @author FuXiaoHei
 */

class indexController extends Controller{

    public function __construct(){
        parent::__construct();
        //$this->filter('before_index','test');
    }

    public function index(){
        $this->model('index->test');
    }

    protected function test(){

    }
}
