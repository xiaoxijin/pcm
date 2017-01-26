<?php
namespace Xphp\IFace;
use Xphp;

interface Log
{
    /**
     * 写入日志
     *
     * @param $msg   string 内容
     * @param $type  int 类型
     */
    function put($msg, $type = Xphp\Log::INFO);
}