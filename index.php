<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午8:39
 * @link      : http://hexiaz.com
 *
 *
 */
error_reporting(E_ALL ^ E_NOTICE);

define('DS', DIRECTORY_SEPARATOR);

define('NOW', time());

define('RootDir', __DIR__ . DS);

define('AppName', 'App');

define(AppName . 'Dir', RootDir . 'app' . DS);

define('LibDir', RootDir . 'lib' . DS);

define('HeXiDir', LibDir . 'HeXi' . DS);

require HeXiDir.'HeXi.php';

HeXi::run();

Error::stop('错误');
