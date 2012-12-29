<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午6:02
 * To change this template use File | Settings | File Templates.
 */

date_default_timezone_set('PRC');

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

require_once 'lib/HeXi/HeXi.php';

$app = new HeXi(__DIR__ . '/app/');

$app->router->get('/blog/slug::string', function(){
    var_dump($GLOBALS);
});

$app->run();