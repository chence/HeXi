<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-9-7
 * Time: 下午2:20
 */

define('DS', DIRECTORY_SEPARATOR);

define('NOW', time());

define('EOL', PHP_EOL);

define('TIMEZONE', 'PRC');

//-----------------------------

define('ROOT_PATH', dirname(__FILE__) . DS);

define('HEXI_PATH', ROOT_PATH . 'HeXi' . DS);

define('APP_PATH', ROOT_PATH . 'demo' . DS);

//---------------------------

require_once HEXI_PATH.'config.php';

require_once HEXI_PATH.'HeXi.php';

HeXi::run($config);