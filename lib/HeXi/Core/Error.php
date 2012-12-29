<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午6:01
 * To change this template use File | Settings | File Templates.
 */

class Error{

    public static function stop($message,$status){
        echo $status.'<br/>';
        echo $message;
        die;
    }
}