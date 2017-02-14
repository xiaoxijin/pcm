<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/14
 * Time: 10:46
 */
namespace IFace;

interface Tcp{

//    function onConnect($server, $client_id, $from_id);
    function onReceive($server,$fd,$from_id,$data);
//    function onClose($server, $client_id, $from_id);

}