<?php
namespace Xphp;

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
            self::$_product[$name] = $this->produceProduct($type,$name);
            if(!self::$_product[$name])//生产产品
                return false;

        //初始化产品
        array_push(\Xphp\Bootstrap\Service::$service_history,self::$_produceHistory[$name]);
//        list('module_name' => \Xphp\Bootstrap\Service::$current_module_name, 'class_name' => \Xphp\Bootstrap\Service::$current_class_name)= self::$_produceHistory[$name];
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
                return (new Factory\Library())->add($name);
            case "api":
                $product = new Factory\Service();
                return $product->add($name);
            case "data_api":
                return (new Factory\DataService())->add($name);
            default:
                return false;
        }
    }


}
