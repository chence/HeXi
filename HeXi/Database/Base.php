<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午10:41
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 数据库连接的基类
 * @package Database
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

abstract class Database_Base {

    protected $database;

    protected $statement;

    abstract public function exec($sql, $param = array());

    abstract public function query($sql, $param = array());

    abstract public function fetch();

    abstract public function fetchAll();

    abstract public function fetchGroup($rowName);

    abstract public function lastId();

    abstract public function affectRows();

    protected function error($message) {
        Error::stop($message);
    }
}
