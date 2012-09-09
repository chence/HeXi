<?php

/**
 * @author FuXiaoHei
 */
class indexController extends Controller {

    public function index() {
        $this->response->view('index',array('content'=>NOW));
    }

}
