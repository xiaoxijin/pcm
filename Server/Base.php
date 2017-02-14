<?php
namespace Server;

/**
 * 服务基类，实现一些公用的方法
 * @package Swoole\Protocol
 */
abstract class Base implements \IFace\Driver
{

    /**
     */
    public $server;

    function run($array)
    {
        $this->server->run($array);
    }

    function daemonize(){
        $this->server->daemonize();
    }

    function task($task, $dstWorkerId = -1, $callback = null)
    {
        $this->server->task($task, $dstWorkerId = -1, $callback);
    }




}