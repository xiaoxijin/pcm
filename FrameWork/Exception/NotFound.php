<?php
namespace Xphp\Exception;

/**
 * 模块不存在
 * Class NotFound
 * @package Xphp
 */
class NotFound extends \Exception
{
    function __construct($message='api module not found.', $code=100101, Exception $previous = null)
    {
//        var_dump($this->getCode());
//        var_dump($this->getMessage());
//        var_dump($this->getPrevious());
        parent::__construct($message, $code,$previous);
    }
}


