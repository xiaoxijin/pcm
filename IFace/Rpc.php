<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/14
 * Time: 13:37
 */
namespace IFace;
interface Rpc extends MultiProcess{
    function onStart($server);
    function onManagerStart($server);
    function onWorkerStart($server,$worker_id);
    function onTask($server, $task_id, $src_worker_id, $data);
    function onWorkerError($server, $worker_id, $worker_pid, $exit_code);
    function onFinish($server,$task_id,$data);
    //    function onWorkerStop($server, $worker_id);

}