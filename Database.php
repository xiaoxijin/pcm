<?php
/**
 * Created by PhpStorm.
 * User: xiaoxijin
 * Date: 2017/1/9
 * Time: 14:39
 * 统一数据接口
 */



class Data{

    private $_data;
    static private $data;
    private $_configs;

    private function __construct(){
        $this->_configs=new \Config(__DIR__);
        $this->_data["config"] = $this->_configs;
    }

    static public function getInstance(){
        if(self::$data)
            return self::$data;
        self::$data = new self();
        return self::$data;
    }

    public function __get($name)
    {
        if (isset($this->_data[$name]))
            return $this->_data[$name];
        $this->_data[$name] = $this->_configs[$name];
        return $this->_data[$name];
    }


    function __invoke($type)
    {
        return $this->source($type);
    }


    public function source($type='data'){
        $type = strtolower($type);
        if (isset($this->_data[$type]))
            return $this->_data[$type];

        switch ($type)
        {
            case "cache":
                $this->_data[$type]=$this->cache("master");
                break;
            case "subredis":
                $this->_data[$type]=$this->subRedis("master");
                break;
            case "db":
                $this->_data[$type]=$this->db("master");
                break;
            default:
                return false;
        }
        return $this->_data[$type];
    }



     private function db($db_key){

        $db_config = $this->_configs['db'][$db_key];
        switch (strtolower($db_config['type']))
        {
            case 'mysql':
                $db = new Data\Source\MySQL($db_config);
                break;
            case 'mysqli':
                $db = new Data\Source\MySQLi($db_config['host'],$db_config['user'],$db_config['passwd'],$db_config['name'],$db_config['port']);
                $db->set_charset($db_config['charset']);
                $db->config = $db_config;
                break;
            case 'clmysql':
                $db = new Data\Source\CLMySQL($db_config);
                break;
            default:
                $db = new Data\Source\PdoDB($db_config);
                break;
        }
        return $db;
    }

    private function cache(){

        $cache= new Data\Cache($this->cache);
        return $cache;
    }

    private function subRedis($flag){
        return new Data\Source\Redis($flag);
    }

}