<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 17:31
 */

namespace Xphp\Data\Source;

class LocalTable extends \Swoole\Table{

    public function __construct($max_rows=1024)
    {
        
        parent::__construct($max_rows);
        $this->column('value', self::TYPE_STRING,8);
        $this->column('expire', self::TYPE_INT, 4);
        $this->create();
    }
}