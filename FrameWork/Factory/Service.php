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
            if(property_exists($class_full_name,'table') && empty($mdl->table))
                $mdl->table = strtolower($this->file_name);
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
        $file_name = strtolower($this->file_name);
        $_db = \Xphp\Data::getInstance()->data("db");
        if(!($_db->classExist($file_name)))
            return false;

        $mdl = new \Xphp\DataService();
        $mdl->table = $file_name;
//        if($primary_key = $_db->getPrimaryKey($file_name))
//            $mdl->primary=$primary_key;
//        else
//            $mdl->primary=$file_name."_id";
        return $mdl;
    }
}
