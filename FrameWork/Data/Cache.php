<?php

/**
 * 缓存基类
 * @author Xijin.Xiao
 * @package XphpSystem
 * @subpackage Database
 */
namespace Xphp\Data;


class Cache {

    private $level;
    private $local;
    private $remote;
    public function __construct($cache_config)
    {
        $this->local = new Cache\Local();
//        $this->remote = new Cache\Remote();
    }

    public function set($key, $value, $expire = 0,$level=1){
        return $this->local->set($key, $value, $expire);
    }

    public function del($key){
        return $this->local->delete($key);
    }

    public function get($key){
        return $this->local->get($key);
    }



}

