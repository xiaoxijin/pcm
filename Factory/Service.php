<?php
namespace Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Service
{

    protected $namespace = 'Service';

    /**
     * 加载service
     */
    public function add($name)
    {
        $class_full_name= BS.$this->namespace.BS.$name;
        if(\Loader::importFileByNameSpace($this->namespace,$name)){
            $mdl =new $class_full_name();
            if(property_exists($class_full_name,'_table') && empty($mdl->_table) && $table_name = strrchr($class_full_name, BS))
                $mdl->_table = strtolower(trim($table_name,BS));
            return $mdl;
        }elseif(!strstr($name,BS)){
            return $this->createTable($name);
        }else{
            return false;
        }
    }

    /**
     * 加载表
     * @param $table_name
     * @param $db_key
     * @return Model
     */
    public function createTable($name)
    {
        $mdl = new \DB\Service();
        $mdl->_table = strtolower($name);
        $init_ret = $mdl->__init();
        if(!$init_ret)
            return false;
        return $mdl;
    }
}
