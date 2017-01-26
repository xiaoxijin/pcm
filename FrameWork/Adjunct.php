<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/18
 * Time: 16:51
 */


namespace Xphp;

trait Adjunct{

    public function Model($model_name){
        return model($this->getLoadName($model_name));
    }

    public function Lib($lib_name){
        return lib($this->getLoadName($lib_name));
    }

    public function getLoadName($name){

        if(strpos($name, '/') !== 0)
            return $name;
        else
            return $this->module_name.'/'.$name;
    }
}