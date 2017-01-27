<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/16
 * Time: 11:34
 */
namespace Xphp;

class Bootstrap{


    static private $_bootstrap;
    private function __construct(){}

    static public function  getInstance($type){

        $type = strtolower($type);
        if (isset(self::$_bootstrap[$type]))
            return self::$_bootstrap[$type];

        switch ($type)
        {
            case "cli":
                self::$_bootstrap[$type]=new Bootstrap\Api();
                break;
            case "api":
                self::$_bootstrap[$type]=new Bootstrap\Service();
                break;
            default:
                return false;
        }
        return self::$_bootstrap[$type];
    }

}
