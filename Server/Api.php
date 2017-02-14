<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 9:32
 */
namespace Server;

class Api extends Rpc
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
        $server_config = getCfg("api");
        \Dora\Packet::$ret = getCfg("ret");
        $this->server_name =ROOT.$server_config['name'];
        $this->pid_dir =ROOT;
        parent::__construct($server_config['host'], $server_config['port']);
        $this->setConfigure($server_config['setting']);
    }

    public function run(){
        $this->start();
    }

    function initServer($server)
    {
        //开启远程shell调试
        $remote_shell_config = getCfg("remote_shell");
        RemoteShell::listen($server,$remote_shell_config['host'], $remote_shell_config['port']);
        //开启热部署，自动更新业务代码
        new CodeReload($server,$this->server_name);
        //开启订阅服务
        new Subscribe($server,$this->server_name);
    }


    function doServiceWork($path_info,$params='')
    {
        return \Bootstrap::getInstance("service")->run($path_info,$params);
    }

    function doJcyWork($path_info,$params){

    }

    function initTask($server, $worker_id)
    {
        \Bootstrap::getInstance("service");
    }
}