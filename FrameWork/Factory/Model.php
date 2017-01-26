<?php
namespace Xphp\Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Model extends Object
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
        self::$type = "Models";
        if($class_full_name =parent::load($name)){
            $mdl= new $class_full_name();
        }else{
            $mdl = self::createTable();
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
            $mdl = new \Xphp\Model();
            $file_name = strtolower(self::$file_name);
            $mdl->table = $file_name;
            $mdl->primary=$file_name.'_id';
            return $mdl;
    }
}
