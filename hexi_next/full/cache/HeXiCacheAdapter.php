<?php

/**
 * 异常类抽象类
 */
abstract class HeXiCacheAdapter {

    /**
     * 缓存的前缀
     * @var string
     */
    protected $prefix;

    /**
     * 过期时间，默认值0说明永不过期
     * @var int
     */
    protected $expire = 0;

    /**
     * 添加缓存数据
     * @abstract
     * @param string $key 唯一键
     * @param mixed $value 要缓存的数据
     * @param int $expire 过期时间，默认0
     * @return mixed
     */
    abstract public function set($key, $value, $expire = 0);

    /**
     * 获取缓存
     * @abstract
     * @param string $key 唯一键
     * @param bool $ignoreExpire 忽略缓存过期，如果过期返回false，忽略过期将返回数据，除非数据不存在
     * @return mixed
     */
    abstract public function get($key, $ignoreExpire = false);

    /**
     * 获取一些缓存
     * @abstract
     * @param array $keyArray
     * @return mixed
     */
    abstract public function getSome($keyArray);

    /**
     * 删除缓存
     * @abstract
     * @param string $key
     * @return mixed
     */
    abstract public function remove($key);

    /**
     * 删除一些缓存
     * @abstract
     * @param string $keyArray
     * @return mixed
     */
    abstract public function removeSome($keyArray);

    /**
     * 清空缓存
     * @abstract
     * @return mixed
     */
    abstract public function clear();

    /**
     * 获取缓存大小
     * @abstract
     * @return int
     */
    abstract public function size();

    /**
     * 生成缓存唯一键
     * @param string $key
     * @return string
     */
    protected function createUniqueKey($key) {
        $key = md5($this->prefix . '-' . $key);
        return $key;
    }

    /**
     * 组合缓存数据成字符串
     * @param mixed $data
     * @param int $expire
     * @return string
     */
    protected function createCacheData($data, $expire) {
        $cacheData = array(
            'data'  => serialize($data),
            'expire'=> $expire
        );
        return serialize($cacheData);
    }

    /**
     * 解析缓存字符串，回复数据结构
     * @param string $data
     * @return array
     */
    protected function parseCacheData($data) {
        $cacheData = unserialize($data);
        return array('expire'=> $cacheData['expire'],
                     'data'  => unserialize($cacheData['data']));
    }

}
