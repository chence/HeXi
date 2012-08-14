<?php
/**
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
class controller extends action {

    protected $web;

    public function __construct() {
        $this->web = web::init();
    }

    protected function _ajax($data,$type,$charset){

    }

    protected function _error($code){

    }
}
