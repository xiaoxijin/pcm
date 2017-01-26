<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/9
 * Time: 12:20
 */

namespace Xphp\Data\Cache;

class Local implements \Xphp\IFace\Cache
{
    public $local_kv;

    public function __construct()
    {
        $this->local_kv = new \Xphp\Data\Source\LocalKV();
    }

    public function set($key, $value, $expire = 0)
    {
        $ret= $this->local_kv->set($key, $value, $expire);
        return $ret;
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
