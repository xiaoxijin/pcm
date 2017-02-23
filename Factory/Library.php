<?php
namespace Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Library
{
    protected $namespace = 'Lib';
    public function add($name)
    {
        $class_full_name= BS.$this->namespace.BS.$name;
        if(\Loader::importFileByNameSpace($this->namespace,$name)){
            return new $class_full_name();
        }else{
            //如果到这里，就只加载文件，不会实例化对象，因为无法确定文件里面的对象名，基本为第三方lib
            $file_list = array(
                $name.'Autoload',
                //Lib/$nameAutoLoad.php
                $name.DS.'Autoload',
                //Lib/$name/AutoLoad.php
                $name.DS.'SplClassLoader',
                //Lib/$name/AutoLoad.php
                $name.DS.$name.'Autoload',
                //Lib/$file_name/$file_nameAutoLoad.php

            );
            foreach ($file_list as $file_name)
                if($file = \Loader::importFileByNameSpace($this->namespace,$file_name)) {
                    if(class_exists($name))
                        return new $name();
                    else
                        return $file;
                }
            return false;
        }

    }
}
