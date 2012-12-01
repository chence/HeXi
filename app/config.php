<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午8:02
 * @link      : http://hexiaz.com
 *
 *
 */

return array(
    'app'      => array(
        'controller' => array(
            'command' => AppName . '.controller',
            'default' => 'index',
            'action'  => 'index',
            'map'     => true
        ),
        'model'      => array(
            'command'  => AppName . '.model',
            'database' => 'default'
        ),
        'view'       => array(
            'command' => AppName . '.view',
            'suffix'  => 'html'
        ),
        'error'      => array(
            'log'      => AppName . '.log',
            'profile'  => true,
            'trace'    => true,
            'template' => 'error'
        )
    ),
    'database' => array(
        'default' => array(
            'driver' => 'PDO',
            'type'   => 'sqlite',
            'file'   => constant(AppName . 'Dir') . 'database.php'
        )
    )
);