<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-9-8
 * Time: 上午11:33
 */
/**
 * @author FuXiaoHei
 */
class Cache {

    /**
     * 缓存对象
     * @var array
     */
    protected static $obj = array();


    public static final function init($type) {

    }

    /**
     * @param string $type
     * @return CacheFile
     */
    public static final function get($type){
        import('HeXi.Cache.CacheFile');
        return new CacheFile();
    }
}

abstract class abstractCache{

    abstract public function get($key);

    abstract public function set($key,$value,$expire = 1);

    abstract public function valid($key);

    abstract public function del($key);

    abstract public function clear();

    abstract public function reset();
}

