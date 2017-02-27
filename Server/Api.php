<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/23
 * Time: 15:22
 */

namespace Server;


class Api extends Rpc
{
    function initServer($server)
    {
        //开启远程shell调试
//        $remote_shell_config = \Cfg::get("remote_shell");
//        \Server\RemoteShell::listen($server,$remote_shell_config['host'], $remote_shell_config['port']);
        //开启热部署，自动更新业务代码
        new CodeReload($server,$this->server_name);
        //开启订阅服务
        new Subscribe($server,$this->server_name);

    }


    function initTask($server, $worker_id)
    {
        \Service::getInstance();
    }

}