<?php
/**
 * User: 肖喜进
 * Date: 2016/7/20
 * Time: 13:42
 */

class Cfg{
    static private $cfg;
    private function __construct(){}

    static private function getInstance(){
        if(self::$cfg)
            return self::$cfg;
        self::$cfg = new Config();
        return self::$cfg;
    }

    static public function get($name){
        return self::getInstance()[$name];
    }

    static public function getEnvName(){
        return self::getInstance()->env_name;
    }


}


class Config extends \ArrayObject
{
    static public $default_env_name='product';
    public $env_name;
    static $debug = false;
    static $active = false;
    private $config;

    public function __construct()
    {
        parent::__construct();
        $this->setEnvName();
    }

    /**
     * 设置运行环境名称
     */
    public function setEnvName(){
        if(isset($_SERVER['argv'][1]))
            $this->env_name = $_SERVER['argv'][1];
        elseif(get_cfg_var('env.name'))
            $this->env_name = get_cfg_var('env.name');
        else
            $this->env_name = self::$default_env_name;
    }


    private function get($target_index,$source_index){
        if($config_data = \Yaconf::get($target_index))
            return $config_data;
        else
            return \Yaconf::get($source_index);
    }

    function offsetGet($index)
    {

        if(!isset($this->config[$index]))
        {
            if(strstr($index,'.')){
                $target_index = explode('.',$index,2);
                $target_index[0]= $target_index[0].'.'.$this->env_name;
                $target_index = implode('.',$target_index);
                $this->config[$index] = $this->get($target_index,$index);
            }else
                $this->config[$index]= $this->get($index.'.'.$this->env_name,$index);
        }
        return $this->config[$index];
    }


    function offsetSet($index, $newval)
    {
        $this->config[$index] = $newval;
    }

    function offsetUnset($index)
    {
        unset($this->config[$index]);
    }

    function offsetExists($index)
    {
        return isset($this->config[$index]);
    }
}

