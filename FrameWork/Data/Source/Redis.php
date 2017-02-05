<?php
namespace Xphp\Data\Source;


class Redis
{
    const READ_LINE_NUMBER = 0;
    const READ_LENGTH = 1;
    const READ_DATA = 2;

    private $_redis;
    private $config;
    private $re_connect_count=3;//重连次数控制

    public static $prefix = "autoinc_key:";

    function __construct($flag)
    {
        $this->config = getCfg("redis")[$flag];
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
//            \Xphp::$php->log->error(__CLASS__ . " Swoole Redis Exception" . var_export($e, 1));
            return false;
        }
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
//                \Xphp::$php->log->error(__CLASS__ . " [" . posix_getpid() . "] Swoole Redis[{$this->config['host']}:{$this->config['port']}]
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