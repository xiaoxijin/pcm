<?php
namespace Xphp\Factory;
/**
 * 模型加载器
 * 产生一个模型的接口对象
 */
class Library extends Object
{
    protected $type = 'Libs';
    public function add($name)
    {
        if($class_full_name = $this->load($name)){
            $lib= new $class_full_name();
        }else{
            $lib = $this->loadExtra();
        }
        $lib->module_name=$this->module_name;
        return $lib;
    }

    //加载自定义路由的类库
    private function loadExtra()
    {

        $file_list = array(
            '\\'.$this->file_name.'\\Autoload'=>$this->type_path.$this->file_name.DS.'Autoload.php',
            //Lib/$file_name/AutoLoad.php,new \\Autoload();
            '\\'.$this->file_name.'\\'.$this->file_name.'Autoload'=>$this->type_path.$this->file_name.DS.$this->file_name.'Autoload.php',
            //Lib/$file_name/$file_nameAutoLoad.php , new \\$file_nameAutoLoad()
            '\\'.$this->file_name=>$this->type_path.$this->file_name.'.php',
            //Lib/$file_name.php, new \\$file_name()
        );

        foreach ($file_list as $class_name =>$file_path)
            if(file_exists($file_path)) {
                require $file_path;
                return new $class_name();
            }
        return false;
    }

}
