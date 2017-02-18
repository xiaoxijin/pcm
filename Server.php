<?php
/**
 * Date: 2017/1/16
 * Time: 9:32
 */
define("DS",DIRECTORY_SEPARATOR);
define("ROOT", __DIR__.DS);
error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require_once('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);

function pushFailedMsg($msg){
    array_push(\Service::$failed_msg_history,$msg);
    return false;
}

function popFailedMsg(){
    return array_pop(\Service::$failed_msg_history);
}

function cleanPackEnv(){
    \Service::$failed_msg_history=[];
//    \Bootstrap\Service::$service_history=[];
}
/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没`有，则默认为寻找服务类名
 */
function service($path_info,$params=''){
    return \Service::getInstance()->run($path_info,$params);
}

function lib($lib_name){
    return \Factory::getInstance()->getProduct("lib",$lib_name);
}

function xphpExceptionHandler($exception) {
    echo $exception->getMessage();
}

// 设置自定义的异常处理函数
set_exception_handler("xphpExceptionHandler");

function xphpErrorExceptionHandler($errno, $errstr, $errfile, $errline ) {

    throw new \ErrorException('SYSTEM_ERROR');
}
//设置自定义的错误处理函数

set_error_handler("xphpErrorExceptionHandler");
//$task='ucenter/member/list';
//var_dump(service($task));
//exit;
class Server extends \Server\Rpc
{

    function initServer($server)
    {
        //开启远程shell调试
//        $remote_shell_config = \Cfg::get("remote_shell");
//        \Server\RemoteShell::listen($server,$remote_shell_config['host'], $remote_shell_config['port']);
        //开启热部署，自动更新业务代码
        new \Server\CodeReload($server,$this->server_name);
        //开启订阅服务
        new \Server\Subscribe($server,$this->server_name);
        //开启文档服务
        new \Server\Doc($server,$this->server_name);
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

$server = new \Server();
$server->start();