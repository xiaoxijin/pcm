<?php
/**
 * Created by PhpStorm.
 * User: xiaoxijin
 * Date: 2017/1/9
 * Time: 14:39
 * 统一数据接口
 */

namespace Xphp;


class Data{

    private $_data;
    static private $data;
    private $_configs;
    const TYPE_MYSQL   = 1;
    const TYPE_MYSQLi  = 2;
    const TYPE_PDO     = 3;
    const TYPE_CLMysql = 4;

    private function __construct(){
        $this->_configs=new Config(__DIR__);
        $this->_data["config"] = $this->_configs;
    }

    static public function getInstance(){
        if(self::$data)
            return self::$data;
        self::$data = new Data();
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
        return $this->data($type);
    }


    public function data($type='data'){
        $type = strtolower($type);
        if (isset($this->_data[$type]))
            return $this->_data[$type];

        switch ($type)
        {
            case "cache":
                $this->_data[$type]=$this->cache("master");
                break;
            case "redis":
                $this->_data[$type]=$this->redis("master");
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
        switch ($db_config['type'])
        {
            case self::TYPE_MYSQL:
                $db = new Data\Source\MySQL($db_config);
                break;
            case self::TYPE_MYSQLi:
                $db = new Data\Source\MySQLi($db_config['host'],$db_config['user'],$db_config['passwd'],$db_config['name'],$db_config['port']);
                $db->config = $db_config;
                break;
            case self::TYPE_CLMysql:
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

    private function redis($flag){
        return new Data\Source\Redis($flag);
    }

    private function subRedis($flag){
        return new Data\Source\Redis($flag);
    }

}