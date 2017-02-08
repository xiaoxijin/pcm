<?php


define("DS",DIRECTORY_SEPARATOR);
define("BS",'\\');

define("ROOT", __DIR__.DS);

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);//注册service的顶级名称空间
function pushFailedMsg($msg){
    array_push(\Bootstrap\Service::$failed_msg_history,$msg);
}

function popFailedMsg(){
    return array_pop(\Bootstrap\Service::$failed_msg_history);
}

function cleanPackEnv(){
    \Bootstrap\Service::$failed_msg_history=[];
    \Bootstrap\Service::$service_history=[];
}

function getCfg($key){
    return \Data::getInstance()->$key;
}


function getCache($key){
    return \Data::getInstance()->data("Cache")->get($key);
}

function setCache($key, $value, $expire=0){
    return \Data::getInstance()->data("Cache")->set($key, $value, $expire);
}

function delCache($key){
    return \Data::getInstance()->data("Cache")->get($key);
}

/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没`有，则默认为寻找服务类名
 */
function service($path_info,$params=''){
    return \Bootstrap::getInstance("service")->run($path_info,$params);
}

function lib($lib_name){
    return \Factory::getInstance()->getProduct("lib",$lib_name);
}

// 用户定义的错误处理函数
function xphpErrorExceptionHandler($errno, $errstr, $errfile, $errline ) {

    throw new \ErrorException('SYSTEM_ERROR');
}
set_error_handler("xphpErrorExceptionHandler");
$task = 'user/get';
//$task['name'] = 'ucenter/user';
//$task['params'] = array(
//    'member_id'=>1
//);
var_dump(service($task));
//\Bootstrap::getInstance(PHP_SAPI)->run();


