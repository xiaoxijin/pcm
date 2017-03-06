<?php


require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架
\Cfg::setEnvName();
\Packet::$ret = \Cfg::get("ret");
\Packet::$task_type = \Cfg::get("rpc.tasktype");
//\Log::put("法规和电饭锅和");
//exit;

//$pdodb = \DB\Connector::get('master');

$Miner = new \DB\Miner();
$Miner->select('c.course_id')
//    ->select('name')
    ->from('course','c')
    ->innerJoin('episodes', 'show_id')
    ->where('shows.network_id', 12)
    ->orderBy('episodes.aired_on', \DB\Miner::ORDER_BY_DESC)
    ->limit(20);

//var_dump($Miner->getStatement());
var_dump($Miner->getStatement(false));

//var_dump($Miner->getPlaceholderValues());



exit;
//$data = \Task::getInstance()->runService("index");

//$data = \Task::getInstance()->runService("getCourseListByDate", [
//    'ret_key'=>'courseList',
//    'gymId'=>1,
//    'date'=>'2017-02-28'
//]);
var_dump($data);
exit;