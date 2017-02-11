<?php

//var_dump(date('D, d-M-Y H:i:s T',time()));
//exit;
//class test1{
//
//}
//$arr = [1,2,34,5,6,7,8,9,1234];
//$msg = msgpack_pack($data);
//$data = msgpack_unpack($msg);
//var_dump($msg);
//var_dump($data);
//$o = new swSerialize();
//$str = $o->fastPack($arr);
//var_dump($str);
//var_dump($o->unpack($str));
//exit;

//var_dump(preg_match_all(''));
//exit;
//测试镜像功能
define("DS",DIRECTORY_SEPARATOR);
define("BS",'\\');

define("ROOT", __DIR__.DS);
error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);//注册service的顶级名称空间

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


//$task = "hjf@";
//var_dump(Validate::notService($task));
//$task['name'] = 'ucenter/user';
//$task['params'] = array(
//    'member_id'=>1
//);
//var_dump(service($task));


\Bootstrap::getInstance(PHP_SAPI)->run();


