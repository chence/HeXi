<?php

define('DS', DIRECTORY_SEPARATOR);

define('NOW', time());

define('EOL', PHP_EOL);

define('TIMEZONE', 'PRC');

//-----------------------------

define('ROOT_PATH', dirname(__FILE__) . DS);

define('HEXI_PATH', ROOT_PATH . 'HeXi' . DS);

define('APP_PATH', ROOT_PATH . 'demo' . DS);

//----------------

define('CONTROLLER_PATH', APP_PATH . 'Controller' . DS);

define('CONTROLLER_DEFAULT', 'index');

define('CONTROLLER_METHOD_DEFAULT', 'index');

//--------------

define('MODEL_PATH', APP_PATH . 'Model' . DS);

define('MODEL_DB_AUTO', true);

//-------------

define('VIEW_PATH', APP_PATH . 'View' . DS);

define('VIEW_SUFFIX', 'html');

define('VIEW_COMPILE_PATH', APP_PATH . 'Compile' . DS);

define('VIEW_COMPILE_EXPIRE', 3600);

define('VIEW_COMPILE_SUFFIX', 'php');

define('VIEW_COMPILE_AUTO', true);

//------------

define('REQUEST_SERVER_AUTO', true);

define('REQUEST_FILES_AUTO', false);

//----------

define('DB_DRIVER', 'pdo');

define('DB_TYPE', 'sqlite');

define('DB_HOST', 'localhost');

define('DB_PORT', 3306);

define('DB_DBNAME', 'test');

define('DB_FILE', APP_PATH . 'database.db');

define('DB_USER', 'fuxiaohei');

define('DB_PWD', 'fuxiaohei');

define('DB_CHARSET','utf8');

//-----------

require_once HEXI_PATH . 'HeXi.php';

HeXi::run();