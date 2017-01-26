<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 16:43
 */

namespace Module\Pay;


class Controller extends \Xphp\Controller
{
    static $CACERT_FILE=__DIR__.DS.CONFIG_DIR_NAME.DS.'cacert.pem';
    static $LOG_FILE=__DIR__.DS.CONFIG_DIR_NAME.DS.'log.txt';
    public function __construct(\Xphp $xphp)
    {
        $this->module_name = explode("\\",__NAMESPACE__)[1];
        parent::__construct($xphp);
    }

}


