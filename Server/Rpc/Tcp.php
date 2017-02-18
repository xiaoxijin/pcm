<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/17
 * Time: 11:10
 */

namespace Server\Rpc;


trait Tcp
{
    function onRpcReceive($server, $fd, $from_id, $data)
    {
        $requestInfo = \Packet::packDecode($data);
        if($requestInfo['type']=='tcp') {

            $requestInfo = $requestInfo['data'];
            #decode error
            if ($requestInfo["code"] != 0) {
                $pack["guid"] = $requestInfo["guid"];
                $req = \Packet::packEncode($requestInfo);
                $server->send($fd, $req);

                return true;
            } else {
                $requestInfo = $requestInfo["data"];
            }

            #api was not set will fail
            if (!is_array($requestInfo["api"]) && count($requestInfo["api"])) {
                $pack = \Packet::packFormat('PARAM_ERR');
                $pack["guid"] = $requestInfo["guid"];
                $pack = \Packet::packEncode($pack);
                $server->send($fd, $pack);

                return true;
            }
            $guid = $requestInfo["guid"];

            //prepare the task parameter
            $task = array(
                "type" => $requestInfo["type"],
                "guid" => $requestInfo["guid"],
                "fd" => $fd,
                "protocol" => "tcp",
            );
            $this->deliveryTask($requestInfo["type"], $requestInfo["api"]);
        }
        return true;
    }


    //task process finished
    function onFinish($serverer, $task_id, $data)
    {

        $fd = $data["fd"];
        $guid = $data["guid"];

        //if the guid not exists .it's mean the api no need return result
        if (!isset($this->taskInfo[$fd][$guid])) {
            return true;
        }

        //get the api key
        $key = $this->taskInfo[$fd][$guid]["taskkey"][$task_id];

        //save the result
        $this->taskInfo[$fd][$guid]["result"][$key] = $data["result"];

        //remove the used taskid
        unset($this->taskInfo[$fd][$guid]["taskkey"][$task_id]);

        switch ($data["type"]) {

            case $this->task_type['SW_MODE_WAITRESULT_SINGLE']:
                $Packet = \Packet::packFormat('OK', $data["result"]);
                $Packet["guid"] = $guid;
                $Packet = \Packet::packEncode($Packet, $data["protocol"]);

                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;

            case $this->task_type['SW_MODE_WAITRESULT_MULTI']:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = \Packet::packFormat('OK', $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet = \Packet::packEncode($Packet, $data["protocol"]);
                    $serverer->send($fd, $Packet);
                    //$server->close($fd);
                    unset($this->taskInfo[$fd][$guid]);

                    return true;
                } else {
                    //not finished
                    //waiting other result
                    return true;
                }
                break;

            case $this->task_type['SW_MODE_ASYNCRESULT_SINGLE']:
                $Packet = \Packet::packFormat("OK",$data["result"]);
                $Packet["guid"] = $guid;
                //flag this is result
                $Packet["isresult"] = 1;
                $Packet = \Packet::packEncode($Packet, $data["protocol"]);

                //sys_get_temp_dir
                $serverer->send($fd, $Packet);
                unset($this->taskInfo[$fd][$guid]);

                return true;
                break;
            case $this->task_type['SW_MODE_ASYNCRESULT_MULTI']:
                if (count($this->taskInfo[$fd][$guid]["taskkey"]) == 0) {
                    $Packet = \Packet::packFormat("OK", $this->taskInfo[$fd][$guid]["result"]);
                    $Packet["guid"] = $guid;
                    $Packet["isresult"] = 1;
                    $Packet = \Packet::packEncode($Packet, $data["protocol"]);
                    $serverer->send($fd, $Packet);

                    unset($this->taskInfo[$fd][$guid]);

                    return true;
                } else {
                    //not finished
                    //waiting other result
                    return true;
                }
                break;
            default:

                return true;
                break;
        }

    }
}