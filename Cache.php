<?php

/**
 * 缓存基类
 * @author Xijin.Xiao
 */


class Cache {
//    private static $cache;
    private static $local;
    private static $remote;
    private function __construct(){}

    static private function getLocalCacheClient(){

        if(self::$local)
            return self::$local;
        self::$local = new Cache\Local();
        return self::$local;
    }

    static private function getRemoteCacheClient(){

        if(self::$remote)
            return self::$remote;
        self::$remote = new Cache\Remote('master');
        return self::$remote;
    }

    static public function set($key, $value, $expire = 0){

        $l_ret = self::set_l($key, $value, $expire);
        $r_ret = self::set_r($key, $value, $expire);
        return $l_ret&$r_ret;
    }

    static public function set_l($key, $value, $expire = 0){
        return self::getLocalCacheClient()->set($key, $value, $expire);
    }

    static public function set_r($key, $value, $expire = 0){
        return self::getRemoteCacheClient()->set($key, $value, $expire);
    }

    static public function del($key){
        $l_ret = self::del_l($key);
        $r_ret = self::del_r($key);
        return $l_ret&$r_ret;
    }

    static public function del_l($key){
        return self::getLocalCacheClient()->del($key);
    }

    static public function del_r($key){
        return self::getRemoteCacheClient()->del($key);
    }

    static public function get($key){
        if($ret = self::get_l($key))
            return $ret;
        if($ret = self::get_r($key))
            return $ret;
    }

    static public function get_l($key){
        return self::getLocalCacheClient()->get($key);
    }

    static public function get_r($key){
        return self::getRemoteCacheClient()->get($key);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([self::getRemoteCacheClient(),$name],$arguments);

        // TODO: Implement __callStatic() method.
    }
}
