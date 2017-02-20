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



}