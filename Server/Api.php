<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 9:32
 */
namespace Server;

class Api extends \Dora\Server
{
    public $server_name;
    public $tcp_port;
    public $http_port;
    public $remote_shell_port;
    public $remote_shell_host;
    public $local_cache;
    public $pid_dir;
    public $data;
    public $configs;
    public $cache;
    public $host;
    public $ret;


    public function __construct()
    {

        $server_config = getCfg("server");
        \Dora\Packet::$ret = getCfg("ret");
        $this->server_name =ROOT.$server_config['name'];
        $this->tcp_port =$server_config['tcp_port'];
        $this->http_port =$server_config['http_port'];
        $this->remote_shell_port =$server_config['remote_shell_port'];
        $this->remote_shell_host =$server_config['remote_shell_host'];
        $this->pid_dir =ROOT;
        $this->externalConfig = $server_config['tcp_setting'];
        $this->externalHttpConfig = $server_config['http_setting'];
        $this->host = $server_config['host'];
        parent::__construct($this->host, $this->tcp_port, $this->http_port);
    }

    public function run(){

        $this->server->start();
    }

    function initServer($server)
    {
        //开启远程shell调试
        RemoteShell::listen($server,$this->remote_shell_host, $this->remote_shell_port);
        //开启热部署，自动更新业务代码
        new CodeReload($server,$this->server_name);
        //开启订阅服务
        new Subscribe($server,$this->server_name);
    }

    public function initStart($server){
//        //进程间通信 ipc 共享内存表
        setCache("master_pid",$server->master_pid);
        setCache("manager_pid",$server->manager_pid);
    }

    function doServiceWork($path_info,$params='')
    {
        return \Bootstrap::getInstance("service")->run($path_info,$params);
    }

    function doServiceDocWork($path_info,$params){

    }

    function doJcyWork($path_info,$params){

    }

    function doDefaultHttpWork($path_info,$params){

    }

    function initTask($server, $worker_id)
    {
        \Bootstrap::getInstance("service");
    }
}