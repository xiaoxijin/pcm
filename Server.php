<?php
/**
 * Date: 2017/1/16
 * Time: 9:32
 */

class Server extends \Server\Rpc
{

    function initServer($server)
    {
        //开启远程shell调试
        $remote_shell_config = \Cfg::get("remote_shell");
        \Server\RemoteShell::listen($server,$remote_shell_config['host'], $remote_shell_config['port']);
        //开启热部署，自动更新业务代码
        new \Server\CodeReload($server,$this->server_name);
        //开启订阅服务
        new \Server\Subscribe($server,$this->server_name);
    }


    function doServiceWork($path_info,$params='')
    {
        return \Service::getInstance()->run($path_info,$params);
    }

    function doJcyWork($path_info,$params){

    }

    function initTask($server, $worker_id)
    {
        \Service::getInstance();
    }
}

