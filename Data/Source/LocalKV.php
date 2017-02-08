<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 17:31
 */

namespace Data\Source;

class LocalKV extends \Yac{

    public function __construct($prefix='')
    {
        parent::__construct($prefix);
    }
}