<?php

//class aaa{
//
//    function bbb($id,$name){
//        return [];
//    }
//}
//
//$reflection = new ReflectionMethod('aaa', 'bbb');
//
//foreach($reflection->getParameters() AS $arg)
//{
//    var_dump($arg->name);
//}
//

require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架
\Cfg::setEnvName();
\Packet::$ret = \Cfg::get("ret");
\Packet::$task_type = \Cfg::get("rpc.tasktype");
\Log::put("法规和电饭锅和");
exit;

//$data = \Task::getInstance()->runService("index");

//$data = \Task::getInstance()->runService("getCourseListByDate", [
//    'ret_key'=>'courseList',
//    'gymId'=>1,
//    'date'=>'2017-02-28'
//]);
var_dump($data);
exit;