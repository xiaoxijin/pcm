<?php
namespace Client;


class Redis  implements \IFace\Cache
{
    const READ_LINE_NUMBER = 0;
    const READ_LENGTH = 1;
    const READ_DATA = 2;

    static $prefix='_c_r_';
    static $flag='|';
    protected $_redis;
    private $config;
    private $re_connect_count=3;//重连次数控制

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

    function __construct($flag)
    {
        $this->config = \Cfg::get("redis")[$flag];
        $this->connect();
    }

    function connect()
    {
        try
        {
            if ($this->_redis)
            {
                unset($this->_redis);
            }
            $this->_redis = new \Redis();
            if ($this->config['pconnect'])
            {
                $this->_redis->pconnect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }
            else
            {
                $this->_redis->connect($this->config['host'], $this->config['port'], $this->config['timeout']);
            }

            if (!empty($this->config['password']))
            {
                $this->_redis->auth($this->config['password']);
            }
            if (!empty($this->config['database']))
            {
                $this->_redis->select($this->config['database']);
            }
        }
        catch (\RedisException $e)
        {
//            ::$php->log->error(__CLASS__ . " Swoole Redis Exception" . var_export($e, 1));
            return false;
        }
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
        return $this->_redis->setex($this->encodeKey($key),$expire,$value);
    }

    /**
     * 获取缓存值
     * @param $key
     * @return mixed
     */
    function get($key)
    {
        return $this->_redis->get($this->encodeKey($key));
    }

    /**
     * 删除缓存值
     * @param $key
     * @return bool
     */
    function del($key)
    {
        return $this->_redis->del($this->encodeKey($key));
    }

    function __call($method, $args = array())
    {

        for($re_connect_num = 0;$re_connect_num<$this->re_connect_count;$re_connect_num++)
        {
            try
            {
                $result = call_user_func_array(array($this->_redis, $method), $args);
            }
            catch (\RedisException $e)
            {
//                ::$php->log->error(__CLASS__ . " [" . posix_getpid() . "] Swoole Redis[{$this->config['host']}:{$this->config['port']}]
//                 Exception(Msg=" . $e->getMessage() . ", Code=" . $e->getCode() . "), Redis->{$method}, Params=" . var_export($args, 1));
                if ($this->_redis->isConnected())
                {
                    $this->_redis->close();
                }
                $this->connect();
                continue;
            }
            return $result;
        }
        //不可能到这里
        return false;
    }
}