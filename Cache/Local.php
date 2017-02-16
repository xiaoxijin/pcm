<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 12:20
 */

namespace Cache;

class Local implements \IFace\Cache
{
    public $local_kv;

    public function __construct($prefix='l_c')
    {
        $this->local_kv =  new \Yac($prefix);
    }

    public function set($key, $value, $expire = 0)
    {
        return $this->local_kv->set($key, $value, $expire);
    }

    public function get($key)
    {
        return $this->local_kv->get($key);
    }

    public function del($key)
    {
        return $this->local_kv->del($key);
    }

}
