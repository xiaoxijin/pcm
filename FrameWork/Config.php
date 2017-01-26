<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/20
 * Time: 13:42
 */
namespace Xphp;

class Config extends \ArrayObject
{
    static public $default_env_name='common';
    protected $default_config_dir_name='Configs';
    protected $config_path;
    protected $env_name;
    public $dir_num = 0;
    static $debug = false;
    static $active = false;
    private $config;

    public function __construct($dir=__DIR__)
    {
        parent::__construct();
        $this->initConfigs($dir);
    }

    /**
     * 设置运行环境名称
     */
    public function setEnvName(){
        if(isset($_SERVER['argv'][1]))
            $this->env_name = $_SERVER['argv'][1];
        elseif(get_cfg_var('env.name'))
            $this->env_name = get_cfg_var('env.name');
    }
    /**
     * 初始化目录，环境
     */
    public function initConfigs($dir) {

        $this->setEnvName();
        if(is_array($dir))
            $this->addModuleConfigPath($dir);
        else
            $this->addConfigPath($dir);
    }

    /**
     * 添加框架配置文件目录
     */
    public function addConfigPath($dir){
        //设置根配置文件目录
        $this->setPath($dir.DS.$this->default_config_dir_name. DS);
    }
    /**
     * 添加框架模块配置文件目录
     */
    public function addModuleConfigPath($path){
        $dir = @ dir($path);
        while (($file = $dir->read())!==false){
            if(is_dir($path.DS.$file) AND ($file!=".") AND ($file!=".."))
                $this->addConfigPath($path.DS.$file);
        }
        $this->addConfigPath($path);
        $dir->close();
    }

    function setPath($dir)
    {
        if(is_dir($dir)){
            $this->config_path[] = $dir;
            self::$active = true;
        }
    }

    function offsetGet($index)
    {

        if(!isset($this->config[$index]))
        {
            $this->load($index);
        }
        return $this->config[$index]??false;
    }

    function loadAll(){
        
        foreach ($this->config_path as $path)
        {
            $dir = @ dir($path);
            $file = $dir->read();
            while (($file = $dir->read())!==false){
                if(!is_dir($path.DS.$file) AND ($file!=".") AND ($file!="..")){
                    $pathinfo = pathinfo($file);
                    if($pathinfo['extension']=='php')
                        $this->loadOne($this->getLoadFileName($path,$pathinfo['filename']),$pathinfo['filename']);
                }
            }
            $dir->close();
        }
    }

    function loadOne($filename,$index){
        if (is_file($filename))
        {
            $retData = include $filename;
            if (empty($retData) and self::$debug)
            {
                trigger_error(__CLASS__ . ": $filename no return data");
            }
            else
            {
                if (!isset($retData[self::$default_env_name]) && isset($retData[$this->env_name]))
                    $retData = $retData[$this->env_name];
                elseif(isset($retData[self::$default_env_name]) && isset($retData[$this->env_name]) && $this->env_name!=self::$default_env_name)
                    $retData = $this->configMerge($retData[self::$default_env_name],$retData[$this->env_name]);
                elseif (isset($retData[self::$default_env_name]) && !isset($retData[$this->env_name]))
                    $retData = $retData[self::$default_env_name];
                $this->config[$index] = $retData;
            }
        }
        elseif (self::$debug)
        {
            trigger_error(__CLASS__ . ": $filename not exists");
        }
    }

    function configMerge(& $res_default,& $res_env){

        foreach ($res_env as $env_key=>$env_val){
            if(is_array($env_val) && isset($res_default[$env_key]))
                $res_env[$env_key] =  $this->configMerge($res_default[$env_key],$env_val);
        }
        return array_merge($res_default,$res_env);

    }

    function getLoadFileName($path,$index){
        return $path . $index . '.php';
    }

    function load($index)
    {
        foreach ($this->config_path as $path)
            $this->loadOne($this->getLoadFileName($path,$index),$index);
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