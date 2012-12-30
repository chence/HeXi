<?php

date_default_timezone_set('PRC');

error_reporting(E_ALL ^ E_NOTICE ^ E_STRICT);

require_once 'lib/HeXi/HeXi.php';

$app = new HeXi(__DIR__ . '/app/');

/*
$app->router->get('/blog/slug::string', function(){
    var_dump($GLOBALS);
});
*/

$app->run();