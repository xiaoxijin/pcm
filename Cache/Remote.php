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
    function __construct($flag='master')
    {
        $this->remote=new \Client\Redis($flag);
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

        return $this->remote->set($key, $value, $expire);
    }

    /**
     * 获取缓存值
     * @param $key
     * @return mixed
     */
    function get($key)
    {
        return $this->remote->get($key);
    }

    /**
     * 删除缓存值
     * @param $key
     * @return bool
     */
    function del($key)
    {
        return $this->remote->del($key);
    }
}
