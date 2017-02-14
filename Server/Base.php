<?php
namespace Server;

/**
 * 服务基类，实现一些公用的方法
 * @package Swoole\Protocol
 */
abstract class Base implements \IFace\Driver
{
    public $server;
    public $host;
    public $port;
    public $sock_type;
    public $pid_file;
    public $config=[
        'dispatch_mode' => 3,
        'package_max_length' => 1024 * 1024 * 2,
        'buffer_output_size' => 1024 * 1024 * 3,
        'pipe_buffer_size' => 1024 * 1024 * 32,
        'heartbeat_check_interval' => 5,
        'heartbeat_idle_time' => 10,
        'open_cpu_affinity' => 1,
        'backlog' => 3000,
        'log_file' => '/tmp/sw_server.log',
        'task_tmpdir' => '/tmp/swtasktmp/',
        'daemonize' => 1,
    ];

    function __construct($host='0.0.0.0', $port='9566',$mode = SWOOLE_PROCESS, $sock_type = SWOOLE_SOCK_TCP)
    {
        $this->host=$host;
        $this->port=$port;
        $this->sock_type=$sock_type;
        $this->server =  new \Swoole\Server($host,$port,$mode,$sock_type);
    }

    /**
     * 杀死所有进程
     * @param $name
     * @param int $signo
     * @return string
     */
    static function killProcessByName($name, $signo = 9)
    {
        $cmd = 'ps -eaf |grep "' . $name . '" | grep -v "grep"| awk "{print $2}"|xargs kill -'.$signo;
        return exec($cmd);
    }

    /**
     * Configuration Server.必须在start之前执行
     *
     * @param array $config
     * @return $this
     */
    function setConfigure(array $external_config=[]){
        $this->config=array_merge($this->config,$external_config);
        $this->server->set($this->config);
    }

    function setCallBack($call_back_functions){
        foreach ($call_back_functions as $cb_name=>$cb_exec){
            $this->server->on($cb_name, [$this,$cb_exec]);
        }
    }

    function start(){
        $this->server->start();
    }
//    function run($array)
//    {
//        $this->server->run($array);
//    }
//
//    function daemonize(){
//        $this->server->daemonize();
//    }
//
//    function task($task, $dstWorkerId = -1, $callback = null)
//    {
//        $this->server->task($task, $dstWorkerId = -1, $callback);
//    }




}