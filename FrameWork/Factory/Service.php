<?php
namespace Xphp\Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Service extends Object
{

    /**
     * 加载Model
     * @param $model_name
     * @param $db_key
     * @return mixed
     * @throws Error
     */
    static public function load($name)
    {
        self::$type = "Service";
        if($class_full_name =parent::load($name)){
            $mdl= new $class_full_name();

        }elseif($mdl = self::createTable()){

        }else{
            return false;
        }
        $mdl->module_name=self::$module_name;
        return $mdl;
    }

    /**
     * 加载表
     * @param $table_name
     * @param $db_key
     * @return Model
     */
    static public function createTable()
    {
            $mdl = new \Xphp\ModelService();
            $file_name = strtolower(self::$file_name);
            $mdl->table = $file_name;
            $mdl->primary=$file_name.'_id';
            return $mdl;
    }
}
