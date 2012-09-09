<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-9-7
 * Time: 上午10:55
 */

$config = array();

$config['class'] = array(
    'Router' => 'Core.Router',
    'Db' => 'Db.Db',
    'Controller' => 'Mvc.Controller',
    'Model' => 'Mvc.Model',
    'View' => 'Mvc.View',
    'Request' => 'Web.Request',
    'Response' => 'Web.Response'
);

$config['controller'] = array(
    'path' => APP_PATH . 'Controller' . DS,
    'default' => 'index',
    'method' => array(
        'default' => 'index',
        'capture' => true
    )
);

$config['model'] = array(
    'path' => APP_PATH . 'Model' . DS,
    'database' => true
);

$config['view'] = array(
    'path' => APP_PATH . 'View' . DS,
    'suffix' => 'html',
    'compile' => array(
        'auto' => true,
        'path' => APP_PATH . 'Runtime' . DS . 'Compile' . DS,
        'suffix' => 'php',
        'expire' => -3600
    ),
    'cache' => array(
        'path' => APP_PATH . 'Runtime' . DS . 'Html' . DS,
        'expire' => -3600,
        'suffix'=>'html',
        'use'=>true
    )
);

$config['cookie'] = array(
    'path' => '/',
    'domain' => null,
    'expire' => 24 * 3600
);

$config['session'] = array(
    'auto' => true
);

$config['error'] = array(
    'silent' => false
);

$config['upload'] = array(
    'path' => APP_PATH . 'upload' . DS,
    'files' => 'jpg,png,gif',
    'mime' => false,
    'saving' => 'day',
    'maxsize' => 1024 * 1024 * 5
);

$config['cache'] = array(
    'file'=>array(
        'path' => APP_PATH . 'Runtime' . DS . 'Cache' . DS,
        'expire' => 3600,
        'suffix'=>'dat',
    )
);

