<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/14
 * Time: 13:37
 */
namespace IFace;
interface Rpc extends MultiProcess{
    function onRpcReceive($server,$fd,$from_id,$data);
    function onRpcRequest($request,$response);
}