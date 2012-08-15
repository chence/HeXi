<?php

/**
 * 文件缓存类
 */
class HeXiCacheFile extends HeXiCacheAdapter {
    /**
     * 缓存文件夹
     * @var string
     */
    protected $dir;

    /**
     * 初始化，获取默认配置
     * @throws HeXiCacheException
     */
    public function __construct() {
        $this->dir = HeXiWebApp::getPath() . HeXiConfig::get('cache', 'path');
        if (!is_dir($this->dir)) {
            throw new HeXiCacheException('FileCache Directory is invalid !');
        }
        $this->prefix = HeXiConfig::get('cache', 'prefix');
        $this->expire = HeXiConfig::get('cache', 'expire');
    }

    /**
     * 添加缓存数据
     * @param string $key 唯一键
     * @param mixed $value 要缓存的数据
     * @param int $expire 过期时间，默认0
     * @return HeXiCacheFile
     */
    public function set($key, $value, $expire = 0) {
        $key = $this->createUniqueKey($key);
        $content = $this->createCacheData($value, $expire);
        file_put_contents($this->dir . $key . '.cache', $content);
        return $this;
    }

    /**
     * 获取缓存
     * @param string $key 唯一键
     * @param bool $ignoreExpire 忽略缓存过期，如果过期返回false，忽略过期将返回数据，除非数据不存在
     * @return mixed|bool
     */
    public function get($key, $ignoreExpire = false) {
        $key = $this->createUniqueKey($key);
        $file = $this->dir . $key . '.cache';
        if (!is_file($file)) {
            return false;
        }
        #忽略过期问题，和永不过期两种情况下直接返回结果
        if ($ignoreExpire || $this->expire == 0) {
            return $this->parseCacheData(file_get_contents($file));
        }
        #如果缓存没有过期，就返回数据
        if (filemtime($file) + $this->expire > time()) {
            return $this->parseCacheData(file_get_contents($file));
        }
        return false;
    }

    /**
     * 获取一些缓存
     * @param array $keyArray
     * @return array
     */
    public function getSome($keyArray) {
        $data = array();
        foreach ($keyArray as $key) {
            $data[$key] = $this->get($key);
        }
        return $data;
    }

    /**
     * 删除缓存
     * @param string $key
     */
    public function remove($key) {
        $key = $this->createUniqueKey($key);
        $file = $this->dir . $key . '.cache';
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * 删除一些缓存
     * @param array $keyArray
     */
    public function removeSome($keyArray) {
        foreach ($keyArray as $key) {
            $this->remove($key);
        }
    }

    /**
     * 清空缓存
     */
    public function clear() {
        $dir = opendir($this->dir);
        while ($file = readdir($dir)) {
            if ($file != '.' || $file != '..') {
                $file = $this->dir . $file;
                unlink($file);
            }
        }
    }

    /**
     * 获取缓存大小
     * @return int
     */
    public function size() {
        $dir = opendir($this->dir);
        $size = 0;
        while ($file = readdir($dir)) {
            if ($file != '.' || $file != '..') {
                $file = $this->dir . $file;
                $size += filesize($file);
            }
        }
        return $size;
    }

}
