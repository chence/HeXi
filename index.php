<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午7:36
 * @link      : http://hexiaz.com
 *
 *
 */

error_reporting(E_ALL ^ E_NOTICE);

$GLOBALS['profile'] = array(
    'time'   => microtime(true),
    'memory' => memory_get_usage()
);

define('RootDir', $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR);

define('LibDir', RootDir . 'lib' . DIRECTORY_SEPARATOR);

define('HeXiDir', LibDir . 'HeXi' . DIRECTORY_SEPARATOR);

define('AppName', 'App');

define(AppName . 'Dir', RootDir . 'app' . DIRECTORY_SEPARATOR);

require HeXiDir . 'HeXi.php';

HeXi::run();