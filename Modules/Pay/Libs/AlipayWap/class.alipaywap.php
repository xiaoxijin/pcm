<?php
/**
 * Created by PhpStorm.
 * User: engin
 * Date: 2016/7/29
 * Time: 10:49
 */
require_once("alipay_submit.class.php");
require_once("alipay_notify.class.php");

class AlipayWap{
    public $_objAlipaySubmit;
    public $_payConfig;
    public $xphp;

    function __construct(\Xphp $xphp){
        $this->xphp=$xphp;
        $this->_payConfig=$this->xphp->config['alipaywap'];
    }

    function getAliPayClass($class_name){
       return  new $class_name($this->_payConfig);
    }
}