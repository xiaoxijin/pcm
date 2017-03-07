<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/16
 * Time: 15:00
 */
namespace DB;
class Connector
{
    private static $connector;
    private function __construct(){}

    static public function get($flag='master'){
        if(!self::$connector[$flag]){
            $db_config = \Cfg::get('db')[$flag];
            switch (strtolower($db_config['type']))
            {
//                case 'mysql':
//                    self::$connector[$flag] = new \Client\MySQL($db_config);
//                    break;
                case 'mysqli':
                    self::$connector[$flag] = new \Client\MySQLi($db_config['host'],$db_config['user'],$db_config['passwd'],$db_config['name'],$db_config['port']);
                    self::$connector[$flag]->set_charset($db_config['charset']);
                    self::$connector[$flag]->config = $db_config;
                    break;
                case 'clmysql':
                    self::$connector[$flag] = new \Client\CLMySQL($db_config);
                    break;
                default:
                    $dsn = $db_config['dbms'] . ":host=" . $db_config['host'] . ";dbname=" . $db_config['name'];
                    self::$connector[$flag] = new \Client\PdoDB($dsn, $db_config['user'], $db_config['passwd']);
                    self::$connector[$flag]->query('set names ' . $db_config['charset']);
                    self::$connector[$flag]->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
                    break;
            }
        }
        return self::$connector[$flag];
    }
}