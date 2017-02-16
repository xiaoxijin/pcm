<?php
namespace Cache;


/**
 * 使用Redis作为远程Cache
 * Class Redis
 *
 */
class Remote implements \IFace\Cache
{
    protected $remote;
    protected $redis;

    function __construct($config)
    {
        return new \Client\Redis('master');
    }

    /**
     * 设置缓存
     * @param $key
     * @param $value
     * @param $expire
     * @return bool
     */
    function set($key, $value, $expire = 0)
    {
//        if ($expire <= 0)
//        {
//            $expire = 0x7fffffff;
//        }
        return $this->redis->setex($key, $expire, serialize($value));
    }

    /**
     * 获取缓存值
     * @param $key
     * @return mixed
     */
    function get($key)
    {
        return unserialize($this->redis->get($key));
    }

    /**
     * 删除缓存值
     * @param $key
     * @return bool
     */
    function del($key)
    {
        return $this->redis->del($key);
    }
}
