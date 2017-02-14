<?php
namespace IFace;

interface Log
{
    /**
     * 写入日志
     *
     * @param $msg   string 内容
     * @param $type  int 类型
     */
    function put($msg, $type = \Log::INFO);
}