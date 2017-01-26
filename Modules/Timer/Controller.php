<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 16:43
 */

namespace Module\Timer;


class Controller extends \Xphp\Controller
{
    public function __construct(\Xphp $xphp)
    {
        $this->module_name = explode("\\",__NAMESPACE__)[1];
        parent::__construct($xphp);
    }

}
