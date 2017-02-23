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
    static $prefix='_c_r_';
    static $flag='|';
    function __construct($flag='master')
    {
        $this->remote=new \Client\Redis($flag);
    }

    static function encodeKey($key){

        return self::getPrefix().$key;
    }

    static function getPrefix(){
        $port = \Cache::get_l('port');
        if($port)
            $p_prefix = $port.\Cfg::getEnvName();
        else
            $p_prefix = \Cfg::getEnvName();
        return self::$prefix.$p_prefix.self::$flag;
    }

    static function decodeKey($key){
        $prefix = self::getPrefix();
        if($prefix == substr($key, 0,strlen($prefix)))
            return substr($key, strlen($prefix));
        else
            return $key;
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
        if ($expire <= 0)
        {
            $expire = 0x7fffffff;
        }
        return $this->remote->setex($this->encodeKey($key),$expire,$value);
    }

    /**
     * 获取缓存值
     * @param $key
     * @return mixed
     */
    function get($key)
    {
        return $this->remote->get($this->encodeKey($key));
    }

    /**
     * 删除缓存值
     * @param $key
     * @return bool
     */
    function del($key)
    {
        return $this->remote->del($this->encodeKey($key));
    }
}
