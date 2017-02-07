<?php
namespace Xphp\Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Service extends Object
{

    protected $type = 'Service';

    /**
     * 加载service
     */
    public function add($name)
    {
        if($class_full_name =$this->load($name)){
            $mdl= new $class_full_name();
            if(property_exists($class_full_name,'_table') && empty($mdl->_table))
                $mdl->_table = strtolower($this->file_name);
//            if(property_exists($class_full_name,'primary') && empty($mdl->primary))
//                $mdl->primary = $mdl->table.'_id';
            return $mdl;
        }elseif($mdl = $this->createTable()){
            return $mdl;
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
    public function createTable()
    {
        $mdl = new \Xphp\DataService();
        $mdl->_table = strtolower($this->file_name);
        $init_ret = $mdl->__init();
        if(!$init_ret)
            return false;
        return $mdl;
    }
}
