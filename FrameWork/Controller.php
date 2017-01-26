<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/20
 * Time: 14:47
 */
namespace Xphp;

class Controller{
    use Adjunct;
    public $ret=array();
    public $if_filter = true;
    public $module_name;

    public function setRet($code=array()){

//        $this->ret['code']=isset($code['code'])?$code['code']:OK;
//        $this->ret['msg']=$this->msg[$this->ret['code']];
//        $this->ret['data']=isset($code['data'])?$code['data']:array("ret"=>"true");
    }

    public function __initRet(){
        $this->setRet();
    }
}