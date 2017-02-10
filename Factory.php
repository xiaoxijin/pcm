<?php

/**
 * Class Factory
 * @package xphp
 */
class Factory
{
    static private $_factory;
    static private $_product;
    static public $_produceHistory;
    private function __construct(){}

    static public function getInstance(){
        if(self::$_factory)
            return self::$_factory;
        self::$_factory=new Factory();
        return self::$_factory;
    }

    public function getProduct($type,$name){
        $name = str_replace(' ',BS,ucwords(str_replace('/',' ',$name)));//获取唯一系统产品名称，替换大小写等
        if(!$name)
            return false;
        if (!isset(self::$_product[$name]))//ctl 已经存在的ctl初始化数据
        {
            $new_product = $this->produceProduct($type,$name);//生产产品
            if(!$new_product)
                return false;
            if(is_object($new_product) || is_array($new_product) || is_string($new_product)){
                self::$_product[$name]  = $new_product;
                return self::$_product[$name];
            }
            else
                return true;
        }
        //初始化产品
//        array_push(\Bootstrap\Service::$service_history,self::$_produceHistory[$name]);
//        list('module_name' => \Bootstrap\Service::$current_module_name, 'class_name' => \Bootstrap\Service::$current_class_name)= self::$_produceHistory[$name];
        return self::$_product[$name];
    }

    private function produceProduct($type,$name)
    {

        switch (strtolower($type))
        {
            case "ctl":
                return Factory\Controller::load($name);
            case "mdl":
                return Factory\Model::load($name);
            case "lib":
                $product = new Factory\Library();
                return $product->add($name);
            case "service":
                $product = new Factory\Service();
                return $product->add($name);
            default:
                return false;
        }
    }


}
