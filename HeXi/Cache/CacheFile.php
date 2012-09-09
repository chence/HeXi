<?php

/**
 * @author FuXiaoHei
 */
class CacheFile extends abstractCache {

    /**
     * 文件缓存保存目录
     * @var string
     */
    public $path;

    /**
     * 过期时间
     * @var int
     */
    public $expire;

    /**
     * 后缀名
     * @var string
     */
    public $suffix;

    /**
     * 初始化
     */
    public function __construct() {
        $this->reset();
    }

    /**
     * 重置配置，使用系统默认
     * @return CacheFile
     */
    public function reset() {
        $this->path = config('cache.file.path');
        $this->expire = (int)config('cache.file.expire');
        $this->suffix = config('cache.file.suffix');
        return $this;
    }

    /**
     * 获取缓存
     * 不存在返回false，过期返回null
     * @param string $key
     * @return bool|null|string
     */
    public function get($key) {
        $cacheFile = $this->path . $key . '.' . $this->suffix;
        if (!is_file($cacheFile)) {
            return false;
        }
        if (filemtime($cacheFile) + $this->expire < NOW) {
            return null;
        }
        return file_get_contents($cacheFile);
    }

    /**
     * 设置缓存
     * @param string $key
     * @param string $value
     * @param int $expire 只是为了实现抽象类，这个参数无效
     */
    public function set($key, $value, $expire = 1) {
        $cacheFile = $this->path . $key . '.' . $this->suffix;
        file_put_contents($cacheFile, $value);
    }

    /**
     * 缓存是否有效
     * 存在还是过期
     * @param string $key
     * @return bool|null
     */
    public function valid($key) {
        $cacheFile = $this->path . $key . '.' . $this->suffix;
        if (!is_file($cacheFile)) {
            return false;
        }
        if (filemtime($cacheFile) + $this->expire < NOW) {
            return null;
        }
        return true;
    }

    /**
     * 删除缓存
     * @param string $key
     */
    public function del($key) {
        $cacheFile = $this->path . $key . '.' . $this->suffix;
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    /**
     * 清空缓存
     * 只删除对应后缀名的文件
     * 不处理子文件夹
     */
    public function clear() {
        foreach (new DirectoryIterator($this->path) as $file) {
            if ($file->isFile()) {
                if ($file->getExtension() == $this->suffix) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * 缓存文件的大小
     * @return int
     */
    public function size() {
        $size = 0;
        foreach (new DirectoryIterator($this->path) as $file) {
            if ($file->isFile()) {
                if ($file->getExtension() == $this->suffix) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }

}
