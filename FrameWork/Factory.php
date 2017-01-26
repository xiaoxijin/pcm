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
    private function __construct(){}

    static public function getInstance(){
        if(self::$_factory)
            return self::$_factory;
        self::$_factory=new Factory();
        return self::$_factory;
    }

    public function getProduct($type,$name){
        $name = $this->getUniqueProductName($name);//获取唯一系统产品名称，替换大小写等
        if (!isset(self::$_product[$name]))//ctl 已经存在的ctl初始化数据
            if(!(self::$_product[$name] = $this->produceProduct($type,$name)))//生产产品
                return false;
        $this->initProduct($name);//初始化产品
        return self::$_product[$name];
    }

    private function getUniqueProductName($name){
       return str_replace(' ',BS,ucwords(str_replace('/',' ',trim($name, '/'))));
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
                return Factory\Library::load($name);
            default:
                return false;
        }
    }

    private function initProduct($name){
        $ref_ctl = new \ReflectionClass(self::$_product[$name]);
        foreach ($ref_ctl->getMethods() as $reflectionMethod){
            if(strstr($reflectionMethod->name, '__'))
            {
                self::$_product[$name]->{$reflectionMethod->name}();
            }
        }
    }
}
