<?php

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();



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

//var_dump(service('gym/get',['is_default'=>'1']));exit;

switch (PHP_SAPI)
{
    case "cli":
        $server = new \Server();
        $server->run();
        break;
    case "service":
        Service();
        break;
    default:
        throw new \Exception("PHP_SAPI IS NOT DEFINED");
}
//\Bootstrap::getInstance(PHP_SAPI)->run();


