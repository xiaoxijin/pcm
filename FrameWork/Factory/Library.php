<?php
namespace Xphp\Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Library extends Object
{
    static public function load(& $name)
    {
        self::$type = "Libs";
        if($class_full_name = parent::load($name)){
            $lib= new $class_full_name();
        }else{
            $lib = self::loadExtra();
        }
        $lib->module_name=self::$module_name;
        return $lib;
    }

    //加载自定义路由的类库
    static private function loadExtra()
    {

        $file_list = array(
            '\\Autoload'=>self::$type_path.self::$file_name.DS.'Autoload.php',
            //Lib/$file_name/AutoLoad.php,new \\Autoload();
            '\\'.self::$file_name.'Autoload'=>self::$type_path.self::$file_name.DS.self::$file_name.'Autoload.php',
            //Lib/$file_name/$file_nameAutoLoad.php , new \\$file_nameAutoLoad()
            '\\'.self::$file_name=>self::$type_path.self::$file_name.DS.self::$file_name.'.php',
            //Lib/$file_name/$file_name.php, new \\$file_name()
        );

        foreach ($file_list as $class_name =>$file_path)
            if(file_exists($file_path)) {
                return new $class_name();
            }
        return false;
    }

}
